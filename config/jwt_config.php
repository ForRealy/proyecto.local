<?php
// JWT Configuration
define('JWT_SECRET_KEY', 'your-secret-key-here'); // Change this to a secure random string in production
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 3600); // Token expiration time in seconds (1 hour)
define('JWT_REFRESH_EXPIRATION', 604800); // Refresh token expiration (7 days)

// Cookie settings
define('COOKIE_SECURE', true); // Set to true in production
define('COOKIE_HTTPONLY', true);
define('COOKIE_SAMESITE', 'Strict');
?> 