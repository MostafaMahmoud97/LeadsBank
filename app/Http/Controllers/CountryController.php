<?php

namespace App\Http\Controllers;

use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    protected $service;

    public function __construct(CountryService $service)
    {
        $this->service = $service;
    }

    public function index(){
        return $this->service->index();
    }

    public function getStateBasedOnCountry($country_id){
        return $this->service->getStateBasedOnCountry($country_id);
    }

    public function getCities(Request $request){
        $Validator = Validator::make($request->all(),[
            "state_id" => "required|numeric|exists:states,id",
        ]);

        if ($Validator->fails()){
            return Response::errorResponse($Validator->errors());
        }

        return $this->service->getCities($request);
    }
}
