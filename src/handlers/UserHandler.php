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
}