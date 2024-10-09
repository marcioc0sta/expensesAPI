<?php 
require dirname(__DIR__) . '/vendor/autoload.php';

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;
use DI\Container;
use App\helpers\EncryptPassword;
use App\helpers\CategoriesEnum;

$config = require dirname(__DIR__) . '/db/config.php';
require dirname(__DIR__) . '/db/db.php';

$container = new Container();

// Set PDO instance in the container
$container->set('db', function() use ($config) {
    return createPDO($config['db']);
});

AppFactory::setContainer($container);
$app = AppFactory::create();

// Get categories from CategoryEnum
$categories = CategoriesEnum::getCategories();

// Get all categories
$app->get('/categories', function (RequestInterface $request, ResponseInterface $response, array $args) {
    $db = $this->get('db');
    $stmt = $db->query('SELECT * FROM categories');
    $data = $stmt->fetchAll();
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// Create user
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
    $response->getBody()->write(json_encode(['message' => 'user id: ' . $db->lastInsertId() . ' successfully created']));
    return $response->withHeader('Content-Type', 'application/json');
});

// Login
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

// Insert expense
$app->post('/expenses', function(RequestInterface $request, ResponseInterface $response, array $args){
    $data = json_decode($request->getBody()->getContents(), true);
    $db = $this->get('db');

    // expense
    $stmt = $db->prepare('INSERT INTO expenses (from_user, description, category, value, date) VALUES (:from_user, :description, :category, :value, :date)');
    $stmt->execute([
        'from_user' => $data['userId'],
        'description' => $data['description'],
        'category' => $data['category'],
        'value' => $data['value'],
        'date' => $data['date']
    ]);

    // Verify if the expense has a valid category
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = :id');
    $stmt->execute(['id' => $data['category']]);
    $category = $stmt->fetch();
    if (!$category) {
        $response->getBody()->write(json_encode(['error' => 'Invalid expense category']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Verifu if the expense has a valid user
    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(['id' => $data['userId']]);
    $user = $stmt->fetch();
    if (!$user) {
        $response->getBody()->write(json_encode(['error' => 'Invalid user']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $response->getBody()->write(json_encode(['message' => 'expense successfully created']));
    return $response->withHeader('Content-Type', 'application/json');
});

// Get expenses by userId
$app->get('/expenses/{userId}', function(RequestInterface $request, ResponseInterface $response, array $args) use ($categories) {
    $db = $this->get('db');
    $stmt = $db->prepare('SELECT * FROM expenses WHERE from_user = :userId');
    $stmt->execute(['userId' => $args['userId']]);
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

// Get expenses by year and month
$app->get('/expenses/{userId}/{year}/{month}', function(RequestInterface $request, ResponseInterface $response, array $args) use ($categories) {
    $db = $this->get('db');
    $stmt = $db->prepare('SELECT * FROM expenses WHERE from_user = :userId AND YEAR(date) = :year AND MONTH(date) = :month');
    $stmt->execute(['userId' => $args['userId'], 'year' => $args['year'], 'month' => $args['month']]);
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
