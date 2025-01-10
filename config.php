<?php
return [
    'db' => [
        'host' => getenv('DB_HOST'),
        'username' => getenv('DB_USERNAME'),
        'password' => getenv('DB_PASSWORD'),
        'database' => getenv('DB_NAME')
    ],
    'app' => [
        'secret_key' => getenv('APP_SECRET'),
        'debug' => getenv('APP_DEBUG', false)
    ]
]; 