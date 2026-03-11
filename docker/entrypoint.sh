#!/bin/bash

echo "🚀 Iniciando aplicação..."

echo "⏳ Aguardando banco de dados..."
until mysqladmin ping -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent; do
    sleep 2
done

echo " Gerando chave da aplicação..."
php artisan key:generate --force

echo " Rodando migrations..."
php artisan migrate --force

echo " Aplicação pronta!"
php artisan serve --host=0.0.0.0 --port=8000
