# Szakdolgozat
NFC, RFID Lock with admin panel


## Endpoints
- try4cc3ss - Reader sends the data here _(try4cc3ss?in=ReaderID%20%RFID)_ and with function GetAccess($rederID, $rfid) decides if echo 0 or 1
## Functions
- GetAccess(int $readerID, int $rfid) \[**db.php**\] Decides if the user can get access or not.
  Logic: If Reader is Active if no, returns 0. If it's active then checks if it's in Absolute mode or not.
  _(Absolute mode)_ In Absolute mode checks if the role of user and role of reader is equal. If yes, returns 1 else reuturns 0.
  _(Normal mode)_ In Normal mode checks if the role of the user is higher or equal of the reader role. True = 1 False = 0.
