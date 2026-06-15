<?php

// Forward static public assets directly
if (file_exists(__DIR__ . '/../public' . $_SERVER['REQUEST_URI'])) {
    return false;
}

// Bootstrap Laravel
require __DIR__ . '/../public/index.php';
