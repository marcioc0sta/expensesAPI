<?php
namespace App\database;

class Categories {
  public static function getCategories($db) {
    $stmt = $db->query('SELECT * FROM categories');
    $data = $stmt->fetchAll();
    return $data;
  }
}