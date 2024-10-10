<?php
namespace App\database;

class Expenses {
  public static function insertExpense($data, $db) {
    $stmt = $db->prepare('INSERT INTO expenses (from_user, description, category, value, date) VALUES (:from_user, :description, :category, :value, :date)');
    $stmt->execute([
        'from_user' => $data['userId'],
        'description' => $data['description'],
        'category' => $data['category'],
        'value' => $data['value'],
        'date' => $data['date']
    ]);
  }

  public static function getExpensesByUserId($userId, $db) {
    $stmt = $db->prepare('SELECT * FROM expenses WHERE from_user = :userId');
    $stmt->execute(['userId' => $userId]);
    $data = $stmt->fetchAll();

    return $data;
  }

  public static function getExpensesByMonthAndYear($args, $db) {
    $stmt = $db->prepare('SELECT * FROM expenses WHERE from_user = :userId AND YEAR(date) = :year AND MONTH(date) = :month');
    $stmt->execute(['userId' => $args['userId'], 'year' => $args['year'], 'month' => $args['month']]);
    $data = $stmt->fetchAll();

    return $data;
  }

  public static function getExpensesByMonth($args, $db) {
    $stmt = $db->prepare('SELECT * FROM expenses WHERE from_user = :userId AND MONTH(date) = :month');
    $stmt->execute(['userId' => $args['userId'], 'month' => $args['month']]);
    $data = $stmt->fetchAll();

    return $data;
  }

  public static function getExpenseById($id, $db) {
    $stmt = $db->prepare('SELECT * FROM expenses WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $data = $stmt->fetch();

    return $data;
  }

  public static function updateExpense($data, $db, $args) {
    $id = (int) $args['id'];

    $stmt = $db->prepare('UPDATE expenses SET description = :description, category = :category, value = :value, date = :date WHERE id = :id');
    $stmt->execute([
        'description' => $data['description'],
        'category' => $data['category'],
        'value' => $data['value'],
        'date' => $data['date'],
        'id' => $id
    ]);
  }
}