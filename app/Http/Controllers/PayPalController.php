<?php

namespace App\Http\Controllers;

use App\Services\PayPalService;
use Illuminate\Http\Request;

class PayPalController extends Controller
{
    protected $service;

    public function __construct(PayPalService $service)
    {
        $this->service = $service;
    }

    public function checkOut(Request $request){
        return $this->service->checkOut($request);
    }

    public function cancel(Request $request){
        return $this->service->callbackCancel($request);
    }

    public function success(Request $request){
        return $this->service->callbackSuccess($request);
    }
}
