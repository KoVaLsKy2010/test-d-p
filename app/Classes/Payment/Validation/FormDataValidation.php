<?php

namespace App\Classes\Payment\Validation;

use App\Classes\Payment\ErrorLog;
use App\Classes\Payment\Traits\DataValidationTrait;

class FormDataValidation
{
    use DataValidationTrait;

    // Массив с передаваемыми полями от мерчанта
    const DATA_KEYS = ['project', 'invoice', 'status', 'amount', 'amount_paid', 'rand'];

    /**
     * Объект с логами
     * @var object
     */
    private object $log;

    /**
     * Передаваемые данные тела запроса
     * @var array
     */
    private array $data;

    /**
     * Создаем или получаем экземпляр лога.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->log = ErrorLog::getInstance();
    }
}
