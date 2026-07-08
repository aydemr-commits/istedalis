import { createServer } from 'node:http';
import { createReadStream, existsSync, readdirSync, statSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const port = Number(process.env.PORT || 10000);

const pages = new Set([
  'admin-dashboard.html',
  'admin-login.html',
  'backup.html',
  'index.html',
  'staff-dashboard.html',
  'staff-login.html',
  'student-dashboard.html',
  'student-dive-entry.html',
  'student-login.html',
  'student-report.html',
]);

const mimeTypes = new Map([
  ['.css', 'text/css; charset=utf-8'],
  ['.html', 'text/html; charset=utf-8'],
  ['.js', 'text/javascript; charset=utf-8'],
  ['.png', 'image/png'],
]);

createServer((request, response) => {
  const url = new URL(request.url || '/', `http://${request.headers.host || 'localhost'}`);

  setSecurityHeaders(response);

  if (url.pathname === '/up') {
    response.writeHead(200, { 'Content-Type': 'text/plain; charset=utf-8' });
    response.end('ok');
    return;
  }

  const filePath = resolvePublicPath(url.pathname);
  streamFile(filePath, response);
}).listen(port, () => {
  console.log(`istedalis is running on port ${port}`);
  console.log(`server directory: ${__dirname}`);
  console.log(`working directory: ${process.cwd()}`);
  console.log(`index.html present: ${existsSync(path.join(__dirname, 'index.html'))}`);
  console.log(`visible files: ${listVisibleFiles().join(', ')}`);
});

function resolvePublicPath(pathname) {
  const cleanPath = decodeURIComponent(pathname).replace(/^\/+/, '');

  if (pathname === '/' || pathname === '') {
    return path.join(__dirname, 'index.html');
  }

  if (pages.has(cleanPath) || cleanPath === 'style.css' || cleanPath === 'app.js') {
    return path.join(__dirname, cleanPath);
  }

  if (cleanPath.startsWith('assets/')) {
    const assetPath = path.normalize(path.join(__dirname, cleanPath));
    if (assetPath.startsWith(path.join(__dirname, 'assets'))) {
      return assetPath;
    }
  }

  return path.join(__dirname, 'index.html');
}

function streamFile(filePath, response) {
  try {
    const fileStat = statSync(filePath);
    if (!fileStat.isFile()) throw new Error('Not a file');

    response.writeHead(200, {
      'Content-Type': mimeTypes.get(path.extname(filePath)) || 'application/octet-stream',
      'Content-Length': fileStat.size,
    });
    createReadStream(filePath).pipe(response);
  } catch (error) {
    response.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
    response.end(`Not found: ${path.basename(filePath)}`);
  }
}

function setSecurityHeaders(response) {
  response.setHeader('X-Frame-Options', 'DENY');
  response.setHeader('X-Content-Type-Options', 'nosniff');
  response.setHeader('Referrer-Policy', 'same-origin');
}

function listVisibleFiles() {
  try {
    return readdirSync(__dirname)
      .filter((name) => !name.startsWith('.'))
      .slice(0, 40);
  } catch (error) {
    return ['unable-to-read-directory'];
  }
}
