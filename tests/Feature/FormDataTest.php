<?php

namespace Tests\Feature;

use App\Models\Payment;
use Tests\TestCase;
use App\Classes\Payment\FormDataClass;

class FormDataTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    private string $url;
    private string|int $correctPaymentId;
    private string|int $correctPaymentAmount;
    private int $correctMerchantId;
    private array $correctData;
    private array $correctHeaders;
    public string $contentType;
    
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        /*
         * Для тестов корректной работы
         */
        $this->url = '/payment';
        $this->correctMerchantId = 816;
        $this->correctPaymentId = 7;
        $this->correctPaymentAmount = 539447;
        $this->contentType = 'multipart/form-data';
        $this->correctHeaders = [
            'Content-Type' => $this->contentType,
            'Authorization' => 'ac5ea1d14b7bad5c190c4499a6224357'
        ];
        $this->correctData = [
            'project' => $this->correctMerchantId,
            'invoice' => $this->correctPaymentId,
            'status' => 'paid',
            'amount' => $this->correctPaymentAmount,
            'amount_paid' => $this->correctPaymentAmount,
            'rand' => 'SNuHufEJ'
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
        $response = $this->post($this->url);
        $response->assertStatus(200);
    }

    /**
     * Проверка на неверную подпись
     * @return void
     */
    public function test_form_broken_signature_status_fail(): void
    {
        $headers = $this->correctHeaders;
        $headers['Authorization'] = '00000001e12';
        $response = $this->withHeaders($headers)
            ->post($this->url, $this->correctData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    /**
     * Проверка на неверный id заказа, при этом верную подпись
     * @return void
     */
    public function test_form_incorrect_payment_id_status_fail(): void
    {
        $data = $this->correctData;
        $data['invoice'] = 99999;

        // Меняем, иначе всегда будет срабатывать проверка на не верную сигнатуру
        $headers = $this->correctHeaders;
        $headers['Authorization'] = '9c441bb5e0e35c3b819ec132051100e9';

        $response = $this->withHeaders($headers)
            ->post($this->url, $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    /**
     * Проверка на неверную стоимость заказа, при этом верную подпись
     * @return void
     */
    public function test_form_incorrect_amount_status_fail(): void
    {
        $data = $this->correctData;
        $data['amount'] = 99;

        // Меняем, иначе всегда будет срабатывать проверка на не верную сигнатуру
        $headers = $this->correctHeaders;
        $headers['Authorization'] = '19ff6f0e53136727861cfc93e8035bc8';

        $response = $this->withHeaders($headers)
            ->post($this->url, $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    /**
     * Проверка на выполнение условия проверки лимита на день
     * @return void
     */
    public function test_form_more_than_limit_data_status_fail(): void
    {
        $randomPayment = Payment::where([
            'merchant_id' => $this->correctMerchantId,
            'payment_id' => $this->correctPaymentId
        ])->first();
        $oldAmount = $randomPayment->amount;

        $randomPayment->amount = FormDataClass::SUM_LIMIT + 1;
        $randomPayment->save();

        $response = $this->withHeaders($this->correctHeaders)
            ->post($this->url, $this->correctData);


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
    public function test_form_broken_data_response_status_exists(): void
    {
        $response = $this->post($this->url, ['name' => '123']);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    /**
     * Проверка на "все ок"
     * @return void
     */
    public function test_form_correct_data_status_success(): void
    {
        $response = $this->withHeaders($this->correctHeaders)
            ->post($this->url, $this->correctData);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }
}
