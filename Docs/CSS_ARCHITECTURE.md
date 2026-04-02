<h1>CSS Architecture</h1>

<p>
CSS is modular and loaded through views/includes/head.php with a core-first and page-specific strategy.
</p>

<hr>

<h2>Directory Layers</h2>
<ul>
  <li>css/core/: global variables, base styles, compatibility</li>
  <li>css/layout/: header, navbar, footer</li>
  <li>css/components/: reusable UI units (forms, tables, search, calendar, buttons, shared)</li>
  <li>css/pages/: page-scope styles (home, auth, clubs, events, admin, etc.)</li>
  <li>css/responsive.css: final responsive overrides</li>
</ul>

<hr>

<h2>Actual Load Order</h2>
<ol>
  <li>core/variables.css</li>
  <li>core/base.css</li>
  <li>layout/header.css</li>
  <li>layout/navbar.css</li>
  <li>layout/footer.css</li>
  <li>Page-selected files via $pageCss map</li>
  <li>responsive.css (last)</li>
</ol>

<hr>

<h2>$pageCss Mapping</h2>
<p>
In head.php, each key maps to an actual file path.
</p>
<pre><code>// examples
'shared'      -> css/components/shared.css
'buttons'     -> css/components/buttons.css
'forms'       -> css/components/forms.css
'tables'      -> css/components/tables.css
'search'      -> css/components/search.css
'pagination'  -> css/components/pagination.css
'calendar'    -> css/components/calendar.css
'home'        -> css/pages/home.css
'auth'        -> css/pages/auth.css
'clubs'       -> css/pages/clubs.css
'events'      -> css/pages/events.css
'profiles'    -> css/pages/profiles.css
'dashboard'   -> css/pages/dashboard.css
'admin'       -> css/pages/admin.css
'validation'  -> css/pages/validation.css
'errors'      -> css/pages/errors.css
'export'      -> css/pages/export.css</code></pre>

<hr>

<h2>Usage Pattern in Views</h2>
<pre><code>&lt;?php
$pageCss = ['shared', 'buttons', 'forms', 'clubs'];
include VIEWS_PATH . '/includes/head.php';
?&gt;</code></pre>

<p>
Keep list minimal to avoid unnecessary CSS payload.
</p>

<hr>

<h2>Practical Rules</h2>
<ul>
  <li>Put tokens and constants in core/variables.css only</li>
  <li>Put reusable classes in components/, not pages/</li>
  <li>Put route- or screen-specific styles in pages/</li>
  <li>Avoid duplicating selector blocks across pages</li>
  <li>Let responsive.css handle breakpoint-level overrides</li>
</ul>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
When documenting CSS changes, store before/after screenshots using names from <a href="screenshots/README.md">screenshots/README.md</a>.
</p>
