<h1>Production Deployment Guide</h1>

<p>
Deployment instructions aligned with config/bootstrap.php, config/Environment.php, config/Security.php, and routes/web.php.
</p>

<hr>

<h2>Checklist</h2>
<ol>
  <li>Install production dependencies</li>
  <li>Create production .env</li>
  <li>Set secure permissions</li>
  <li>Configure web server rewrite and HTTPS</li>
  <li>Run smoke tests</li>
</ol>

<hr>

<h2>Install Dependencies</h2>
<pre><code>composer install --no-dev --optimize-autoloader</code></pre>

<h2>Production .env</h2>
<pre><code>APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example

DB_HOST=prod-db-host
DB_PORT=3306
DB_NAME=prod_db_name
DB_USER=prod_db_user
DB_PASS=strong_password
DB_CHARSET=utf8mb4

MAIL_HOST=smtp.provider.example
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=no-reply@your-domain.example
MAIL_PASSWORD=mail_secret
MAIL_FROM=no-reply@your-domain.example
MAIL_FROM_NAME="Vie Etudiante EILCO"
MAIL_TIMEOUT=15
MAIL_VERIFY_PEER=true
MAIL_VERIFY_PEER_NAME=true
MAIL_ALLOW_SELF_SIGNED=false

SESSION_LIFETIME=3600
CSRF_TOKEN_LIFETIME=7200
COOKIE_SECURE=true
COOKIE_HTTPONLY=true
COOKIE_SAMESITE=Strict
SERVER_TYPE=auto</code></pre>

<hr>

<h2>Mail Security Requirements</h2>
<ol>
  <li>Use a dedicated mailbox account (no personal mailbox credentials)</li>
  <li>Rotate SMTP password before go-live if it has been exposed</li>
  <li>Ensure DNS records are configured (SPF, DKIM, DMARC) for better Gmail deliverability</li>
  <li>Keep TLS/certificate verification enabled in production</li>
  <li>Monitor logs for repeated SMTP failures and authentication errors</li>
</ol>

<p><strong>Compatibility note:</strong> the application supports both <code>MAIL_*</code> and legacy <code>SMTP_*</code> variables.</p>

<hr>

<h2>Permissions</h2>
<ul>
  <li>Writable directories: uploads/, logs/</li>
  <li>Restricted file: .env</li>
</ul>

<p>Linux/macOS example:</p>
<pre><code>find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 775 uploads logs
chmod 600 .env</code></pre>

<hr>

<h2>Web Server Requirements</h2>
<ul>
  <li>All requests route through index.php</li>
  <li>Block direct access to .env and hidden files</li>
  <li>Block PHP execution in uploads/</li>
  <li>HTTPS enabled and valid certificate installed</li>
</ul>

<hr>

<h2>Post-Deployment Validation</h2>
<ol>
  <li>Open home/login/register</li>
  <li>Confirm HTTPS redirect and secure cookies</li>
  <li>Submit POST form and verify CSRF behavior</li>
  <li>Validate upload and export paths</li>
  <li>Check custom 403/404/500 pages</li>
</ol>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
Store deployment evidence screenshots using <a href="screenshots/README.md">screenshots/README.md</a>.
</p>
