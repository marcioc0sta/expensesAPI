<?php
namespace App\handlers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\database\User;
use App\database\Categories;
use App\database\Expenses;
use App\helpers\CategoriesEnum;
use App\helpers\ExpensesTotals;

class ExpensesHandler {
  public static function createExpense(RequestInterface $request, ResponseInterface $response, $data, $db) {
    // Verify if the expense has a valid category
    $category = Categories::getCategoryById($data['category'], $db);
    if (!$category) {
        $response->getBody()->write(json_encode(['error' => 'Invalid expense category']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Verifu if the expense has a valid user
    $user = User::getUserById($data['userId'], $db);
    if (!$user) {
        $response->getBody()->write(json_encode(['error' => 'Invalid user']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // insert expense
    Expenses::insertExpense($data, $db);

    $response->getBody()->write(json_encode(['message' => 'expense successfully created']));
    return $response->withHeader('Content-Type', 'application/json');
  }

  public static function getExpensesByUserId(RequestInterface $request, ResponseInterface $response, $db, $args) {
    $data = Expenses::getExpensesByUserId($args['userId'], $db);

    // Separate expenses by category and calculate totals
    $expensesWithTotals = ExpensesTotals::getTotals($data, CategoriesEnum::getCategories());

    $response->getBody()->write(json_encode($expensesWithTotals));
    return $response->withHeader('Content-Type', 'application/json');
  }

  public static function getExpensesByMonthAndYear(RequestInterface $request, ResponseInterface $response, $db, $args) {
    $data = Expenses::getExpensesByMonthAndYear($args, $db);

    // Separate expenses by category and calculate totals
    $expensesWithTotals = ExpensesTotals::getTotals($data, CategoriesEnum::getCategories());
    $response->getBody()->write(json_encode($expensesWithTotals));
    return $response->withHeader('Content-Type', 'application/json');
  }

  public static function getExpensesByMonth(RequestInterface $request, ResponseInterface $response, $db, $args) {
    $data = Expenses::getExpensesByMonth($args, $db);

    // Separate expenses by category and calculate totals
    $expensesWithTotals = ExpensesTotals::getTotals($data, CategoriesEnum::getCategories());
    $response->getBody()->write(json_encode($expensesWithTotals));
    return $response->withHeader('Content-Type', 'application/json');
  }
}