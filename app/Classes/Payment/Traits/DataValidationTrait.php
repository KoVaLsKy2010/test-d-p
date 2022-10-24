<?php

namespace App\Classes\Payment\Traits;

trait DataValidationTrait
{
    /**
     * Общий метод валидации данных тела запроса
     * @return bool
     */
    public function validateData(): bool
    {
        $isArray = $this->checkIsArray();
        $hasKeys = $this->checkKeys();
        if($isArray && $hasKeys){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Проверка, переданы и интерпретированы данные как массив или нет
     * @return bool
     */
    private function checkIsArray(): bool
    {
        if(count($this->data) > 0){
            return true;
        }else{
            $this->log->pushLog([
                'text' => 'Переданы пустые данные или в неверном формате',
                'data' => $this->data
            ]);
            return false;
        }
    }

    /**
     * Сверяем ключи с ожидаемыми ключами
     * @return bool
     */
    private function checkKeys(): bool
    {
        $diff = array_diff_key(array_flip(self::DATA_KEYS), $this->data);
        if (count($diff) == 0){
            return true;
        }else{
            $this->log->pushLog([
                'text' => 'Переданы не все данные',
                'data' => $this->data,
                'diff' => $diff
            ]);
            return false;
        }
    }
}
