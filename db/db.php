<?php
// db.php
function createPDO(array $config): PDO {
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};user={$config['user']};password={$config['pass']}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, $config['user'], $config['pass'], $options);
}