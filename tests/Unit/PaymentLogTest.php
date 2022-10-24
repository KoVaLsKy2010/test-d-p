<?php

namespace Tests\Unit;

use App\Classes\Payment\ErrorLog;
use PHPUnit\Framework\TestCase;
use Tests\CreatesApplication;

class PaymentLogTest extends TestCase
{
    use CreatesApplication;

    private object $log;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->log = ErrorLog::getInstance();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
    }

    /**
     * 2 запрошенных экземпляра должны быть идентичны
     *
     * @return void
     */
    public function test_error_log_is_single(): void
    {
        $newLog = ErrorLog::getInstance();
        $this->assertEquals($newLog, $this->log);
    }

    /**
     * Проверяем формат и присутствие данных
     *
     * @return void
     */
    public function test_error_log_single_pushing(): void
    {
        $data = ['foo'=>'bar'];
        $this->log->pushLog($data);
        $checkArray = [
            0 => [
                'info' => $data
            ]
        ];
        $this->assertEquals(serialize($checkArray), serialize($this->log->getLog()));
    }

    /**
     * Проверяем формат и присутствие данных. Дополнительная проверка на отсутствие приватных данных
     *
     * @return void
     */
    public function test_error_log_private_info_pushing(): void
    {
        // В .env.testing прописать APP_DEBUG=false
        // Теперь, когда дебаг отключен, приватная часть должна быть скрыта
        $data = ['foo'=>'bar'];
        $privateData = ['bar' => 'biz'];
        $this->log->pushLog($data, $privateData);
        // Дважды, так как у нас лог сделан через синглтон. Данные должны накапливаться
        $checkArray = [
            0 => [
                'info' => $data
            ],
            1 => [
                'info' => $data
            ]
        ];
        $this->assertEquals(serialize($checkArray), serialize($this->log->getLog()));
    }

    /**
     * Логи успешно сохраняются
     *
     * @return void
     */
    public function test_errors_log_save_success(): void
    {
        $this->assertTrue($this->log->saveLog());
    }
}
