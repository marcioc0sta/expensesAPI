<?php

namespace App\handlers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\database\User;

class LoginHandler {
  public function login (RequestInterface $request, ResponseInterface $response, $db, $data){
    // Verify if user exists
    $user = User::getUserByEmail($data, $db);;
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
  }
}