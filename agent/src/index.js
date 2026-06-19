const fs = require('fs')
const path = require('path')
const { execFile } = require('child_process')

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

async function sendHeartbeat(config) {
  await apiRequest(config, 'POST', '/print-devices/heartbeat', {
    printer_name: config.printer_name,
    agent_version: '1.0.0',
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

function ensureDryRunDir(dir) {
  if (!fs.existsSync(dir))
    fs.mkdirSync(dir, { recursive: true })
}

async function printContent(config, job) {
  const content = job.content_text || ''
  if (!content)
    throw new Error('Empty content_text')

  if (config.dry_run) {
    ensureDryRunDir(config.dry_run_dir)
    const file = path.join(config.dry_run_dir, `job-${job.id}-${Date.now()}.txt`)
    fs.writeFileSync(file, content, 'utf8')
    console.log(`[dry-run] wrote ${file}`)
    return
  }

  if (!config.printer_name)
    throw new Error('printer_name not configured')

  const tmp = path.join(require('os').tmpdir(), `nightpos-job-${job.id}.txt`)
  fs.writeFileSync(tmp, content, 'utf8')

  await new Promise((resolve, reject) => {
    execFile('cmd', ['/c', 'print', `/D:${config.printer_name}`, tmp], error => {
      try { fs.unlinkSync(tmp) }
      catch {}
      if (error)
        reject(error)
      else
        resolve()
    })
  })
}

async function processJob(config, job) {
  console.log(`Processing job #${job.id} (${job.type})`)
  await claimJob(config, job.id)
  try {
    await printContent(config, job)
    await markPrinted(config, job.id)
    console.log(`Job #${job.id} PRINTED`)
  }
  catch (error) {
    const message = error instanceof Error ? error.message : String(error)
    console.error(`Job #${job.id} FAILED: ${message}`)
    await markFailed(config, job.id, message)
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
  console.log(`NightPOS print agent — ${config.dry_run ? 'DRY-RUN' : config.printer_name}`)
  console.log(`Polling ${config.backend_url} every ${config.poll_interval_ms}ms`)

  while (true) {
    try {
      await tick(config)
    }
    catch (error) {
      console.error('Poll error:', error instanceof Error ? error.message : error)
    }
    await new Promise(r => setTimeout(r, config.poll_interval_ms))
  }
}

main().catch(error => {
  console.error(error)
  process.exit(1)
})
