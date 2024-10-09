<?php

namespace App\handlers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\database\Categories;

class CategoriesHandler {
  public function getCategories(RequestInterface $request, ResponseInterface $response, $db) {
    $categories = Categories::getCategories($db);

    $response->getBody()->write(json_encode($categories));
    return $response->withHeader('Content-Type', 'application/json');
  }
}