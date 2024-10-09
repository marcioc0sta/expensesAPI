<?php
namespace App\database;

class Categories {
  public static function getCategories($db) {
    $stmt = $db->query('SELECT * FROM categories');
    $data = $stmt->fetchAll();
    return $data;
  }

  public static function getCategoryById($id, $db) {
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $category = $stmt->fetch();

    return $category;
  }
}