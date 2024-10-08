<?php
namespace App\helpers;

class CategoriesEnum {
    const MORADIA = 1;
    const ALIMENTACAO = 2;
    const TRANSPORTE = 3;
    const SAUDE = 4;
    const EDUCACAO = 5;
    const LAZER = 6;

    public static function getCategories() {
        return [
            self::MORADIA => 'moradia',
            self::ALIMENTACAO => 'alimentacao',
            self::TRANSPORTE => 'transporte',
            self::SAUDE => 'saude',
            self::EDUCACAO => 'educacao',
            self::LAZER => 'lazer',
        ];
    }
}