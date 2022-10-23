<?php

namespace App\Classes\Payment;

use App\Classes\Payment\Interfaces\UpdatePaymentInterface;
use App\Classes\Payment\Validation\JsonDataValidation;
use App\Classes\Payment\Traits\{DataModificationTrait, StatusToStatusTrait, PaymentTrait};

class JsonDataClass implements UpdatePaymentInterface
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
    private int $paymentId;

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
    const SUM_LIMIT = 780000*100; //центов

    /**
     * Переносим из .env файла ключи и секреты от мерчантов
     */
    public function __construct()
    {
        $this->merchantId = config('app.merchantSecrets.json.id');
        $this->merchantKey = config('app.merchantSecrets.json.key');
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
        $this->paymentId = $this->data['payment_id'];
    }

    /**
     * Проверка на корректность и наличие id платежа
     * @return bool
     */
    public function validatePaymentId(): bool
    {
        if(array_key_exists('payment_id', $this->data)
            && !is_null($this->data['payment_id'])
        ){
            return true;
        }else{
            $this->log->pushLog([
                'text' => 'Не верный payment_id',
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
        if( (new JsonDataValidation($this->data))->validateData()){
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
        $originalSignature = $data['sign'];
        unset($data['sign']);
        $preSignature = DataModificationTrait::implodeData($data) . $this->merchantKey;
        $signature = hash('sha256', $preSignature);
        if($signature === $originalSignature){
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
