import { createServer } from 'node:http';
import { createReadStream, existsSync, mkdirSync, readFileSync, readdirSync, statSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const port = Number(process.env.PORT || 10000);
const dataDir = process.env.DATA_DIR || path.join(__dirname, 'storage');
const dataFile = process.env.DATA_FILE || path.join(dataDir, 'site-data.json');
const maxBodyBytes = 1024 * 1024;
const writeMethods = new Set(['POST', 'PUT', 'PATCH', 'DELETE']);
const rateLimitHits = new Map();

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

  if (!isAllowedRequest(request, response)) {
    return;
  }

  if (url.pathname === '/up') {
    response.writeHead(200, { 'Content-Type': 'text/plain; charset=utf-8' });
    response.end('ok');
    return;
  }

  if (url.pathname === '/api/data') {
    handleDataApi(request, response);
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
  response.setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
  response.setHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
  response.setHeader('Cross-Origin-Opener-Policy', 'same-origin');
  response.setHeader('Cross-Origin-Resource-Policy', 'same-origin');
  response.setHeader('Content-Security-Policy', "default-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; connect-src 'self'");
  response.setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
}

function isAllowedRequest(request, response) {
  if (writeMethods.has(request.method || '')) {
    const origin = request.headers.origin;
    const host = request.headers.host;
    if (origin && host && new URL(origin).host !== host) {
      response.writeHead(403, { 'Content-Type': 'text/plain; charset=utf-8' });
      response.end('Forbidden origin');
      return false;
    }
  }

  const remote = request.socket.remoteAddress || 'unknown';
  const key = `${remote}|${Math.floor(Date.now() / 60000)}`;
  const hits = (rateLimitHits.get(key) || 0) + 1;
  rateLimitHits.set(key, hits);
  if (hits > 240) {
    response.writeHead(429, { 'Content-Type': 'text/plain; charset=utf-8' });
    response.end('Too many requests');
    return false;
  }

  return true;
}

function handleDataApi(request, response) {
  if (request.method === 'GET') {
    response.writeHead(200, {
      'Content-Type': 'application/json; charset=utf-8',
      'Cache-Control': 'no-store',
    });
    response.end(readDataFile());
    return;
  }

  if (request.method !== 'PUT') {
    response.writeHead(405, { 'Allow': 'GET, PUT', 'Content-Type': 'text/plain; charset=utf-8' });
    response.end('Method not allowed');
    return;
  }

  let body = '';
  request.on('data', (chunk) => {
    body += chunk;
    if (Buffer.byteLength(body) > maxBodyBytes) {
      request.destroy();
    }
  });
  request.on('end', () => {
    try {
      const parsed = JSON.parse(body);
      const safeData = normalizeDataForStorage(parsed);
      mkdirSync(dataDir, { recursive: true });
      writeFileSync(dataFile, JSON.stringify(safeData, null, 2), 'utf8');
      response.writeHead(204);
      response.end();
    } catch (error) {
      response.writeHead(400, { 'Content-Type': 'text/plain; charset=utf-8' });
      response.end('Invalid data');
    }
  });
}

function readDataFile() {
  try {
    return readFileSync(dataFile, 'utf8');
  } catch (error) {
    return JSON.stringify({ students: [], staff: [], admins: [{ id: 1, admin_no: '3001', password: '', password_hash: 'c9b018966de06d7ac7a5aba21cd4f14b096aa0697ea97b3e6da73fcc5fb80d10', name: 'Sistem', surname: 'Yonetici', role_name: 'admin', approval_status: 'approved' }], dives: [] });
  }
}

function normalizeDataForStorage(data) {
  if (!data || typeof data !== 'object') throw new Error('Invalid root');
  return {
    students: Array.isArray(data.students) ? data.students : [],
    staff: Array.isArray(data.staff) ? data.staff : [],
    admins: Array.isArray(data.admins) && data.admins.length ? data.admins : [{ id: 1, admin_no: '3001', password: '', password_hash: 'c9b018966de06d7ac7a5aba21cd4f14b096aa0697ea97b3e6da73fcc5fb80d10', name: 'Sistem', surname: 'Yonetici', role_name: 'admin', approval_status: 'approved' }],
    dives: Array.isArray(data.dives) ? data.dives : [],
  };
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
