<h1>Contributing Guide</h1>

<p>
Contributions are expected to be production-grade: safe, minimal, testable, and documented.
</p>

<hr>

<h2>Workflow</h2>
<ol>
  <li>Create a focused branch</li>
  <li>Implement small, reviewable changes</li>
  <li>Run tests and manual verification</li>
  <li>Update docs for any behavior change</li>
  <li>Submit PR with clear evidence</li>
</ol>

<hr>

<h2>Local Setup</h2>
<pre><code>composer install

# Linux/macOS
cp .env.example .env

# Windows PowerShell
Copy-Item .env.example .env</code></pre>

<p>
Configure .env using INSTALLATION.md and confirm app starts.
</p>

<hr>

<h2>Code Quality Rules</h2>
<ul>
  <li>Do not bypass route auth/permission checks</li>
  <li>Use prepared SQL statements only</li>
  <li>Escape output in views for user-controlled data</li>
  <li>Keep changes scoped to the target requirement</li>
  <li>Preserve existing architecture boundaries (model/controller/view)</li>
</ul>

<hr>

<h2>Required Validation Before PR</h2>
<ol>
  <li>Run tests:
    <pre><code>composer test</code></pre>
  </li>
  <li>Verify modified routes and permissions manually</li>
  <li>Verify forms still include CSRF tokens on POST actions</li>
  <li>Confirm no secret is committed</li>
  <li>Update docs under Docs/</li>
</ol>

<hr>

<h2>Commit Format</h2>
<pre><code>type: short summary

Examples:
feat: add export filter by date window
fix: enforce POST-only delete-user flow
docs: update routing matrix for tutor export routes</code></pre>

<hr>

<h2>PR Checklist Template</h2>
<pre><code>## What changed
- 

## Why
- 

## Routes/permissions impacted
- 

## Tests executed
- composer test
- manual checks: ...

## Docs updated
- Docs/...

## Screenshots
- Docs/screenshots/... (if UI changed)</code></pre>

<hr>

<h2>Documentation Update Rule</h2>
<p>
If you change route behavior, env keys, permission rules, validation states, or UI workflow,
update documentation in the same PR.
</p>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
Use slots from <a href="screenshots/README.md">screenshots/README.md</a> for UI-related contributions.
</p>
