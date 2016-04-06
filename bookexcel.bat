@echo off
if exist runtime\php.exe (runtime\php bkExcel.php) else (php bkExcel.php)
pause