<?php
namespace App\database;

use App\helpers\EncryptPassword;

class User {
  public static function getUserByEmail($data, $db){
    $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $data['email']]);
    $user = $stmt->fetch();
    return $user;
  }

  public static function createUser($data, $db){
    $encryptedPassword = EncryptPassword::encrypt($data['password']);

    $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
    $stmt->execute([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => $encryptedPassword
    ]);
    return $db->lastInsertId();
  }
}