chcp 65001
if not exist "..\logs\" mkdir ..\logs
C:\PHP\php.exe ..\app.php events >> ..\logs\sync-events-%Date%.log