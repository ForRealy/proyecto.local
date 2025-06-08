<?php
// Configuración de JWT (ajusta los valores según tu entorno)
define("JWT_SECRET", "tu_clave_secreta_jwt");
define("JWT_EXPIRATION", 3600); // 1 hora (en segundos)
define("JWT_REFRESH_EXPIRATION", 86400); // 1 día (en segundos)

// Configuración de cookies (ajusta según tu entorno)
define("COOKIE_SECURE", true); // (true en producción, false en desarrollo)
define("COOKIE_HTTPONLY", true);
define("COOKIE_SAMESITE", "Strict");

// JWT Configuration
define('JWT_ALGORITHM', 'HS256');
?> 