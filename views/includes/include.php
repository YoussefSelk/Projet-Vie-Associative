<?php
// Don't start session here - it's already started in bootstrap.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
