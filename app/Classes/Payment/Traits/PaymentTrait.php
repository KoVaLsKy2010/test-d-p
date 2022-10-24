<?php

namespace App\Classes\Payment\Traits;

use App\Models\Payment;

trait PaymentTrait
{
    /**
     * Метод проверки лимита на день по платежам по конкретному мерчанту
     * @return bool
     */
    public function checkLimit(): bool
    {
        $sum = Payment::whereDate('created_at', date('Y-m-d'))
            ->where('merchant_id', $this->merchantId)->sum('amount');
        //TODO: Нужно сопоставить нагрузку и риски. Можно сделать счетчик в memcache, например.
        if (($sum + $this->data['amount_paid']) > self::SUM_LIMIT){
            $this->log->pushLog([
                'text' => 'Превышен лимит транзакций за ' . date('Y-m-d'),
                'data' => $this->data,
                'sum' => $sum,
                'limit' => self::SUM_LIMIT
            ]);
            return false;
        }else{
            return true;
        }
    }

    /**
     * Метод проверки соответствия данных в БД и прилетевших данных из мерчанта
     * @return bool
     */
    public function checkPaymentSum(): bool
    {
        $paymentSum = Payment::where('merchant_id', $this->merchantId)
            ->where('payment_id', $this->paymentId)->value('amount');
        if ($paymentSum != $this->data['amount_paid'] && !is_null($paymentSum)){
            $this->log->pushLog([
                'text' => 'Сумма в заказе и сумма в платеже отличаются ',
                'data' => $this->data,
                'BdPaymentSum' => $paymentSum
            ]);
            return false;
        }else{
            return true;
        }
    }

    /**
     * Метод обновления данных о платеже.
     * @return bool
     */
    public function updatePaymentStatus(): bool
    {
        $payment = Payment::where('merchant_id', $this->merchantId)
            ->where('payment_id', $this->paymentId)->first();

        if (!$this->hasPayment($payment))
            return false;

        if (!$this->checkStatus($payment))
            return false;

        $payment->save();
            return true;
    }

    /**
     * Проверка, существует ли у нас в системе платеж
     * @param object|null $payment
     * @return bool
     */
    private function hasPayment(object|null $payment): bool
    {
        if(is_null($payment)){
            $this->log->pushLog([
                'text' => 'Платеж не найден',
                'data' => $this->data
            ]);
            return false;
        }else{
            return true;
        }
    }

    /**
     * Метод, проверяющий приходящие статусы платежа. Удастся ли их сопоставить со статусами в нашей системе
     * @param object $payment
     * @return bool
     */
    private function checkStatus(object $payment): bool
    {
        //TODO: повесить проверку на переход на взаимоисключающие статусы. Например из rejected в paid ли из paid
        $payment->status = StatusToStatusTrait::getTrueStatus($this->merchantId, $this->data['status']);
        if ($payment->status != 'unknown status'){
            return true;
        }else{
            $this->log->pushLog([
                'text' => 'Неизвестный НОВЫЙ статус платежа',
                'data' => $this->data,
                'merchantId' => $this->merchantId
            ]);
            return false;
        }
    }
}