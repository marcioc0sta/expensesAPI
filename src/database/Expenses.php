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
}