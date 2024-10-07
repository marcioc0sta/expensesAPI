<?php 
require dirname(__DIR__) . '/vendor/autoload.php';

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;
use DI\Container;
use App\helpers\EncryptPassword;

$config = require dirname(__DIR__) . '/db/config.php';
require dirname(__DIR__) . '/db/db.php';

$container = new Container();

// Set PDO instance in the container
$container->set('db', function() use ($config) {
    return createPDO($config['db']);
});

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->get('/categories', function (RequestInterface $request, ResponseInterface $response, array $args) {
    $db = $this->get('db');
    $stmt = $db->query('SELECT * FROM categories');
    $data = $stmt->fetchAll();
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/users', function (RequestInterface $request, ResponseInterface $response, array $args) {
    $data = json_decode($request->getBody()->getContents(), true);
    $encryptedPassword = EncryptPassword::encrypt($data['password']);
    $db = $this->get('db');
    
    // Verify if user already exists
    $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $data['email']]);
    $user = $stmt->fetch();
    if ($user) {
        $response->getBody()->write(json_encode(['error' => 'User already exists']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    
    // Insert new user
    $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
    $stmt->execute([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => $encryptedPassword
    ]);
    $response->getBody()->write(json_encode(['id' => $db->lastInsertId()]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/login', function (RequestInterface $request, ResponseInterface $response, array $args) {
    $data = json_decode($request->getBody()->getContents(), true);
    $db = $this->get('db');
    
    // Verify if user exists
    $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $data['email']]);
    $user = $stmt->fetch();
    if (!$user) {
        $response->getBody()->write(json_encode(['error' => 'User not found']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
    
    // Verify password
    if (!password_verify($data['password'], $user['password'])) {
        $response->getBody()->write(json_encode(['error' => 'Invalid password']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    
    //Return user info
    $response->getBody()->write(json_encode(['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();