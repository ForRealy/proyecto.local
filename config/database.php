<?php
// Configuración de la base de datos (ajusta los valores según tu entorno)
try {
    error_log("Intentando conectar a la base de datos...");
    $db = new PDO('mysql:host=localhost;dbname=PokemonDB;charset=utf8', 'pokemon_user', 'pokemon123');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Conexión a la base de datos exitosa");
} catch (PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    throw $e;
} 