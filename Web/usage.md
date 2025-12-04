# Fájlstruktúra
-**/api**
    -`router.php`
   -`config.php`
   -`db.php`
   -`functions.php`
   -`auth.php`
   -`admins.php`
   -`keys.php`
   -`readers.php`
   -`readlog.php`
   -`access.php`

## admin jelszó generálás

```php
$hash = password_hash($password, PASSWORD_DEFAULT);
```

## login ellenőrzés

```php
if (!password_verify($inputPassword, $row['pass'])) {
    // Invalid login
}
```

## Érkező információ dekódolása

```php
$decrypted = decrypt_data($input);
list($readerId, $rfid) = explode(";", $decrypted);
```

## ARDUINO --> API

``` c++
plaintext = readerId + ";" + RFID
encrypted = AES-256-CBC encrypt(plaintext, shared_key)
POST /api/access/check
BODY = encrypted
```
