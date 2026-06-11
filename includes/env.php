<?php
/**
 * includes/env.php
 * Minimal .env loader. No external dependency.
 * Populates getenv()/$_ENV/$_SERVER. Idempotent.
 */

if (!function_exists('loadEnv')) {
    function loadEnv($file) {
        static $loaded = false;
        if ($loaded || !is_readable($file)) {
            return;
        }
        $loaded = true;
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            list($k, $v) = array_map('trim', explode('=', $line, 2));
            // strip surrounding quotes
            if (strlen($v) >= 2 && (($v[0] === '"' && substr($v, -1) === '"') || ($v[0] === "'" && substr($v, -1) === "'"))) {
                $v = substr($v, 1, -1);
            }
            // do not overwrite existing real env vars
            if (getenv($k) === false) {
                putenv("$k=$v");
                $_ENV[$k] = $v;
                $_SERVER[$k] = $v;
            }
        }
    }
}

// Load .env from project root if present
loadEnv(dirname(__DIR__) . '/.env');
