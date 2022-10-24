<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $statusRand = rand(0, count(config('app.paymentStatuses'))-1);
        return [
            'merchant_id' => (rand(0,1) == 1) ? env('JSON_MERCHANT_ID') : config('app.merchantSecrets.form.id'),
            'payment_id' => rand(1,200000),
            'status' => config('app.paymentStatuses')[$statusRand],
            'user_id' => 1,
            'amount' => rand(10, 1000),
        ];
    }

}
