<?php
// Local bridge for frontend fetch() calls.
// Keeps existing UI URLs like /public/api.php?controller=post&action=index
// inside this merged application instead of proxying to a separate project.

require_once __DIR__ . '/../config/session.php';

chdir(__DIR__ . '/..');

if (!isset($_GET['format'])) {
    $_GET['format'] = 'json';
}

require __DIR__ . '/../index.php';
