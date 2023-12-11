<?php

namespace App\Http\Controllers;

use App\Services\CRMService;
use Illuminate\Http\Request;

class CRMController extends Controller
{
    protected $service;
    public function __construct(CRMService $service)
    {
        $this->service = $service;
    }

    public function GetStatesAndTransactionType(){
        return $this->service->GetStatesAndTransactionType();
    }

    public function GetLeadsBankBasedOnStateAndTransactionType(Request $request){
        return $this->service->GetLeadsBankBasedOnStateAndTransactionType($request);
    }

    public function addLeadsToCart(Request $request){
        return $this->service->addLeadToCart($request);
    }

    public function showLeadsDetailsInDashboard($client_id){
        return $this->service->showLeadsDetailsInDashboard($client_id);
    }

    public function openCartDetails(Request $request){
        return $this->service->openCartDetails($request);
    }

    public function removeLeadFromCart(Request $request){
        return $this->service->removeLeadFromCart($request);
    }

}
