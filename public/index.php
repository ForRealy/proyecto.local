<?php
// Declarar la codificación en la cabecera HTTP para evitar adivinación y modo Quirks.
header('Content-Type: text/html; charset=UTF-8');

// Entrada pública para Apache. Incluye el router principal.
require_once __DIR__ . '/../index.php'; 