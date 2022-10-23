<?php

namespace App\Classes\Payment;

use App\Classes\Payment\Traits\ErrorLogTrait;
use App\Models\PaymentLog;

class ErrorLog
{
    use ErrorLogTrait;

    /**
     * Массив, куда записываем данные логов
     * @var array
     */
    private array $log;

    /**
     * Метод добавления в массив логов новых данных
     * Вторым аргументом могут быть переданы приватные данные.
     * Клиенту приватные данные мы не покажем, но в БД у себя сохраним
     * @param array|string $data
     * @param $isPrivate
     * @return void
     */
    public function pushLog(array|string $data, $private = null): void
    {
        $info = ['info' => $data];
        if (!is_null($private))
            $info['private'] = $private;

        $this->log[] = $info;
    }

    /**
     * Метод получения логов для показа клиенту
     * @return array
     */
    public function getLog(): array
    {
        $log = $this->log;
        if (!config('app.debug')){
            foreach ($log as &$logData){
                unset($logData['private']);
            }
        }
        return $log;
    }

    /**
     * Метод сохранения в БД полных логов, в т.ч приватных
     * @return bool
     */
    public function saveLog(): bool
    {
        $dbLog = new PaymentLog();
        $dbLog->data = $this->log;
        $dbLog->save();
        return true;
    }
}