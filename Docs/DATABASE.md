<h1>Database Documentation</h1>

<p>
This document describes the active conceptual schema used by current models and workflows.
</p>

<hr>

<h2>Core Entities</h2>
<ul>
  <li>users: identity, authentication, permission, profile</li>
  <li>fiche_club: club metadata, owner, tutor, validation fields</li>
  <li>membres_club: user-club memberships and roles</li>
  <li>fiche_event: event metadata, organizer club, validation/report fields</li>
  <li>abonnements: user subscriptions to events</li>
</ul>

<hr>

<h2>Relationship Overview</h2>
<pre><code>users (1) -> (N) fiche_club.responsable
users (N) <-> (N) fiche_club via membres_club
fiche_club (1) -> (N) fiche_event
users (N) <-> (N) fiche_event via abonnements</code></pre>

<hr>

<h2>Validation-State Columns</h2>
<ul>
  <li>validation_bde</li>
  <li>validation_tuteur</li>
  <li>validation_admin</li>
  <li>validation_finale</li>
  <li>motif_refus and related remarks fields</li>
</ul>

<p>
These fields drive pending/approved/rejected behavior shown in validation and owner views.
</p>

<hr>

<h2>Lifecycle Patterns</h2>

<h3>Club lifecycle</h3>
<ol>
  <li>Create club</li>
  <li>Pending validation</li>
  <li>Approve or reject with remarks</li>
  <li>If rejected, owner edits and resubmits</li>
</ol>

<h3>Event lifecycle</h3>
<ol>
  <li>Create event</li>
  <li>Pending validation</li>
  <li>Approve or reject with remarks</li>
  <li>Subscribers managed in abonnements</li>
  <li>Post-event report data attached if provided</li>
</ol>

<hr>

<h2>Data Integrity Expectations</h2>
<ul>
  <li>Foreign keys should preserve relationship integrity</li>
  <li>User email uniqueness should be enforced</li>
  <li>Membership and subscription records should remain de-duplicated</li>
  <li>Rejection reasons should remain available for correction workflows</li>
</ul>

<hr>

<h2>Query Behavior in Code</h2>
<ul>
  <li>Public pages query validated clubs/events only</li>
  <li>Validation pages query pending/rejected sets</li>
  <li>Admin/export pages use joined data from users/clubs/events</li>
  <li>Subscriptions are counted for analytics and detail pages</li>
</ul>

<hr>

<h2>Developer Rule</h2>
<p>
If a column/table change affects model SQL, update tests and this file in the same PR.
</p>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
Use screenshot names listed in <a href="screenshots/README.md">screenshots/README.md</a>.
</p>
