@echo off
REM ============================================================
REM  CRM Tienda Celulares - Backup Automatico (Windows)
REM  Programado a diario 2:00 AM via Programador de Tareas
REM ============================================================

cd /d C:\laragon\www\taller_luitech-main
php artisan backup:automatico --retencion=7 >> storage\logs\backup-cron.log 2>&1