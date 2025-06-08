<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/session.php';
startSecureSession();

error_log("Estado inicial de la sesión: " . print_r($_SESSION, true));
error_log("Cookies disponibles: " . print_r($_COOKIE, true));

// Verificar si hay un token de refresco válido
if (!isset($_SESSION['username']) && isset($_COOKIE['refresh_token'])) {
    require_once __DIR__ . '/api/auth.php';
    $decoded = validateToken($_COOKIE['refresh_token']);
    if ($decoded && $decoded->type === 'refresh') {
        try {
            $stmt = $db->prepare("SELECT * FROM Users WHERE id = ?");
            $stmt->execute([$decoded->user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                // Generar nuevo token de acceso
                $accessToken = generateToken($user['id']);
                // Generar nuevo token de refresco
                $refreshToken = generateToken($user['id'], true);
                setcookie(
                    'refresh_token',
                    $refreshToken,
                    [
                        'expires' => time() + JWT_REFRESH_EXPIRATION,
                        'path' => '/',
                        'secure' => COOKIE_SECURE,
                        'httponly' => COOKIE_HTTPONLY,
                        'samesite' => COOKIE_SAMESITE
                    ]
                );
            }
        } catch (PDOException $e) {
            error_log("Error al restaurar sesión: " . $e->getMessage());
        }
    }
}

require_once __DIR__ . '/vendor/autoload.php'; // Asegúrate de tener instalado Twig vía Composer
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Load i18n configuration
require_once __DIR__ . '/config/i18n.php';

// Debug logging for translations
error_log("Current locale: " . $locale);
error_log("LANG environment variable: " . getenv('LANG'));
error_log("LC_ALL environment variable: " . getenv('LC_ALL'));
error_log("Text domain path: " . bindtextdomain('messages', __DIR__ . '/locale'));
error_log("Current text domain: " . textdomain('messages'));

// Test translation
error_log("Test translation (Login): " . gettext('Login'));

// Load database configuration (centralized PDO instantiation)
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/jwt_config.php';

// Configuración de Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views');
$twig = new \Twig\Environment($loader);

// Add trans filter to Twig
$twig->addFilter(new \Twig\TwigFilter('trans', function ($string) {
    return gettext($string);
}));

// Add global variables to Twig
$twig->addGlobal('app', [
    'language' => $locale,
    'session' => $_SESSION
]);

// Add isAuthenticated function to Twig
$twig->addFunction(new \Twig\TwigFunction('isAuthenticated', function () {
    $isAuth = isset($_SESSION['username']) && !empty($_SESSION['username']) && 
              isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    error_log("isAuthenticated check - Session: " . print_r($_SESSION, true) . ", Result: " . ($isAuth ? 'true' : 'false'));
    return $isAuth;
}));

// Add loginUser function (for login via /login and /api/auth/login)
function loginUser($username, $password) {
    global $db;
    error_log("Intentando login para usuario: " . $username);
    
    try {
        $stmt = $db->prepare("SELECT * FROM Users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            error_log("Login exitoso para usuario: " . $username);
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);
            
            error_log("Sesión establecida después del login: " . print_r($_SESSION, true));
            return $user;
        } else {
            error_log("Login fallido para usuario: " . $username);
        }
    } catch (PDOException $e) {
        error_log("Error en la base de datos: " . $e->getMessage());
    }
    return false;
}

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

// Obtener la ruta solicitada (para legacy routes)
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// New route for POST /api/auth/login (JSON login) – use preg_match to match /api/auth/login (with optional trailing slash or query)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && preg_match('/^\/api\/auth\/login(\/|\?|$)/', $_SERVER['REQUEST_URI'])) {
    // (Optional) log the incoming request URI (and raw REQUEST_URI) for debugging
    error_log("Incoming request URI (POST /api/auth/login): " . $_SERVER['REQUEST_URI'] . " (raw: " . var_export($_SERVER['REQUEST_URI'], true) . ")");
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        if (isset($data['username']) && isset($data['password'])) {
            $user = loginUser($data['username'], $data['password']);
            if ($user) {
                // (Optional) set a session or cookie if you want to keep the user logged in
                // session_start();
                // $_SESSION['user'] = $user;
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing username or password']);
        }
    } catch (Exception $e) {
        error_log("Error in /api/auth/login: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
    exit;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/api/auth/login') !== false) {
    // (Optional) log a debug message (using var_export) if the regex did not match (so we can see if the route is being hit)
    error_log("POST /api/auth/login route not matched. (REQUEST_URI: " . var_export($_SERVER['REQUEST_URI'], true) . ")");
    // (For debugging) force a 200 (OK) JSON response (with a dummy success message) so that we can see (in the browser's network tab) if the route is being hit.
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'POST /api/auth/login route not matched (debug fallback).']);
    exit;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/api/auth/login') {
    // (For debugging) force a 200 (OK) JSON response (with a dummy success message) so that we can see (in the browser's network tab) if the route is being hit.
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'POST /api/auth/login (exact match) (debug fallback).']);
    exit;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/api/auth/login') {
    // (For debugging) force a 200 (OK) JSON response (with a dummy success message) so that we can see (in the browser's network tab) if the route is being hit.
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'POST /api/auth/login (exact match (fallback)) (debug fallback).']);
    exit;
}

/* Agregar la definición de generateToken antes de la ruta /login */
function generateToken($user_id, $is_refresh = false) {
    $issued_at = time();
    $expiration = ($is_refresh) ? (JWT_REFRESH_EXPIRATION) : (JWT_EXPIRATION);
    $payload = [
         "sub" => $user_id,
         "iat" => $issued_at,
         "exp" => ($issued_at + $expiration)
    ];
    $header = json_encode([ "alg" => "HS256", "typ" => "JWT" ]);
    $payload_json = json_encode($payload);
    $base64_header = rtrim(strtr(base64_encode($header), "+/", "-_"), "=");
    $base64_payload = rtrim(strtr(base64_encode($payload_json), "+/", "-_"), "=");
    $signature = hash_hmac("sha256", $base64_header . "." . $base64_payload, JWT_SECRET, true);
    $base64_signature = rtrim(strtr(base64_encode($signature), "+/", "-_"), "=");
    return $base64_header . "." . $base64_payload . "." . $base64_signature;
}

// Agregar función isAuthenticated() para verificar si la sesión está activa
function isAuthenticated() {
    return isset($_SESSION['username']) && !empty($_SESSION['username']) && 
           isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Agregar ruta de logout
if ($request === '/logout') {
    error_log("Procesando logout - Sesión antes: " . print_r($_SESSION, true));
    clearSession();
    error_log("Sesión después del logout: " . print_r($_SESSION, true));
    header('Location: /');
    exit;
}

switch ($request) {
    case '/':
        // Debug: Verificar si la conexión a la base de datos está activa
        try {
            $db->getAttribute(PDO::ATTR_CONNECTION_STATUS);
            error_log("Conexión a la base de datos OK.");
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            http_response_code(500);
            echo $twig->render('error.html.twig', ['error' => 'Error de conexión a la base de datos.']);
            exit;
        }

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

            // Debug: Verificar si se obtuvieron datos
            error_log("Número de Pokémon obtenidos: " . count($pokemons));
            if (empty($pokemons)) {
                error_log("No se encontraron Pokémon en la base de datos.");
            }

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

            // Debug: Verificar si la plantilla existe
            if (!$twig->getLoader()->exists('home.html.twig')) {
                error_log("Error: La plantilla home.html.twig no existe.");
                http_response_code(500);
                echo "Error: La plantilla no existe.";
                exit;
            }

            error_log("Renderizando home.html.twig con sesión: " . print_r($_SESSION, true));
            echo $twig->render('home.html.twig', [
                'pokemons'   => $pokemons,
                'typeColors' => $typeColors,
                'session'    => $_SESSION  // Pasamos toda la sesión a la plantilla
            ]);
        } catch (PDOException $e) {
            error_log("Error en consulta: " . $e->getMessage());
            http_response_code(500);
            echo $twig->render('error.html.twig', ['error' => 'Error en la consulta a la base de datos.']);
        } catch (Exception $e) {
            error_log("Error general: " . $e->getMessage());
            http_response_code(500);
            echo $twig->render('error.html.twig', ['error' => 'Error general en la aplicación.']);
        }
        break;

    case '/login':
        // Ruta de login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("Recibida solicitud POST en /login");
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $user = loginUser($username, $password);
            if ($user) {
                error_log("Login exitoso, redirigiendo a dashboard");
                header('Location: /admin/dashboard');
                exit;
            } else {
                error_log("Login fallido, mostrando error");
                echo $twig->render('login.html.twig', ['error' => 'Invalid credentials']);
            }
        } else {
            echo $twig->render('login.html.twig');
        }
        break;

    case '/admin/dashboard':
        // Ruta del dashboard (requiere autenticación)
        if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            error_log("Acceso denegado a dashboard - Sesión: " . print_r($_SESSION, true));
            header("Location: /login");
            exit;
        }
        
        // Consulta para obtener estadísticas
        $stmt = $db->query("
            SELECT 
                SUBSTRING_INDEX(t.TypeName, '\n', 1) AS type,
                COUNT(DISTINCT pt_min.PokemonNumber) AS count,
                GROUP_CONCAT(DISTINCT p.Name) AS pokemons
            FROM (
                SELECT PokemonNumber, MIN(TypeID) AS TypeID 
                FROM PokemonTypes 
                GROUP BY PokemonNumber
            ) AS pt_min
            JOIN Types t ON pt_min.TypeID = t.TypeID
            LEFT JOIN Pokemon p ON pt_min.PokemonNumber = p.Number
            GROUP BY SUBSTRING_INDEX(t.TypeName, '\n', 1)
            ORDER BY count DESC
        ");
        $typeCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($typeCounts as &$row) {
            $row['pokemons'] = $row['pokemons'] ? explode(',', $row['pokemons']) : [];
        }
        unset($row);
        $pokemon_stats = $typeCounts;

        // Consulta para obtener la lista de Pokémon
        $stmt2 = $db->query("SELECT Number, Name, ImagePath FROM Pokemon ORDER BY Number ASC");
        $pokemons = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        error_log("Renderizando dashboard con sesión: " . print_r($_SESSION, true));
        echo $twig->render('admin/dashboard.html.twig', [
            'session' => $_SESSION,
            'pokemon_stats' => $pokemon_stats,
            'pokemons' => $pokemons
        ]);
        break;

    case '/admin/pokemon/add':
        // Ruta para agregar un nuevo Pokémon
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: /login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $number = isset($_POST['number']) ? intval($_POST['number']) : 0;
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $imagePath = isset($_POST['imagePath']) ? trim($_POST['imagePath']) : '';

            if ($number > 0 && !empty($name) && !empty($imagePath)) {
                try {
                    // Verificar si el número ya existe
                    $stmt = $db->prepare("SELECT COUNT(*) FROM Pokemon WHERE Number = :number");
                    $stmt->execute(['number' => $number]);
                    if ($stmt->fetchColumn() > 0) {
                        echo $twig->render('admin/add_pokemon.html.twig', [
                            'error' => 'A Pokemon with this number already exists.'
                        ]);
                        exit;
                    }

                    // Insertar el nuevo Pokémon
                    $stmt = $db->prepare("INSERT INTO Pokemon (Number, Name, ImagePath) VALUES (:number, :name, :imagePath)");
                    $stmt->execute([
                        'number' => $number,
                        'name' => $name,
                        'imagePath' => $imagePath
                    ]);

                    header("Location: /admin/dashboard");
                    exit;
                } catch (PDOException $e) {
                    error_log("Error inserting pokemon: " . $e->getMessage());
                    echo $twig->render('admin/add_pokemon.html.twig', [
                        'error' => 'Error adding Pokemon: ' . $e->getMessage()
                    ]);
                }
            } else {
                echo $twig->render('admin/add_pokemon.html.twig', [
                    'error' => 'All fields are required.'
                ]);
            }
        } else {
            echo $twig->render('admin/add_pokemon.html.twig');
        }
        break;

    case (preg_match('/^\/admin\/pokemon\/edit\/(\d+)$/', $request, $matches) ? true : false):
        // Ruta para editar un Pokémon
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: /login");
            exit;
        }
        
        $number = intval($matches[1]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $imagePath = $_POST['imagePath'] ?? '';
            
            if (!empty($name) && !empty($imagePath)) {
                try {
                    $stmt = $db->prepare("UPDATE Pokemon SET Name = :name, ImagePath = :imagePath WHERE Number = :number");
                    $stmt->execute([
                        'name' => $name,
                        'imagePath' => $imagePath,
                        'number' => $number
                    ]);
                    header("Location: /admin/dashboard");
                    exit;
                } catch (PDOException $e) {
                    error_log("Error actualizando pokemon: " . $e->getMessage());
                    echo $twig->render('error.html.twig', ['error' => 'Error al actualizar el Pokémon']);
                }
            } else {
                echo $twig->render('admin/edit_pokemon.html.twig', [
                    'error' => 'Todos los campos son obligatorios',
                    'pokemon' => $_POST
                ]);
            }
        } else {
            try {
                $stmt = $db->prepare("SELECT * FROM Pokemon WHERE Number = :number");
                $stmt->execute(['number' => $number]);
                $pokemon = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($pokemon) {
                    echo $twig->render('admin/edit_pokemon.html.twig', ['pokemon' => $pokemon]);
                } else {
                    echo $twig->render('error.html.twig', ['error' => 'Pokémon no encontrado']);
                }
            } catch (PDOException $e) {
                error_log("Error obteniendo pokemon: " . $e->getMessage());
                echo $twig->render('error.html.twig', ['error' => 'Error al obtener el Pokémon']);
            }
        }
        break;

    case (preg_match('/^\/admin\/pokemon\/delete\/(\d+)$/', $request, $matches) ? true : false):
        // Ruta para eliminar un Pokémon
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: /login");
            exit;
        }
        
        $number = intval($matches[1]);
        
        try {
            // Primero eliminar las referencias en PokemonTypes
            $stmt = $db->prepare("DELETE FROM PokemonTypes WHERE PokemonNumber = :number");
            $stmt->execute(['number' => $number]);
            
            // Luego eliminar el Pokémon
            $stmt = $db->prepare("DELETE FROM Pokemon WHERE Number = :number");
            $stmt->execute(['number' => $number]);
            
            header("Location: /admin/dashboard");
            exit;
        } catch (PDOException $e) {
            error_log("Error eliminando pokemon: " . $e->getMessage());
            echo $twig->render('error.html.twig', ['error' => 'Error al eliminar el Pokémon']);
        }
        break;

    default:
        header("HTTP/1.0 404 Not Found");
        echo $twig->render('404.html.twig');
        break;
}
