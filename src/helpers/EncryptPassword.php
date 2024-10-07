<?php
namespace App\helpers;
/**
 * In this case, we want to increase the default cost for BCRYPT to 12.
 * Note that we also switched to BCRYPT, which will always be 60 characters.
 */
class EncryptPassword {
  public static function encrypt($password){
    $salt = crypt('something', '$5$cabritaehOn0me-do-m3euCachorro$');
    $options = [
        'cost' => 12,
    ];

    return password_hash($password, PASSWORD_BCRYPT, $options);
  } 
}