<?php
session_start();
require_once 'vendor/autoload.php'; // Asegúrate de tener instalado Twig vía Composer
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Configuración de Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views');
$twig = new \Twig\Environment($loader);

// Configuración de la base de datos (ajusta los valores)
$db = new PDO('mysql:host=localhost;dbname=PokemonDB;charset=utf8', 'usuario', 'password');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Definición de colores para cada tipo (si lo necesitas en alguna vista)
$typeColors = [
    'Fire'     => 'bg-danger',
    'Water'    => 'bg-primary',
    'Electric' => 'bg-warning text-dark',
    'Grass'    => 'bg-success',
    'Ice'      => 'bg-info',
    'Fighting' => 'bg-dark',
    'Poison'   => 'bg-purple',
    'Ground'   => 'bg-brown',
    'Flying'   => 'bg-sky',
    'Psychic'  => 'bg-violet',
    'Bug'      => 'bg-lime',
    'Rock'     => 'bg-stone',
    'Ghost'    => 'bg-deep-purple',
    'Dragon'   => 'bg-indigo',
    'Dark'     => 'bg-dark',
    'Steel'    => 'bg-secondary',
    'Fairy'    => 'bg-pink'
];

// Obtener la ruta solicitada
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($request) {
    case '/':
        // Página principal (listado de Pokémon con LEFT JOIN para incluir todos)
        try {
            $stmt = $db->prepare("
                SELECT p.Number, p.Name, p.ImagePath, 
                       GROUP_CONCAT(t.TypeName ORDER BY t.TypeID SEPARATOR ', ') as tipos 
                FROM Pokemon p
                LEFT JOIN PokemonTypes pt ON p.Number = pt.PokemonNumber
                LEFT JOIN Types t ON pt.TypeID = t.TypeID
                GROUP BY p.Number
            ");
            $stmt->execute();
            $pokemons = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($pokemons as &$pokemon) {
                if ($pokemon['tipos']) {
                    $tipoArray = explode(', ', $pokemon['tipos']);
                    $tipoArray = array_map(function($type) {
                        return ucwords(strtolower($type));
                    }, $tipoArray);
                    $pokemon['tipos'] = $tipoArray;
                } else {
                    $pokemon['tipos'] = [];
                }
            }

            echo $twig->render('home.html.twig', [
                'pokemons'   => $pokemons,
                'typeColors' => $typeColors,
                'user'       => $_SESSION['user'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Error en consulta: " . $e->getMessage());
            http_response_code(500);
            echo $twig->render('error.html.twig');
        }
        break;

    case '/login':
        // Ruta de login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';

            // Si el usuario es admin, redirigimos al panel de administración
            if ($username === 'admin' && $password === 'admin') {
                $_SESSION['user'] = $username;
                $_SESSION['role'] = 'admin';
                header("Location: /admin/dashboard");
                exit;
            } else {
                // Usuario normal, redirige a la página principal
                $_SESSION['user'] = $username;
                $_SESSION['role'] = 'user';
                header("Location: /");
                exit;
            }
        } else {
            echo $twig->render('login.html.twig');
        }
        break;

    case '/admin/dashboard':
        // Panel de administración: solo para administradores
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: /");
            exit;
        }
        try {
            // Consulta para el gráfico: cantidad de Pokémon por tipo
            $stmtCounts = $db->prepare("
    SELECT t.TypeName AS `TypeName`, COUNT(pt.PokemonNumber) AS `count`
    FROM PokemonTypes pt
    JOIN Types t ON pt.TypeID = t.TypeID
    GROUP BY t.TypeName
");
$stmtCounts->execute();
$typeCounts = $stmtCounts->fetchAll(PDO::FETCH_ASSOC);


            // Consulta para obtener la lista de Pokémon (para editarlos)
            $stmtPokemons = $db->prepare("SELECT * FROM Pokemon");
            $stmtPokemons->execute();
            $pokemons = $stmtPokemons->fetchAll(PDO::FETCH_ASSOC);

            echo $twig->render('admin/dashboard.html.twig', [
                'user'       => $_SESSION['user'],
                'typeCounts' => $typeCounts,
                'pokemons'   => $pokemons
            ]);
        } catch (PDOException $e) {
            error_log("Error en admin dashboard: " . $e->getMessage());
            http_response_code(500);
            echo $twig->render('error.html.twig');
        }
        break;

    case '/admin/pokemon/nuevo':
        // Ruta para agregar un nuevo Pokémon (ya implementada)
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: /");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $number = isset($_POST['number']) ? intval($_POST['number']) : 0;
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $imagePath = isset($_POST['imagePath']) ? trim($_POST['imagePath']) : '';

            if ($number > 0 && !empty($name) && !empty($imagePath)) {
                try {
                    $stmt = $db->prepare("INSERT INTO Pokemon (Number, Name, ImagePath) VALUES (:number, :name, :imagePath)");
                    $stmt->execute([
                        'number'    => $number,
                        'name'      => $name,
                        'imagePath' => $imagePath
                    ]);
                    header("Location: /admin/dashboard");
                    exit;
                } catch (PDOException $e) {
                    error_log("Error insertando pokemon: " . $e->getMessage());
                    http_response_code(500);
                    echo $twig->render('error.html.twig', ['mensaje' => 'Error al agregar el Pokémon']);
                }
            } else {
                echo $twig->render('admin/agregar_pokemon.html.twig', [
                    'error' => 'Todos los campos son obligatorios.'
                ]);
            }
        } else {
            echo $twig->render('admin/agregar_pokemon.html.twig');
        }
        break;

    case '/admin/pokemon/editar':
        // Ruta para editar un Pokémon (solo para admin)
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: /");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $number = isset($_POST['number']) ? intval($_POST['number']) : 0;
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $imagePath = isset($_POST['imagePath']) ? trim($_POST['imagePath']) : '';

            if ($number > 0 && !empty($name) && !empty($imagePath)) {
                try {
                    $stmt = $db->prepare("UPDATE Pokemon SET Name = :name, ImagePath = :imagePath WHERE Number = :number");
                    $stmt->execute([
                        'name'      => $name,
                        'imagePath' => $imagePath,
                        'number'    => $number
                    ]);
                    header("Location: /admin/dashboard");
                    exit;
                } catch (PDOException $e) {
                    error_log("Error actualizando pokemon: " . $e->getMessage());
                    http_response_code(500);
                    echo $twig->render('error.html.twig', ['mensaje' => 'Error al actualizar el Pokémon']);
                }
            } else {
                echo $twig->render('admin/editar_pokemon.html.twig', [
                    'error'   => 'Todos los campos son obligatorios.',
                    'pokemon' => $_POST
                ]);
            }
        } else {
            // Mostrar formulario con datos prellenados
            $number = isset($_GET['number']) ? intval($_GET['number']) : 0;
            if ($number > 0) {
                $stmt = $db->prepare("SELECT * FROM Pokemon WHERE Number = :number");
                $stmt->execute(['number' => $number]);
                $pokemon = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($pokemon) {
                    echo $twig->render('admin/editar_pokemon.html.twig', ['pokemon' => $pokemon]);
                } else {
                    echo $twig->render('error.html.twig', ['mensaje' => 'Pokémon no encontrado']);
                }
            } else {
                echo $twig->render('error.html.twig', ['mensaje' => 'Número de Pokémon inválido']);
            }
        }
        break;

    default:
        http_response_code(404);
        echo $twig->render('404.html.twig');
        break;
}
