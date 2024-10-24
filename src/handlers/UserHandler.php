<?php

namespace App\handlers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\database\User;

class UserHandler {
  public function createUser(RequestInterface $request, ResponseInterface $response, $db) {
    $data = json_decode($request->getBody()->getContents(), true);
    
    // Verify if user already exists
    $user = User::getUserByEmail($data, $db);
    if ($user) {
        $response->getBody()->write(json_encode(['error' => 'User already exists']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    
    // Insert new user
    $newUserId = User::createUser($data, $db);

    $response->getBody()->write(json_encode(['message' => 'user id: ' . $newUserId . ' successfully created']));
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function updateIncome(RequestInterface $request, ResponseInterface $response, $db) {
    $data = json_decode($request->getBody()->getContents(), true);

    // Verify if user already exists
    $user = User::getUserById($data['userId'], $db);
    if (!$user) {
        $response->getBody()->write(json_encode(['error' => 'Invalid user']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
    
    // Update user income
    User::updateUserIncome($data, $db);

    $response->getBody()->write(json_encode(['message' => 'user income successfully updated']));
    return $response->withHeader('Content-Type', 'application/json');
  }

  public function updateUser(RequestInterface $request, ResponseInterface $response, $db) {
    $data = json_decode($request->getBody()->getContents(), true);

    // Verify if user already exists
    $user = User::getUserById($data['userId'], $db);
    if (!$user) {
        $response->getBody()->write(json_encode(['error' => 'Invalid user']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Prepare the fields to update
    $fieldsToUpdate = [];
    if (isset($data['income']) && $data['income'] !== $user['user_income']) {
        $fieldsToUpdate['user_income'] = $data['income'];
    }
    if (isset($data['name']) && $data['name'] !== $user['name']) {
        $fieldsToUpdate['name'] = $data['name'];
    }
    if (isset($data['email']) && $data['email'] !== $user['email']) {
        $fieldsToUpdate['email'] = $data['email'];
    }
    if (isset($data['last_name']) && $data['last_name'] !== $user['last_name']) {
        $fieldsToUpdate['last_name'] = $data['last_name'];
    }

    // Update user if there are fields to update
    if (!empty($fieldsToUpdate)) {
        User::updateUser($data['userId'], $fieldsToUpdate, $db);
    }

     // Fetch the updated user data
     $updatedUser = User::getUserById($data['userId'], $db);

     // Respond with the updated user data
     $response->getBody()->write(json_encode([
      'id' => $updatedUser['id'], 
      'name' => $updatedUser['name'], 
      'email' => $updatedUser['email'], 
      'user_income' => $updatedUser['user_income'], 
      'last_name' => $updatedUser['last_name']
    ]));

    return $response->withHeader('Content-Type', 'application/json');
}
}