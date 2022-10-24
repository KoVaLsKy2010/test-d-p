# Test project

## Необходимое ПО

* Docker
* Composer v2
* ext-bcmath
* php 8.1
* php8.1-{dom,bcmath,gmp,xml,zip}


## Запуск 

    composer install
    ./vendor/bin/sail up

# Тесты

    cp .env .env.testing

В `.env.testing` установить `APP_DEBUG=false`

## Запуск Feature тестов

    php artisan test --testsuite=Feature

## Запуск Unit текстов

    php artisan test --testsuite=Unit

## Обзор общего покрытия тестами

    php artisan test --coverage