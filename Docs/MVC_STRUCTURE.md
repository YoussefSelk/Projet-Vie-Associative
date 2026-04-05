<h1>MVC Structure</h1>

<p>
This document describes how MVC responsibilities are implemented in this project.
</p>

<hr>

<h2>Model Layer</h2>
<ul>
  <li>Owns SQL and data-level business rules</li>
  <li>Represents users, clubs, events, subscriptions, reports, and validation flows</li>
</ul>

<h2>Controller Layer</h2>
<ul>
  <li>Coordinates request processing</li>
  <li>Calls model methods</li>
  <li>Returns view-ready arrays</li>
</ul>

<h2>View Layer</h2>
<ul>
  <li>Renders HTML templates grouped by feature in views/</li>
  <li>Uses extracted data from controller return arrays</li>
  <li>Uses shared includes for head/header/footer/nav</li>
</ul>

<hr>

<h2>Cross-Cutting Layers</h2>
<ul>
  <li>Router: dispatch, method checks, auth and permission checks</li>
  <li>Security: headers, HTTPS enforcement, CSRF lifecycle</li>
  <li>Environment: .env loading and typed config</li>
  <li>ErrorHandler: consistent error rendering and logs</li>
</ul>

<hr>

<h2>Feature Extension Pattern</h2>
<ol>
  <li>Add/extend model method in models/</li>
  <li>Add controller action in controllers/</li>
  <li>Create view under views/{feature}/</li>
  <li>Add route in routes/web.php with proper auth/permission/method metadata</li>
  <li>Add tests in tests/Feature or tests/Unit</li>
  <li>Update Docs/API_REFERENCE.md and Docs/ROUTING.md</li>
</ol>

<hr>

<h2>Anti-Patterns</h2>
<ul>
  <li>SQL in views</li>
  <li>Bypassing route security gates</li>
  <li>Adding POST forms without CSRF token</li>
  <li>Copying logic across controllers instead of shared model methods</li>
</ul>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
Use filenames listed in <a href="screenshots/README.md">screenshots/README.md</a>.
</p>
