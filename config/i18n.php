<?php
// Set default locale
$defaultLocale = 'en_US.utf8';

// Get locale from cookie or query parameter
$locale = $_GET['lang'] ?? $_COOKIE['locale'] ?? $defaultLocale;

// Map simple locale codes to full locale names
$localeMap = [
    'en' => 'en_US.utf8',
    'es' => 'es_ES.utf8'
];

// Convert simple locale to full locale name
$fullLocale = $localeMap[$locale] ?? $defaultLocale;

// Set locale cookie with simple locale code
setcookie('locale', $locale, time() + (86400 * 30), '/'); // 30 days

// Set locale environment variables
putenv("LANG=$fullLocale");
putenv("LANGUAGE=$fullLocale");
putenv("LC_ALL=$fullLocale");

// Set locale
if (!setlocale(LC_ALL, $fullLocale)) {
    error_log("Failed to set locale: $fullLocale");
}

// Set text domain
$domain = 'messages';
$localePath = __DIR__ . '/../locale';

// Ensure the locale directory exists
if (!is_dir($localePath)) {
    error_log("Locale directory does not exist: $localePath");
}

// Set the text domain path
if (!bindtextdomain($domain, $localePath)) {
    error_log("Failed to bind text domain: $domain to $localePath");
}

// Set the text domain
if (!textdomain($domain)) {
    error_log("Failed to set text domain: $domain");
}

// Enable gettext
if (!function_exists('gettext')) {
    error_log("gettext is not available");
}

// Test translation
$testTranslation = gettext('Login');
error_log("Test translation in i18n.php: $testTranslation (locale: $fullLocale)");
?> 