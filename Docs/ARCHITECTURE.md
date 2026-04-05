<h1>Architecture</h1>

<p>
The application follows a classic PHP MVC architecture with a single entry point and centralized route dispatch.
</p>

<hr>

<h2>Runtime Flow</h2>
<pre><code>HTTP Request
        -> index.php
                 -> config/bootstrap.php
                                -> Environment::load()
                                -> ErrorHandler setup
                                -> Security headers + HTTPS enforcement
                                -> session initialization + hardening
                                -> PDO connection
                                -> load models + controllers
                 -> Router::dispatch()
                                -> route lookup from routes/web.php
                                -> HTTP method check
                                -> CSRF check for POST
                                -> auth/permission check
                                -> controller method call
                                -> view render (if route has view)</code></pre>

<hr>

<h2>Core Components</h2>

<h3>index.php</h3>
<ul>
        <li>Bootstraps application</li>
        <li>Handles custom HTTP error rendering</li>
        <li>Runs router dispatch</li>
</ul>

<h3>config/bootstrap.php</h3>
<ul>
        <li>Defines project paths and constants</li>
        <li>Loads environment and security settings</li>
        <li>Configures sessions with secure cookie options</li>
        <li>Initializes database and utility helper functions</li>
</ul>

<h3>config/Router.php</h3>
<ul>
        <li>Loads route map from routes/web.php</li>
        <li>Validates allowed HTTP methods</li>
        <li>Validates CSRF tokens for POST requests</li>
        <li>Applies auth and permission gates</li>
        <li>Calls controller method then includes view</li>
</ul>

<h3>config/Security.php</h3>
<ul>
        <li>Sets security headers</li>
        <li>Generates and validates CSRF tokens</li>
        <li>Detects HTTPS (direct/proxy) and enforces HTTPS in production</li>
</ul>

<hr>

<h2>Project Layout</h2>
<ul>
        <li>config/: bootstrap, security, environment, error handling, router</li>
        <li>controllers/: request orchestration and business actions</li>
        <li>models/: SQL and domain data behavior</li>
        <li>views/: server-rendered templates by feature area</li>
        <li>routes/web.php: route definition map</li>
        <li>css/ + assets/js/: frontend presentation and behavior</li>
        <li>tests/: feature and unit test suites</li>
</ul>

<hr>

<h2>Authorization Model</h2>
<table>
        <thead>
                <tr><th>Permission</th><th>Role</th><th>Typical Scope</th></tr>
        </thead>
        <tbody>
                <tr><td>0</td><td>Visitor</td><td>Public routes only</td></tr>
                <tr><td>1</td><td>Member</td><td>Create events, club member features</td></tr>
                <tr><td>2</td><td>Tutor</td><td>Tutoring pages, export dashboard</td></tr>
                <tr><td>3</td><td>BDE/Admin-level</td><td>Validation and admin dashboard</td></tr>
                <tr><td>4</td><td>Extended internal role</td><td>Used on selected array-gated routes</td></tr>
                <tr><td>5</td><td>Super Admin</td><td>Settings, audit, user permission updates</td></tr>
        </tbody>
</table>

<hr>

<h2>State-Driven Workflows</h2>
<ul>
        <li>Clubs and events can move through pending, approved, rejected states</li>
        <li>Validation models/controllers coordinate remarks and decision fields</li>
        <li>Rejected entities can be corrected and re-submitted based on model logic</li>
</ul>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
Architecture documentation screenshots (request-flow diagram, admin dashboard snapshot) are tracked in <a href="screenshots/README.md">screenshots/README.md</a>.
</p>
