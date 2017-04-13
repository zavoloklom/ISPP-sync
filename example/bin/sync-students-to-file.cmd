chcp 65001
if not exist "..\logs\" mkdir ..\logs
C:\PHP\php.exe ..\app.php students >> ..\logs\sync-students-%Date%.log