<?php

namespace App\Http\Controllers;

use App\Services\CheckOutService;
use Illuminate\Http\Request;

class CheckOutController extends Controller
{
    protected $service;
    public function __construct(CheckOutService $service)
    {
        $this->service = $service;
    }

    public function checkOut(Request $request){
        return $this->service->checkOut($request);
    }

    public function CreateClients(Request $request){
        return $this->service->CreateClient($request);
    }
}
