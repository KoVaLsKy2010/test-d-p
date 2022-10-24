<?php

namespace App\Classes\Payment\Interfaces;

interface UpdatePaymentInterface
{
    public function setData(array $array): void;

    public function checkSignature(): bool;

    public function checkLimit(): mixed;

    public function checkPaymentSum(): bool;

    public function updatePaymentStatus(): bool;

    public function setPaymentId(): void;

    public function validatePaymentId(): bool;
}