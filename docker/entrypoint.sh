#!/bin/bash

echo " Iniciando aplicação..."

echo " Aguardando banco de dados..."
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    echo " Banco ainda não está pronto, aguardando..."
    sleep 2
done

echo "Gerando chave da aplicação..."
php artisan key:generate --force

echo " Rodando migrations..."
php artisan migrate --force

echo " Aplicação pronta!"
php artisan serve --host=0.0.0.0 --port=8000
