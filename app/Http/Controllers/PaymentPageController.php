<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\Payment\Factories\HandlerFactory;

class PaymentPageController extends Controller
{
    public function update(Request $request){
        $data = $request->all();
        $method = $request->header('Content-Type');
        $paymentHandler = (new HandlerFactory($method));
        $result = json_encode($paymentHandler->run($data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return response($result)->header('Content-type', 'application/json');
    }
}
