<?php 
require dirname(__DIR__) . '/vendor/autoload.php';

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;
use DI\Container;
use App\helpers\CategoriesEnum;

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

// Get categories from CategoryEnum
$categories = CategoriesEnum::getCategories();

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
$app->get('/expenses/{userId}/{year}/{month}', function(RequestInterface $request, ResponseInterface $response, array $args) use ($categories) {
    $db = $this->get('db');
    $expensesHandler = $this->get('ExpensesHandler');
    return $expensesHandler->getExpensesByMonthAndYear($request, $response, $db, $args);
});

// Get expenses by month
$app->get('/expenses/{userId}/{month}', function(RequestInterface $request, ResponseInterface $response, array $args) use ($categories) {
    $db = $this->get('db');
    $stmt = $db->prepare('SELECT * FROM expenses WHERE from_user = :userId AND MONTH(date) = :month');
    $stmt->execute(['userId' => $args['userId'], 'month' => $args['month']]);
    $data = $stmt->fetchAll();

    // Separate expenses by category and calculate totals
    $expenses = [];
    foreach ($data as $expense) {
        $categoryName = $categories[$expense['category']] ?? 'Unknown';
        if (!isset($expenses[$categoryName])) {
            $expenses[$categoryName] = [
                'total' => 0,
                'items' => []
            ];
        }
        $expenses[$categoryName]['items'][] = $expense;
        $expenses[$categoryName]['total'] += $expense['value'];
    }

    // Remove empty keys and format totals
    $expenses = array_filter($expenses);
    foreach ($expenses as $category => &$categoryData) {
        $categoryData['total'] = number_format(ceil($categoryData['total'] * 100) / 100, 2, '.', '');
    }

    $response->getBody()->write(json_encode($expenses));
    return $response->withHeader('Content-Type', 'application/json');
});


$app->run();
