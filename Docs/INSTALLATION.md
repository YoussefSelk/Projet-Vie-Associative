<h1>Installation Guide</h1>

<p>
This setup guide is aligned with the current codebase behavior and environment keys.
</p>

<hr>

<h2>Requirements</h2>
<ul>
  <li>PHP 8.0+ (8.1+ recommended)</li>
  <li>Composer 2.x</li>
  <li>MySQL or MariaDB</li>
  <li>Apache, Nginx, IIS, or PHP built-in server</li>
  <li>PHP extensions: pdo, pdo_mysql, mbstring, intl, fileinfo, openssl, curl</li>
</ul>

<hr>

<h2>Quick Setup</h2>
<ol>
  <li>Install dependencies:
    <pre><code>composer install</code></pre>
  </li>
  <li>Copy environment template:
    <pre><code># Linux/macOS
cp .env.example .env

# Windows PowerShell

Copy-Item .env.example .env</code></pre>

  </li>
  <li>Edit .env:
    <pre><code>APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_HOST=localhost
DB_PORT=3306
DB_NAME=vieasso_local
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=465
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_email_password
MAIL_FROM=noreply@domain.com
MAIL_FROM_NAME="Vie Etudiante EILCO"

SESSION_LIFETIME=3600
CSRF_TOKEN_LIFETIME=7200
COOKIE_SECURE=false
COOKIE_HTTPONLY=true
COOKIE_SAMESITE=Strict
SERVER_TYPE=auto</code></pre>

  </li>
  <li>Create database:
    <pre><code>CREATE DATABASE vieasso_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code></pre>
  </li>
  <li>Run local server:
    <pre><code>php -S localhost:8000</code></pre>
  </li>
</ol>

<p>Open: <code>http://localhost:8000/index.php?page=home</code></p>

<hr>

<h2>Smoke Test</h2>
<ol>
  <li>Open home: <code>?page=home</code></li>
  <li>Open login: <code>?page=login</code></li>
  <li>Open register: <code>?page=register</code></li>
  <li>Open event list: <code>?page=event-list</code></li>
</ol>

<hr>

<h2>Useful Commands</h2>
<pre><code>composer test
composer test-unit
composer test-feature</code></pre>

<hr>

<h2>Troubleshooting</h2>
<ul>
  <li>DB error: verify DB_* keys and database user grants</li>
  <li>403 on POST: verify csrf_token is present and session is active</li>
  <li>404 route: verify route exists in routes/web.php</li>
  <li>Upload issues: verify uploads/ and logs/ are writable</li>
</ul>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
Capture setup screenshots using <a href="screenshots/README.md">screenshots/README.md</a>.
</p>
