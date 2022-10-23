<?php

namespace App\Classes\Payment\Traits;

trait ErrorLogTrait
{
    /**
     * Состояние объекта с логами
     * @var object|null
     */
    private static object|null $instance = null;

    /**
     * Хитрый финт ушами, запрещающий повторное создание экземпляра класса
     */
    private function __construct(){}
    private function __clone(){}

    /**
     * Возвращаем объект
     * @return object
     */
    public static function getInstance(): object
    {
        return static::$instance ?? (static::$instance = new static());
    }
}