<?php
require_once 'vendor/autoload.php';
$loader = new \Twig\Loader\FilesystemLoader('views');
$twig = new \Twig\Environment($loader, ['cache' => false]); // Desactiva cache en desarrollo

echo $twig->render('home.html.twig', ['name' => 'Alejandro']);