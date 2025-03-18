<?PHP

//Twig Setup
require_once 'vendor/autoload.php';
$loader = new \Twig\Loader\FilesystemLoader('views');
$twig = new \Twig\Environment($loader);

//Twig Render
echo $twig->render('admin.html', ['name' => 'Tomé']);
