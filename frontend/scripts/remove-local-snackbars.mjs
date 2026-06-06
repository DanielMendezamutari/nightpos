import fs from 'node:fs'
import path from 'node:path'

const root = path.resolve('src/pages/nightpos')

function walk(dir, files = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name)
    if (entry.isDirectory())
      walk(full, files)
    else if (entry.name.endsWith('.vue'))
      files.push(full)
  }
  return files
}

const snackbarRe = /\s*<VSnackbar[\s\S]*?<\/VSnackbar>\s*/g

let changed = 0
for (const file of walk(root)) {
  const original = fs.readFileSync(file, 'utf8')
  if (!original.includes('<VSnackbar'))
    continue

  let next = original.replace(snackbarRe, '\n')
  next = next.replace(/const\s*\{\s*snackbar\s*,\s*notify\s*\}\s*=\s*useNightPosNotify\(\)/g, 'const { notify } = useNightPosNotify()')
  next = next.replace(/const\s*\{\s*notify\s*,\s*snackbar\s*\}\s*=\s*useNightPosNotify\(\)/g, 'const { notify } = useNightPosNotify()')

  if (next !== original) {
    fs.writeFileSync(file, next)
    changed++
    console.log('updated:', path.relative(process.cwd(), file))
  }
}

console.log(`Done. ${changed} file(s) updated.`)
