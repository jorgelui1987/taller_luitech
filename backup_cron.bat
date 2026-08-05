@echo off
REM ============================================================
REM  CRM Tienda Celulares - Backup Automatico (Windows)
REM  Ejecutar cada minuto con el Programador de Tareas de Windows
REM ============================================================

cd /d C:\laragon\www\servicio-tecnico-main\servicio-tecnico-main
php artisan schedule:run >> storage\logs\scheduler.log 2>&1