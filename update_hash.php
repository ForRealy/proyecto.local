<?php
require_once __DIR__ . '/config/database.php';

$new_hash = password_hash("admin123", PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE Users SET password = :new_hash WHERE username = 'admin'");
$stmt->execute(['new_hash' => $new_hash]);
echo "Hash actualizado para el usuario 'admin' (nuevo hash: " . $new_hash . ").\n"; 