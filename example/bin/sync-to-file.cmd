chcp 65001
if not exist "..\logs\" mkdir ..\logs
C:\PHP\php.exe ..\sync.php help >> ..\logs\sync-help-%Date%.log