<h1>Security Documentation</h1>

<p>
Security is implemented with multiple layers in bootstrap, Security, Router, and controller/model logic.
</p>

<hr>

<h2>Security Layers</h2>
<ol>
  <li>HTTP security headers</li>
  <li>HTTPS enforcement in production</li>
  <li>Hardened session cookies and renewal strategy</li>
  <li>CSRF token checks for POST routes</li>
  <li>Route-level auth and permission checks</li>
  <li>Prepared SQL and output escaping in views</li>
</ol>

<hr>

<h2>Headers and HTTPS</h2>
<ul>
  <li>Security::setHeaders() applies browser hardening headers</li>
  <li>Security::enforceHttps() redirects HTTP in production contexts</li>
  <li>HTTPS detection supports reverse proxy scenarios</li>
</ul>

<hr>

<h2>Session Security</h2>
<ul>
  <li>HttpOnly cookies enabled</li>
  <li>Secure cookies enabled for HTTPS/production</li>
  <li>SameSite policy configurable via .env</li>
  <li>Session timeout and periodic ID regeneration enabled</li>
</ul>

<hr>

<h2>CSRF Controls</h2>
<ul>
  <li>POST requests are validated against csrf_token</li>
  <li>Token lifetime configurable via CSRF_TOKEN_LIFETIME</li>
  <li>Invalid token results in 403 error handling</li>
</ul>

<hr>

<h2>Authorization Model</h2>
<ul>
  <li>Route metadata defines auth and permission requirements</li>
  <li>validateSession() checks user session existence</li>
  <li>checkPermission() supports numeric threshold and explicit allowed lists</li>
  <li>Unauthorized access attempts are logged</li>
</ul>

<hr>

<h2>Password and Login Safety</h2>
<ul>
  <li>Password hashing uses bcrypt in user/auth flows</li>
  <li>Login flow includes throttling helpers</li>
  <li>Secrets are loaded from .env and must never be committed</li>
</ul>

<hr>

<h2>Email Transport Security</h2>
<ul>
  <li>SMTP subject is normalized to prevent header injection</li>
  <li>Both HTML and text/plain bodies are generated for client compatibility and safer fallback</li>
  <li>TLS mode is configurable with <code>MAIL_ENCRYPTION</code> (ssl/tls)</li>
  <li>Certificate verification is configurable and enabled by default</li>
  <li>Sender and recipient addresses are validated before transmission</li>
  <li>SMTP timeout is bounded to avoid hung worker processes</li>
</ul>

<hr>

<h2>Operational Security Checklist</h2>
<ol>
  <li>APP_ENV=production and APP_DEBUG=false in production</li>
  <li>COOKIE_SECURE=true under HTTPS</li>
  <li>.env inaccessible from web</li>
  <li>uploads/ does not execute PHP files</li>
  <li>All state-changing forms include csrf_token</li>
  <li>Role-sensitive routes tested with low/high permission accounts</li>
  <li>SMTP credentials rotated immediately after any accidental exposure</li>
</ol>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
Use names from <a href="screenshots/README.md">screenshots/README.md</a> for security evidence captures.
</p>
