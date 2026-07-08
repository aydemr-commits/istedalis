import { existsSync } from 'node:fs';
import path from 'node:path';

const requiredFiles = [
  'index.html',
  'app.js',
  'style.css',
  'assets/iste-amblem.png',
  'admin-login.html',
  'staff-login.html',
  'student-login.html',
];

const missingFiles = requiredFiles.filter((file) => !existsSync(path.join(process.cwd(), file)));

if (missingFiles.length) {
  console.error(`Missing required deploy files: ${missingFiles.join(', ')}`);
  process.exit(1);
}

console.log(`Static file check passed: ${requiredFiles.length} files found.`);
