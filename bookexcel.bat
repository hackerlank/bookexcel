@echo off
if exist runtime\php.exe (runtime\php bookexcel.php) else (php bookexcel.php)
pause