<?php
// Set default locale
$defaultLocale = 'en';

// Get locale from cookie or query parameter
$locale = $_GET['lang'] ?? $_COOKIE['locale'] ?? $defaultLocale;

// Validate locale
if (!in_array($locale, ['en', 'es'])) {
    $locale = $defaultLocale;
}

// Set locale cookie
setcookie('locale', $locale, time() + (86400 * 30), '/'); // 30 days

// Set locale
putenv("LANG=$locale");
putenv("LANGUAGE=$locale");
setlocale(LC_ALL, $locale . '.UTF-8');

// Set text domain
bindtextdomain('messages', __DIR__ . '/../locale');
textdomain('messages');
?> 