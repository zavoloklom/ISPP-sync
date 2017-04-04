chcp 65001
if not exist "..\logs\" mkdir ..\logs
C:\PHP\php.exe ..\app.php groups >> ..\logs\sync-groups-%Date%.log