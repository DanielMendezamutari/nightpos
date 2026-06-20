const fs = require('fs')
const path = require('path')
const { execFile } = require('child_process')
const { promisify } = require('util')

const execFileAsync = promisify(execFile)

const LOG_PREFIX = '[nightpos-printer]'

function log(level, message, extra = null) {
  const ts = new Date().toISOString()
  const line = `${LOG_PREFIX} ${ts} ${level.toUpperCase()} ${message}`
  if (extra !== null)
    console.log(line, extra)
  else
    console.log(line)
}

/** ESC/POS helpers */
const ESC = 0x1B
const GS = 0x1D

function escPosInit() {
  return Buffer.from([ESC, 0x40])
}

function escPosCut() {
  return Buffer.from([GS, 0x56, 0x00])
}

function escPosFeed(lines = 3) {
  return Buffer.from([ESC, 0x64, Math.min(lines, 255)])
}

/**
 * Build RAW buffer for thermal printer (Windows spooler RAW datatype).
 * Uses Windows-1252 for Spanish accents on most ESC/POS devices.
 */
function buildEscPosPayload(contentText) {
  const body = Buffer.from(String(contentText), 'latin1')
  return Buffer.concat([
    escPosInit(),
    body,
    escPosFeed(3),
    escPosCut(),
  ])
}

function getRawPrintScriptPath() {
  return path.join(__dirname, 'winRawPrint.ps1')
}

async function runPowerShell(scriptPath, args, timeoutMs = 30000) {
  const psArgs = [
    '-NoProfile',
    '-NonInteractive',
    '-ExecutionPolicy', 'Bypass',
    '-File', scriptPath,
    ...args,
  ]

  log('debug', `PowerShell exec: ${path.basename(scriptPath)} ${args.join(' ')}`)

  try {
    const { stdout, stderr } = await execFileAsync(
      'powershell.exe',
      psArgs,
      { timeout: timeoutMs, windowsHide: true, maxBuffer: 1024 * 1024 },
    )

    return {
      ok: true,
      stdout: (stdout || '').trim(),
      stderr: (stderr || '').trim(),
      exitCode: 0,
    }
  }
  catch (error) {
    const stdout = error.stdout ? String(error.stdout).trim() : ''
    const stderr = error.stderr ? String(error.stderr).trim() : ''
    const exitCode = typeof error.code === 'number' ? error.code : 1

    return {
      ok: false,
      stdout,
      stderr: stderr || (error.message || 'PowerShell failed'),
      exitCode,
    }
  }
}

/**
 * @returns {Promise<string[]>}
 */
async function listPrinters() {
  if (process.platform !== 'win32') {
    log('warn', 'listPrinters only supported on Windows')
    return []
  }

  const scriptPath = getRawPrintScriptPath()
  const result = await runPowerShell(scriptPath, ['-ListPrinters'])

  if (!result.ok) {
    throw new Error(`Cannot list printers: ${result.stderr || result.stdout || 'unknown error'}`)
  }

  if (!result.stdout)
    return []

  return result.stdout
    .split(/\r?\n/)
    .map(line => line.trim())
    .filter(Boolean)
}

/**
 * @returns {Promise<{ found: boolean, status?: string, driver?: string }>}
 */
async function getPrinterInfo(printerName) {
  const scriptPath = getRawPrintScriptPath()
  const result = await runPowerShell(scriptPath, ['-PrinterName', printerName, '-Info'])

  if (!result.ok) {
    throw new Error(result.stderr || result.stdout || 'getPrinterInfo failed')
  }

  try {
    return JSON.parse(result.stdout)
  }
  catch {
    throw new Error(`Invalid printer info response: ${result.stdout}`)
  }
}

const BLOCKED_STATUSES = new Set([
  'offline',
  'error',
  'paperout',
  'paperjam',
  'paperproblem',
  'dooropen',
  'notavailable',
  'paused',
  'userintervention',
])

async function verifyPrinter(printerName) {
  log('info', `Verifying printer "${printerName}" exists in Windows spooler`)

  const info = await getPrinterInfo(printerName)

  if (!info.found) {
    const available = await listPrinters()
    const hint = available.length
      ? `Available: ${available.join(', ')}`
      : 'No printers found in spooler'

    throw new Error(`Printer "${printerName}" not found in Windows spooler. ${hint}`)
  }

  const statusNorm = String(info.status || '').toLowerCase().replace(/\s+/g, '')
  log('info', `Printer OK — status=${info.status || '?'} driver=${info.driver || '?'} port=${info.port || '?'}`)

  if (BLOCKED_STATUSES.has(statusNorm)) {
    throw new Error(
      `Printer "${printerName}" not ready (status=${info.status}). `
      + 'Check USB/power/paper and clear Windows print queue.',
    )
  }

  return info
}

/**
 * Send RAW ESC/POS bytes to Windows spooler.
 * Throws on any spooler / Win32 error — caller must NOT mark PRINTED.
 *
 * @returns {Promise<{ bytesSent: number, spoolerMessage: string }>}
 */
async function printRawEscPos(printerName, contentText) {
  if (process.platform !== 'win32')
    throw new Error('RAW spooler printing is only supported on Windows')

  await verifyPrinter(printerName)

  const payload = buildEscPosPayload(contentText)
  log('info', `Sending RAW job to "${printerName}" — ${payload.length} bytes (ESC/POS)`)

  const tmpDir = require('os').tmpdir()
  const tmpFile = path.join(tmpDir, `nightpos-raw-${Date.now()}-${process.pid}.bin`)
  fs.writeFileSync(tmpFile, payload)

  try {
    const scriptPath = getRawPrintScriptPath()
    const result = await runPowerShell(scriptPath, [
      '-PrinterName', printerName,
      '-FilePath', tmpFile,
    ])

    log('debug', `Spooler stdout: ${result.stdout || '(empty)'}`)
    if (result.stderr)
      log('warn', `Spooler stderr: ${result.stderr}`)

    if (!result.ok) {
      const detail = [result.stderr, result.stdout].filter(Boolean).join(' | ')
      throw new Error(`Windows spooler rejected job (exit ${result.exitCode}): ${detail || 'unknown error'}`)
    }

    let parsed = null
    try {
      parsed = JSON.parse(result.stdout)
    }
    catch {
      throw new Error(`Spooler returned invalid response: ${result.stdout}`)
    }

    if (!parsed.success) {
      const win32 = parsed.win32Error ? ` Win32=${parsed.win32Error}` : ''
      throw new Error(`Spooler RAW write failed:${win32} ${parsed.message || 'unknown'}`)
    }

    log('info', `Spooler accepted job — bytesSent=${parsed.bytesSent} message=${parsed.message}`)

    return {
      bytesSent: parsed.bytesSent,
      spoolerMessage: parsed.message,
    }
  }
  finally {
    try { fs.unlinkSync(tmpFile) }
    catch {}
  }
}

/**
 * Dry-run: write ESC/POS payload to file for inspection.
 */
function writeDryRunPayload(outputDir, jobId, contentText) {
  if (!fs.existsSync(outputDir))
    fs.mkdirSync(outputDir, { recursive: true })

  const payload = buildEscPosPayload(contentText)
  const txtFile = path.join(outputDir, `job-${jobId}-${Date.now()}.txt`)
  const binFile = path.join(outputDir, `job-${jobId}-${Date.now()}.bin`)

  fs.writeFileSync(txtFile, contentText, 'utf8')
  fs.writeFileSync(binFile, payload)

  log('info', `[dry-run] wrote ${txtFile} and ${binFile} (${payload.length} bytes RAW)`)

  return { txtFile, binFile, bytes: payload.length }
}

module.exports = {
  log,
  buildEscPosPayload,
  listPrinters,
  getPrinterInfo,
  verifyPrinter,
  printRawEscPos,
  writeDryRunPayload,
}
