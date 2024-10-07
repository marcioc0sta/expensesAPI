<?php 
require dirname(__DIR__) . '/vendor/autoload.php';

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;
use DI\Container;

$config = require dirname(__DIR__) . '/db/config.php';
require dirname(__DIR__) . '/db/db.php';

$container = new Container();

// Set PDO instance in the container
$container->set('db', function() use ($config) {
    return createPDO($config['db']);
});

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->get('/', function (RequestInterface $request, ResponseInterface $response, array $args) {
    $response->getBody()->write('Hello from Slim 4 request handler');
    return $response;
});


$app->get('/categories', function (RequestInterface $request, ResponseInterface $response, array $args) {
    $db = $this->get('db');
    $stmt = $db->query('SELECT * FROM categories');
    $data = $stmt->fetchAll();
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});


$app->run();

