<h1>Vie Etudiante EILCO</h1>
<p><strong>Student clubs and events platform (PHP MVC)</strong></p>

<p>
This repository contains the active application used to manage clubs, events, subscriptions,
validation workflows, exports, and administration.
</p>

<hr>

<h2>Quick Start</h2>
<ol>
  <li>Install dependencies:
    <pre><code>composer install</code></pre>
  </li>
  <li>Create local environment file:
    <pre><code>
      
# Linux/macOS
cp .env.example .env

# Windows PowerShell

Copy-Item .env.example .env</code></pre>

  </li>
  <li>Edit .env with your local credentials and URLs (see sample below)</li>
  <li>Create database:
    <pre><code>CREATE DATABASE vieasso_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code></pre>
  </li>
  <li>Run local server:
    <pre><code>php -S localhost:8000</code></pre>
  </li>
  <li>Open:
    <pre><code>http://localhost:8000/index.php?page=home</code></pre>
  </li>
</ol>

<hr>

<h2>Simple Technical Help (.env)</h2>
<pre><code>APP_ENV=development
APP_DEBUG=true
APP_TIMEZONE=Europe/Paris
APP_URL=http://localhost:8000
APP_NAME="Vie Etudiante EILCO"

DB_HOST=localhost
DB_PORT=3306
DB_NAME=vieasso_local
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_email_password
MAIL_FROM=noreply@domain.com
MAIL_FROM_NAME="Vie Etudiante EILCO"
MAIL_TIMEOUT=15
MAIL_VERIFY_PEER=true
MAIL_VERIFY_PEER_NAME=true
MAIL_ALLOW_SELF_SIGNED=false

SESSION_LIFETIME=3600
CSRF_TOKEN_LIFETIME=7200
COOKIE_SECURE=false
COOKIE_HTTPONLY=true
COOKIE_SAMESITE=Strict
SERVER_TYPE=auto</code></pre>

<hr>

<h2>Main Features</h2>
<ul>
  <li>Centralized route map in routes/web.php with auth and permission metadata</li>
  <li>Secure session + CSRF + security-header protections</li>
  <li>Club and event lifecycle with validation and rejection workflows</li>
  <li>Event subscriptions including AJAX toggle endpoint</li>
  <li>CSV exports with permission control and rate-limit safeguards</li>
  <li>Admin pages for analytics, audit, settings, and user management</li>
  <li>Layered CSS architecture (core/components/layout/pages/responsive)</li>
</ul>

<hr>

<h2>Documentation Index</h2>
<table>
  <thead>
    <tr><th>Document</th><th>Purpose</th></tr>
  </thead>
  <tbody>
    <tr><td><a href="Docs/ARCHITECTURE.md">Docs/ARCHITECTURE.md</a></td><td>Runtime architecture and startup flow</td></tr>
    <tr><td><a href="Docs/MVC_STRUCTURE.md">Docs/MVC_STRUCTURE.md</a></td><td>MVC implementation responsibilities</td></tr>
    <tr><td><a href="Docs/ROUTING.md">Docs/ROUTING.md</a></td><td>Current route inventory and dispatch rules</td></tr>
    <tr><td><a href="Docs/API_REFERENCE.md">Docs/API_REFERENCE.md</a></td><td>Controllers and models API map</td></tr>
    <tr><td><a href="Docs/DATABASE.md">Docs/DATABASE.md</a></td><td>Data model and state-driven workflows</td></tr>
    <tr><td><a href="Docs/SECURITY.md">Docs/SECURITY.md</a></td><td>Security controls and operational checklist</td></tr>
    <tr><td><a href="Docs/INSTALLATION.md">Docs/INSTALLATION.md</a></td><td>Local installation and troubleshooting</td></tr>
    <tr><td><a href="Docs/PRODUCTION_DEPLOYMENT.md">Docs/PRODUCTION_DEPLOYMENT.md</a></td><td>Production deployment checklist</td></tr>
    <tr><td><a href="Docs/CSS_ARCHITECTURE.md">Docs/CSS_ARCHITECTURE.md</a></td><td>CSS layering and loading rules</td></tr>
    <tr><td><a href="Docs/SWEETALERT2_INTEGRATION.md">Docs/SWEETALERT2_INTEGRATION.md</a></td><td>SweetAlert helper usage</td></tr>
    <tr><td><a href="Docs/IMPLEMENTATION_REJECTION_CYCLE.md">Docs/IMPLEMENTATION_REJECTION_CYCLE.md</a></td><td>Reject/correct/review process details</td></tr>
    <tr><td><a href="Docs/CONTRIBUTING.md">Docs/CONTRIBUTING.md</a></td><td>Contribution standards and PR checks</td></tr>
    <tr><td><a href="Docs/screenshots/README.md">Docs/screenshots/README.md</a></td><td>Screenshot placeholders and naming</td></tr>
  </tbody>
</table>

<hr>

<h2>Test Commands</h2>
<pre><code>composer test
composer test-unit
composer test-feature
composer security:audit</code></pre>

<hr>

<h2>Scheduled Event Reminders</h2>
<p>
The platform supports automated reminder emails for event subscribers:
</p>
<ul>
  <li>first reminder: 48 hours before event start</li>
  <li>second reminder: 24 hours before event start</li>
</ul>

<p>Run manually:</p>
<pre><code>php scripts/send_event_reminders.php
php scripts/send_event_reminders.php --window=30</code></pre>

<p>Recommended production schedule (every 15 minutes):</p>
<pre><code>*/15 * * * * php /path/to/project/scripts/send_event_reminders.php >> /path/to/project/logs/reminders.log 2>&1</code></pre>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
Use screenshot slots defined in <a href="Docs/screenshots/README.md">Docs/screenshots/README.md</a>.
</p>
