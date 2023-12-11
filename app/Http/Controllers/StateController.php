<?php

namespace App\Http\Controllers;

use App\Services\StateService;
use Illuminate\Http\Request;

class StateController extends Controller
{
    protected $serviec;
    public function __construct(StateService $service)
    {
        $this->serviec = $service;
    }

    public function getStateBasedOnGoogle($state_name){
        return $this->serviec->getStateBasedOnGoogle($state_name);
    }
}
