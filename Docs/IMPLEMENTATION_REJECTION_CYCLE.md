<h1>Rejection and Correction Cycle</h1>

<p>
This document describes the effective reject/correct/review lifecycle implemented in current validation logic.
</p>

<hr>

<h2>Goal</h2>
<p>
Allow reviewers to reject clubs/events with clear reasons, and allow owners to correct and resubmit.
</p>

<hr>

<h2>Club Rejection Cycle</h2>
<ol>
    <li>Club is submitted and appears in pending validation routes</li>
    <li>Reviewer approves or rejects with remarks</li>
    <li>On rejection, validation columns move to rejected state and motif_refus is stored</li>
    <li>Owner sees rejection context on their club pages and can edit</li>
    <li>After edit, club re-enters pending review based on model/controller workflow</li>
</ol>

<hr>

<h2>Event Rejection Cycle</h2>
<ol>
    <li>Event enters pending queue</li>
    <li>Reviewer validates or rejects</li>
    <li>Rejected event stores refusal motive</li>
    <li>Responsible user can update and resubmit through event edit/update flow</li>
</ol>

<hr>

<h2>Relevant Components</h2>
<ul>
    <li>models/Validation.php: validation/rejection persistence logic</li>
    <li>controllers/ValidationController.php: reviewer actions and decision flow</li>
    <li>views/validation/pending_clubs.php and pending_events.php: reviewer UI</li>
    <li>views/club/my_clubs.php and event-related views: owner feedback visibility</li>
</ul>

<hr>

<h2>Data Expectations</h2>
<ul>
    <li>motif_refus-style fields must be preserved for correction guidance</li>
    <li>Pending selectors must include the expected validation states</li>
    <li>Rejected-only delete operations must stay constrained to rejected state</li>
</ul>

<hr>

<h2>Quality Checks</h2>
<ol>
    <li>Reject a club/event with remarks</li>
    <li>Verify reason appears to the creator</li>
    <li>Edit and resubmit</li>
    <li>Verify it returns to appropriate pending list</li>
    <li>Verify unauthorized users cannot validate or delete</li>
</ol>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
Capture the full cycle using slots in <a href="screenshots/README.md">screenshots/README.md</a> (pending list, reject modal, creator correction view, re-pending state).
</p>
