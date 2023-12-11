<?php


namespace App\Services;


use App\Models\City;
use App\Models\Country;
use Illuminate\Support\Facades\Response;


class CountryService
{
    public function index(){
        $countries = Country::all();
        return Response::successResponse($countries,"Countries have been fetched success");
    }

    public function getStateBasedOnCountry($countryId)
    {
        $states = Country::with(['States','States.Cities'])->findOrFail($countryId);
        return Response::successResponse($states, "Country with states has been fetched success");
    }

    public function getCities($request){
        $Cities = City::where("state_id" , $request->state_id)->get();
        return Response::successResponse($Cities,"Cities have been fetched success");
    }
}
