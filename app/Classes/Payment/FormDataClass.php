<?php

namespace App\Classes\Payment;

use App\Classes\Payment\Interfaces\UpdatePaymentInterface;
use App\Classes\Payment\Validation\FormDataValidation;
use App\Classes\Payment\Traits\{DataModificationTrait, StatusToStatusTrait, PaymentTrait};

class FormDataClass implements UpdatePaymentInterface
{
    use DataModificationTrait, StatusToStatusTrait, PaymentTrait;

    /** Данные, получаемые из тела запроса
     * @var array
     */
    private array $data;

    /**
     * Массив с логами
     * @var object
     */
    private object $log;

    /**
     * Отдельное от данных свойство. Нужно для уменьшения связанности и оптимизации запросов к БД
     * @var int|string
     */
    private int|string $paymentId;

    /**
     * Отдельное от данных свойство. Нужно для уменьшения связанности и оптимизации запросов к БД
     * @var int|string
     */
    private int|string $merchantId;

    /**
     * Отдельное от данных свойство. Нужно для уменьшения связанности. Используется для построения сигнатур
     * @var int|string
     */
    private int|string $merchantKey;

    // Дневной лимит на выполненные операции. По-хорошему нужно вынести в БД или конфиги
    const SUM_LIMIT = 4500000*100; //центов

    /**
     * Переносим из .env файла ключи и секреты от мерчантов
     */
    public function __construct()
    {
        $this->merchantId = config('app.merchantSecrets.form.id');
        $this->merchantKey = config('app.merchantSecrets.form.key');
        $this->log = ErrorLog::getInstance();
    }

    /**
     * @param array $array
     * @return void
     */
    public function setData(array $array): void
    {
        $this->data = $array;
    }

    /**
     * @return void
     */
    public function setPaymentId(): void
    {
        $this->paymentId = $this->data['invoice'];
    }

    /**
     * Проверка на корректность и наличие id платежа
     * @return bool
     */
    public function validatePaymentId(): bool
    {
        if(array_key_exists('invoice', $this->data)
            && !is_null($this->data['invoice'])
        ){
            return true;
        }else{
            $this->log->pushLog([
                'text' => 'Не верный invoice',
                'data' => $this->data
            ]);
            return false;
        }
    }

    /**
     * Проверка тела запроса на валидность
     * @return bool
     */
    public function validateDataArray(): bool
    {
        if ((new FormDataValidation($this->data))->validateData()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Метод сравнения сигнатур
     * @return bool
     */
    public function checkSignature(): bool
    {
        $data = DataModificationTrait::arraySort($this->data);
        $originalSignature = request()->header('authorization');
        $preSignature = DataModificationTrait::implodeData($data, '.') . $this->merchantKey;
        $signature = hash('md5', $preSignature);
        if ($signature === $originalSignature){
            $status = true;
        }else{
            $status = false;
            $this->log->pushLog([
                'text' => 'Сигнатуры не совпадают',
                'data' => $this->data,
            ], ['signature' => $signature]);
        }
        return $status;
    }
}
