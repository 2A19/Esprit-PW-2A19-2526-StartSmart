<?php
// Shared session bootstrap for the integrated frontend and RH backend.
if (session_status() === PHP_SESSION_NONE) {
    session_name('STARTSMART_SESSION');
    session_start();
}

