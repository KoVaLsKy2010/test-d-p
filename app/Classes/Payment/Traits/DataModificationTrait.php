<?php

namespace App\Classes\Payment\Traits;

trait DataModificationTrait
{
    /**
     * Сортируем массив по ключам. Вынесен в отдельный метод для удобства тестов и возможностей расширения
     * @param array $data
     * @return array
     */
    public static function arraySort(array $data): array
    {
        ksort($data);
        return $data;
    }

    /**
     * Метод используется для формирования сигнатуры запроса. У разных мерчантов отличаются разделители
     * По этой причине вынесен в отдельный метод, где вторым аргументов сепаратор.
     * @param array $data
     * @param string $separator
     * @return string
     */
    public static function implodeData(array $data, string $separator = ':'): string
    {
        return implode($separator, $data);
    }
}