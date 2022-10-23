<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Payment;
use App\Classes\Payment\JsonDataClass;

class JsonDataTest extends TestCase
{
    private string $url;
    private string|int $correctPaymentId;
    private string|int $correctPaymentAmount;
    private int $correctMerchantId;
    private array $correctData;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        /*
         * Для тестов корректной работы
         */
        $this->url = '/payment';
        $this->correctMerchantId = 6;
        $this->correctPaymentId = 555555;
        $this->correctPaymentAmount = 150000;
        $this->correctData = [
            'merchant_id' => $this->correctMerchantId,
            'payment_id' => $this->correctPaymentId,
            'status' => 'completed',
            'amount' => $this->correctPaymentAmount,
            'amount_paid' => $this->correctPaymentAmount,
            'timestamp' => '1654103837',
            'sign' => 'ce564b6be7705ed978f799a70ae99cef628e8c8ddadb9687de7597d006d016d8'
        ];
    }

    // Создаем запись с корректными данными. В конце ее почистим
    protected function setUp(): void
    {
        parent::setUp();

        Payment::firstOrCreate([
            'merchant_id' => $this->correctMerchantId,
            'payment_id' => $this->correctPaymentId
        ], [
            'status' => 'pending',
            'amount' => $this->correctPaymentAmount,
            'user_id' => 1
        ]);
    }

    // Чистим за собой свой правильный вариант
    protected function tearDown(): void
    {
        Payment::where([
            'merchant_id' => $this->correctMerchantId,
            'payment_id' => $this->correctPaymentId
        ])->delete();

        parent::tearDown();
    }

    /**
     * Callback_url отдает 200ок, независимо от данных
     * @return void
     */
    public function test_get(): void
    {
        $response = $this->get($this->url);
        $response->assertStatus(200);
    }

    /**
     * Проверка на неверную подпись
     * @return void
     */
    public function test_json_broken_signature_status_fail(): void
    {
        $data = $this->correctData;
        $data['sign'] = '9c816945aae23aa898174cefd24a9c816945aae23aa898174cefd24a';
        $response = $this->postJson($this->url, $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    /**
     * Проверка на неверный id заказа, при этом верную подпись
     * @return void
     */
    public function test_json_incorrect_payment_id_status_fail(): void
    {
        $data = $this->correctData;
        $data['payment_id'] = '7';

        // Меняем, иначе всегда будет срабатывать проверка на не верную сигнатуру
        $data['sin'] = 'b5627ce9c816945aae23aa898174cefd24a52a0713db3aab99c165a6f7ed5717';
        $response = $this->postJson($this->url, $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    /**
     * Проверка на неверную стоимость заказа, при этом верную подпись
     * @return void
     */
    public function test_json_incorrect_amount_status_fail(): void
    {
        $data = $this->correctData;
        $data['amount'] = '120';
        $data['amount_paid'] = '120';

        // Меняем, иначе всегда будет срабатывать проверка на не верную сигнатуру
        $data['sin'] = 'af99c94d18353ec6f2f79e6ed07075284d95273c68f5947f9898a2de19853b17';

        $response = $this->postJson($this->url, $data);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    /**
     * Проверка на выполнение условия проверки лимита на день
     * @return void
     */
    public function test_json_more_than_limit_data_status_fail(): void
    {
        $randomPayment = Payment::where('merchant_id', $this->correctMerchantId)->first();
        $oldAmount = $randomPayment->amount;

        $randomPayment->amount = JsonDataClass::SUM_LIMIT + 1;
        $randomPayment->save();

        $data = $this->correctData;
        $data['amount'] = '120';
        $data['amount_paid'] = '120';
        $response = $this->postJson($this->url, $data);

        $randomPayment->amount = $oldAmount;
        $randomPayment->save();

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    /**
     * Проверка на валидацию данных и тела запроса
     * @return void
     */
    public function test_json_broken_data_response_status_exists(): void
    {
        $response = $this->postJson($this->url, ['name' => '123']);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    /**
     * Проверка на "все ок"
     * @return void
     */
    public function test_json_correct_data_status_success(): void
    {
        $response = $this->postJson($this->url, $this->correctData);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }
}
