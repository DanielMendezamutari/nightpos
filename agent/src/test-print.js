const fs = require('fs')
const path = require('path')
const {
  log,
  listPrinters,
  getPrinterInfo,
  verifyPrinter,
  printRawEscPos,
  writeDryRunPayload,
} = require('./printer')

const CONFIG_PATH = path.join(__dirname, '..', 'config.json')

function loadConfig() {
  if (!fs.existsSync(CONFIG_PATH))
    return {}

  return JSON.parse(fs.readFileSync(CONFIG_PATH, 'utf8'))
}

function parseArgs() {
  const args = process.argv.slice(2)
  const opts = {
    printer: null,
    dryRun: false,
    list: false,
    info: false,
  }

  for (let i = 0; i < args.length; i++) {
    const arg = args[i]
    if (arg === '--dry-run')
      opts.dryRun = true
    else if (arg === '--list')
      opts.list = true
    else if (arg === '--info')
      opts.info = true
    else if (arg === '--printer' && args[i + 1]) {
      opts.printer = args[++i]
    }
    else if (!arg.startsWith('-') && !opts.printer) {
      opts.printer = arg
    }
  }

  const config = loadConfig()
  if (!opts.printer)
    opts.printer = config.printer_name || null

  return opts
}

const SAMPLE_TICKET = `
======== NIGHTPOS ========
COMANDA BAR — TEST
Mesa: 5 · Salon
Garzon: Prueba
Fecha: ${new Date().toLocaleString('es-BO')}
------------------------
2x Paceña        SOLO
1x Combo Test      ACOMP
------------------------
TOTAL 120.00 BOB
========================
`.trim()

async function main() {
  const opts = parseArgs()

  if (opts.list) {
    const printers = await listPrinters()
    console.log('Impresoras Windows:')
    for (const name of printers)
      console.log(`  - ${name}`)
    return
  }

  if (!opts.printer) {
    console.error('Usage: node src/test-print.js --printer CAJA')
    console.error('       node src/test-print.js --list')
    console.error('       node src/test-print.js CAJA --info')
    process.exit(1)
  }

  if (opts.info) {
    const info = await getPrinterInfo(opts.printer)
    console.log(JSON.stringify(info, null, 2))
    return
  }

  log('info', `Test print to "${opts.printer}" (dryRun=${opts.dryRun})`)

  await verifyPrinter(opts.printer)

  if (opts.dryRun) {
    const outDir = path.join(__dirname, '..', 'dry-run-output')
    const result = writeDryRunPayload(outDir, 'test', SAMPLE_TICKET)
    console.log('Dry-run OK:', result)
    return
  }

  const result = await printRawEscPos(opts.printer, SAMPLE_TICKET)
  console.log('Print OK:', result)
  console.log('Si no sale papel, verifique:')
  console.log('  1. Driver térmico ESC/POS (no solo "Generic Text")')
  console.log('  2. Puerto USB correcto en Propiedades de impresora')
  console.log('  3. Cola de impresión sin jobs atascados en error')
}

main().catch(error => {
  console.error('[nightpos-test-print] FAILED:', error.message || error)
  process.exit(1)
})
