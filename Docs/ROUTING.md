<h1>Routing Documentation</h1>

<p>
Routes are defined in routes/web.php and executed by Router::dispatch().
</p>

<hr>

<h2>Route Schema</h2>
<pre><code>'route-name' => [
  'permission' => null|int|array,
  'auth' => bool,
  'methods' => ['GET','POST'],
  'controller' => 'ControllerClass',
  'method' => 'controllerMethod',
  'view' => '/feature/view.php'|null,
  'redirect_if_logged' => bool
]</code></pre>

<hr>

<h2>Dispatch Sequence</h2>
<ol>
  <li>Read and sanitize page</li>
  <li>Verify route exists</li>
  <li>Verify allowed HTTP method</li>
  <li>For POST: verify CSRF token</li>
  <li>Apply auth and permission checks</li>
  <li>Call controller method</li>
  <li>Render configured view</li>
</ol>

<hr>

<h2>Current Route Groups</h2>

<h3>Public</h3>
<ul>
  <li>home, legal, calendar-data</li>
  <li>login, register, logout</li>
  <li>event-list, event-view, event-details (alias)</li>
  <li>clubs-browse, club-view</li>
</ul>

<h3>Authenticated</h3>
<ul>
  <li>profile, profile-edit, dashboard, users-list</li>
  <li>event-report, my-events, update-event</li>
  <li>my-subscriptions, my-clubs, club-edit, club-create</li>
  <li>subscribe-ajax (POST), subscribe (POST), unsubscribe (POST)</li>
</ul>

<h3>Permission-Gated</h3>
<ul>
  <li>[1,3,4,5]: event-create</li>
  <li>1: club-list</li>
  <li>2: export, export-*, export-members, admin-event-reports</li>
  <li>[2,3,4,5]: tutoring</li>
  <li>3: admin, event-analytics, admin-reports, pending-clubs, pending-events, users-list</li>
  <li>5: admin-settings, export-data, admin-users, admin-user-view, admin-audit, admin-database, update-permission (POST), delete-user (POST)</li>
</ul>

<hr>

<h2>Method Constraints</h2>
<ul>
  <li>POST-only: subscribe-ajax, subscribe, unsubscribe, update-permission, delete-user</li>
  <li>All other routes default to GET and POST if not explicitly restricted</li>
</ul>

<hr>

<h2>Examples</h2>
<pre><code>/index.php?page=home
/index.php?page=event-view&id=5
/index.php?page=club-view&id=2
/index.php?page=my-subscriptions</code></pre>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
Capture key route screens according to <a href="screenshots/README.md">screenshots/README.md</a>.
</p>
