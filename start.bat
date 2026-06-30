@echo off
setlocal

echo ========================================
echo  SimpleServerStat  [GLOBUS.studio]
echo ========================================
echo.
echo Starting built-in PHP server...
start http://127.0.0.1:8080/
php -S 127.0.0.1:8080 -t app

endlocal
pause
