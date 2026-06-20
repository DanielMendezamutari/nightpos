const fs = require('fs')
const path = require('path')
const {
  log,
  printRawEscPos,
  writeDryRunPayload,
  verifyPrinter,
} = require('./printer')

const CONFIG_PATH = path.join(__dirname, '..', 'config.json')

function loadConfig() {
  const args = process.argv.slice(2)
  const dryRunArg = args.includes('--dry-run')

  if (!fs.existsSync(CONFIG_PATH)) {
    console.error('Missing config.json — copy config.example.json and edit values.')
    process.exit(1)
  }

  const config = JSON.parse(fs.readFileSync(CONFIG_PATH, 'utf8'))
  if (dryRunArg)
    config.dry_run = true

  if (!config.backend_url || !config.device_key) {
    console.error('config.json requires backend_url and device_key')
    process.exit(1)
  }

  config.poll_interval_ms = config.poll_interval_ms || 1500
  config.dry_run_dir = config.dry_run_dir || path.join(__dirname, '..', 'dry-run-output')

  return config
}

async function apiRequest(config, method, route, body = null) {
  const url = `${config.backend_url.replace(/\/$/, '')}${route}`
  const response = await fetch(url, {
    method,
    headers: {
      Authorization: `Bearer ${config.device_key}`,
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: body ? JSON.stringify(body) : undefined,
  })

  const text = await response.text()
  let json = {}
  try {
    json = text ? JSON.parse(text) : {}
  }
  catch {
    json = { raw: text }
  }

  if (!response.ok) {
    const message = json.message || response.statusText
    throw new Error(`${response.status} ${message}`)
  }

  return json.data ?? json
}

async function sendHeartbeat(config, lastError = null) {
  await apiRequest(config, 'POST', '/print-devices/heartbeat', {
    printer_name: config.printer_name,
    agent_version: '1.1.0',
    last_error: lastError,
  })
}

async function fetchPending(config) {
  const data = await apiRequest(config, 'GET', '/print-jobs/pending?limit=5')
  return data.jobs ?? []
}

async function claimJob(config, jobId) {
  const data = await apiRequest(config, 'POST', `/print-jobs/${jobId}/claim`)
  return data.job
}

async function markPrinted(config, jobId) {
  await apiRequest(config, 'POST', `/print-jobs/${jobId}/printed`)
}

async function markFailed(config, jobId, error) {
  await apiRequest(config, 'POST', `/print-jobs/${jobId}/failed`, { error })
}

async function printContent(config, job) {
  const content = job.content_text || ''
  if (!content)
    throw new Error('Empty content_text')

  if (config.dry_run) {
    writeDryRunPayload(config.dry_run_dir, job.id, content)
    return { mode: 'dry-run' }
  }

  if (!config.printer_name)
    throw new Error('printer_name not configured in config.json')

  const result = await printRawEscPos(config.printer_name, content)
  return { mode: 'raw-spooler', ...result }
}

async function processJob(config, job) {
  log('info', `Processing job #${job.id} (${job.type})`)
  await claimJob(config, job.id)

  try {
    const printResult = await printContent(config, job)
    log('info', `Physical print confirmed for job #${job.id}`, printResult)
    await markPrinted(config, job.id)
    log('info', `Job #${job.id} marked PRINTED in backend`)
  }
  catch (error) {
    const message = error instanceof Error ? error.message : String(error)
    log('error', `Job #${job.id} print failed — NOT marking PRINTED`, message)
    await markFailed(config, job.id, message)
    try {
      await sendHeartbeat(config, message)
    }
    catch (heartbeatError) {
      log('warn', 'Failed to send error heartbeat', heartbeatError.message)
    }
  }
}

async function tick(config) {
  await sendHeartbeat(config)
  const jobs = await fetchPending(config)
  for (const job of jobs)
    await processJob(config, job)
}

async function main() {
  const config = loadConfig()

  log('info', `NightPOS print agent v1.1.0 — ${config.dry_run ? 'DRY-RUN' : config.printer_name}`)
  log('info', `Backend ${config.backend_url} — poll ${config.poll_interval_ms}ms`)

  if (!config.dry_run && config.printer_name) {
    try {
      await verifyPrinter(config.printer_name)
    }
    catch (error) {
      log('error', 'Startup printer check failed', error.message)
      log('error', 'Fix printer_name or run: node src/test-print.js --list')
      process.exit(1)
    }
  }

  while (true) {
    try {
      await tick(config)
    }
    catch (error) {
      log('error', 'Poll error', error instanceof Error ? error.message : error)
    }
    await new Promise(r => setTimeout(r, config.poll_interval_ms))
  }
}

main().catch(error => {
  console.error(error)
  process.exit(1)
})
