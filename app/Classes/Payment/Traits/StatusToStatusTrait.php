<?php

namespace App\Classes\Payment\Traits;

trait StatusToStatusTrait
{
    /**
     * Массив для сопоставления статусов "у них" и у нас
     * @var array|\string[][]
     */
    private static array $statusArray = [
        6 =>[
            'new' => 'new',
            'pending' => 'pending',
            'completed' => 'completed',
            'expired' => 'expired',
            'rejected' => 'rejected',
        ],
        816 => [
            'created' => 'new',
            'inprogress' => 'pending',
            'paid' => 'completed',
            'expired' => 'expired',
            'rejected' => 'rejected',
        ]
    ];

    /**
     * Метод проверки успешного сопоставления статусов
     * @param string $merchantId
     * @param string $status
     * @return string
     */
    public static function getTrueStatus(string $merchantId, string $status): string
    {
        if(!array_key_exists($merchantId, self::$statusArray)
            || !array_key_exists($status, self::$statusArray[$merchantId])){
            return 'unknown status';
        }else{
            return self::$statusArray[$merchantId][$status];
        }
    }
}