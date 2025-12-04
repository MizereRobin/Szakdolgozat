<?php
// config.php
return [
    // MariaDB (PDO) connection
    'db' => [
        'host' => '127.0.0.1',
        'dbname' => 'belépteto',   // állítsd be
        'user' => 'dbuser',
        'pass' => 'dbpass',
        'charset' => 'utf8mb4'
    ],

    // Titkos kulcs: EZZEL a kulccsal titkosítunk/dekódolunk minden esetben.
    // Több biztonságért használj erős, 32 byte-os véletlent (pl. openssl_random_pseudo_bytes(32)).
    'encryption_key' => 'EzVANazAzonosKulcs_Amely30+byte_justExample',

    // Token secret (HMAC alapú ellenőrzéshez)
    'token_secret' => 'SzuperRejtettTokenKulcs123!',

    // Token lejárat másodpercben (pl. 3600 = 1 óra)
    'token_ttl' => 3600
];
