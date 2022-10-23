<?php

namespace App\Classes\Payment\Factories;

use App\Classes\Payment\{JsonDataClass, FormDataClass, ErrorLog};

class HandlerFactory
{
    /**
     * @var object|FormDataClass|JsonDataClass|null Объект, с которым нам предстоит работать
     */
    private object|null $handler;

    /**
     * @var object Глобальный лог ошибок для вывода пользователю и сохранению в системе
     */
    public object $log;

    /**
     * @var array Маркет-статус операции обновления
     */
    public array $status;

    /**
     * @param string|null $contentType
     */
    public function __construct(string|null $contentType)
    {
        // Шаблон ответа
        $this->status = ['status' => 'fail', 'time' => date('Y-m-d H:i:s'), 'data' => []];

        // Получаем|создаем экземпляр лога
        $this->log = ErrorLog::getInstance();

        switch ($this->getContentTypeStartString($contentType)){
            case 'application/json':
                $this->handler = new JsonDataClass();
                $this->log->pushLog(['handler' => 'JsonDataClass']);
                break;
            case 'multipart/form-data':
                $this->handler = new FormDataClass();
                $this->log->pushLog(['handler' => 'FormDataClass']);
                break;
            default:
                $this->handler = null;
                $this->log->pushLog(['handler' => null]);
                $this->log->pushLog([
                    'text' => 'Content-Type ' . $contentType . ' не поддерживается. Ожидаемый Content-Type: multipart/form-data или application/json'
                ]);
                $this->log->saveLog();
                $this->status['data'] = $this->log->getLog();
        }
    }

    /**
     * @param array $data Запускаем обработку транзакций.
     * @return array Статус и/или лог
     */
    public function run(array $data): array
    {
        // Если не верный формат запроса
        if (is_null($this->handler))
            return $this->status;

        // Закидываем в обработчик данные
        $this->handler->setData($data);

        // Проверяем, валидный формат payment_id или нет. Без него работать не сможем
        if ($this->handler->validatePaymentId()){
            $this->handler->setPaymentId();
        }else{
            $this->log->saveLog();
            $this->status['data'] = $this->log->getLog();
            return $this->status;
        }

        // Проверка на валидность массива полученных данных
        $isValidData = $this->handler->validateDataArray();

        // Проверка сигнатур, лимита на день, суммы платежа, валидности ключей данных
        if ($isValidData
            && $this->handler->checkSignature()
            && $this->handler->checkLimit()
            && $this->handler->checkPaymentSum()
        ){
            // Проверка, получится ли обновить данные в палатеже
            $isSuccess = $this->handler->updatePaymentStatus();

            // Успешно обновили, возвращаем статус success
            if ($isSuccess){
                $this->status['status'] = 'success';

            // При обновлении что-то пошло не так (например не соответствует payment_id и merchant_id)
            }else{
                $this->log->saveLog();
                $this->status['data'] = $this->log->getLog();
            }
        }else{
            $this->log->saveLog();
            $this->status['data'] = $this->log->getLog();
        }
        return $this->status;
    }

    /**
     * Вспомогательная функция. Иногда заголовки отправляются с дополнительными примесями. Прим: multipart/form-data; ; boundary=...
     * @param string|null $contentType
     * @return string
     */
    private function getContentTypeStartString(string|null $contentType): string
    {
        return explode(';', $contentType)[0];
    }
}
