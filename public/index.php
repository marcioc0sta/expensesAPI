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

// Register Handlers
$container->set('UserHandler', function () {
    return new App\handlers\UserHandler();
});
$container->set('CategoriesHandler', function () {
    return new App\handlers\CategoriesHandler();
});
$container->set('LoginHandler', function () {
    return new App\handlers\LoginHandler();
});
$container->set('ExpensesHandler', function () {
    return new App\handlers\ExpensesHandler();
});

AppFactory::setContainer($container);
$app = AppFactory::create();

// Routes
$app->get('/categories', function (RequestInterface $request, ResponseInterface $response) {
    $db = $this->get('db');
    $categoryHandler = $this->get('CategoriesHandler');
    return $categoryHandler->getCategories($request, $response, $db);
});
$app->post('/users', function (RequestInterface $request, ResponseInterface $response) {
    $db = $this->get('db');
    $userHandler = $this->get('UserHandler');
    return $userHandler->createUser($request, $response, $db);
});
$app->post('/login', function (RequestInterface $request, ResponseInterface $response) {
    $data = json_decode($request->getBody()->getContents(), true);
    $db = $this->get('db');
    $loginHandler = $this->get('LoginHandler');
    return $loginHandler->login($request, $response, $db, $data);
});
$app->post('/expenses', function(RequestInterface $request, ResponseInterface $response){
    $data = json_decode($request->getBody()->getContents(), true);
    $db = $this->get('db');
    $expensesHandler = $this->get('ExpensesHandler');
    return $expensesHandler->createExpense($request, $response, $data, $db);
});
$app->get('/expenses/{userId}', function(RequestInterface $request, ResponseInterface $response, array $args) {
    $db = $this->get('db');
    $expensesHandler = $this->get('ExpensesHandler');
    return $expensesHandler->getExpensesByUserId($request, $response, $db, $args);
});
$app->get('/expenses/{userId}/{year}/{month}', function(RequestInterface $request, ResponseInterface $response, array $args) {
    $db = $this->get('db');
    $expensesHandler = $this->get('ExpensesHandler');
    return $expensesHandler->getExpensesByMonthAndYear($request, $response, $db, $args);
});
$app->get('/expenses/{userId}/{month}', function(RequestInterface $request, ResponseInterface $response, array $args) {
    $db = $this->get('db');
    $expensesHandler = $this->get('ExpensesHandler');
    return $expensesHandler->getExpensesByMonth($request, $response, $db, $args);
});
$app->put('/expenses/{id}', function(RequestInterface $request, ResponseInterface $response, array $args) {
    $data = json_decode($request->getBody()->getContents(), true);
    $db = $this->get('db');
    $args['id'] = (int) $args['id'];

    $expensesHandler = $this->get('ExpensesHandler');
    return $expensesHandler->editExpense($request, $response, $data, $db, $args);
});

$app->run();
