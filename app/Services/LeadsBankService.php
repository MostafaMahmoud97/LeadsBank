<?php


namespace App\Services;


use App\Http\Resources\LeadsBank\AvailableLeadsBankPaginatResource;
use App\Http\Resources\LeadsBank\SoldLeadsBankPaginatResource;
use App\Models\City;
use App\Models\LeadsBank;
use App\Models\ServiceType;
use App\Models\State;
use App\Traits\ConsumesExternalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class LeadsBankService
{
    use ConsumesExternalService;

    public $baseUri;
    public $api_key;

    public function __construct()
    {
        $this->baseUri = config("gateway_services.crm.base_uri");
        $this->api_key = config("gateway_services.crm.api_key");
    }

    public function getServicesType(){

        $headers = [
            "Accept" => "application/json"
        ];

        try{
            $response = $this->performRequest("GET","service/services-api",[],$headers);
        }catch (\Exception $e){
            return Response::errorResponse($e);
        }

        return json_decode($response);
    }

    public function store($request){

        $stateId  = $request->state_id;
        $cityName = $request->city_name;

        if ($request->is_alter_address) {
            $stateId  = $request->alter_state;
            $cityName = $request->alter_city;
        }

        $city = City::where([
            ["state_id", "=", $stateId],
            ["name", "like", "%" . $cityName . "%"]
        ])->first();

        $state = State::find($stateId);

        if (!$city) {
            $city = City::create([
                "name"     => $cityName,
                "state_id" => $stateId
            ]);
        };

        $location = $request->location;

        if ($request->is_alter_address) {
            $location = $request->alter_address . ', ' . $city->name . ', ' . $state->name . ' ' . $request->alter_zip_code . ', USA';
        }

        $lat = null;
        $lng = null;

        if ($request->has('building_coordinates')
            && $request->filled('building_coordinates')
            && is_array($request->building_coordinates)) {
            $lat = $request->building_coordinates['lat'];
            $lng = $request->building_coordinates['lng'];
        }

        $LeadsBank = LeadsBank::create([
            'customer_type'             => $request->customer_type,
            'first_name'                => $request->first_name,
            'last_name'                 => $request->last_name,
            'phone'                     => $request->phone,
            'is_phone_receives_txt'     => $request->is_phone_receives_txt,
            'mobile'                    => $request->mobile,
            'is_mobile_receives_txt'    => $request->is_mobile_receives_txt,
            'email'                     => $request->email,
            'preferred_language'        => $request->preferred_language,
            'location'                  => $location,
            'street'                    => ($request->is_alter_address) ? $request->alter_address : $request->street,
            'unit'                      => $request->unit,
            'country_id'                => $request->country_id,
            'state_id'                  => ($request->is_alter_address) ? $request->alter_state : $request->state_id,
            'city_id'                   => $city->id,
            'is_active'                 => $request->is_active,
            'is_hoa'                    => $request->is_hoa,
            'company_name'              => $request->company_name,
            'company_business_model'    => $request->company_business_model,
            'user_id'                   => Auth::id(),
            'source'                    => $request->source,

            //----
            'building_type'             => $request->building_type,
            'time_to_contact'           => $request->time_to_contact,
            'way_to_contact'            => $request->way_to_contact,
            'is_decision_maker_present' => $request->is_decision_maker_present,
            'house_ownership'           => $request->house_ownership,
            'zip_code'                  => ($request->is_alter_address) ? $request->alter_zip_code : $request->zip_code,
            'lat'                       => $lat,
            'lng'                       => $lng,
            'citizenship_status'        => $request->citizenship_status,
            'county'                    => $request->county,

            'rate'                      => $request->rate,
            'last_time_communicated'    => $request->last_time_communicated,
            'transaction_type'          => $request->transaction_type,
            'price_percentage'          => $request->price_percentage,
            'commission_based'          => $request->commission_based,
            'commission_type'           => $request->commission_type,
            'description'               => $request->description
        ]);

        if($request->service_types && count($request->service_types) > 0){
            foreach ($request->service_types as $type){
                $service_type = ServiceType::create([
                    "service_id" => $type['service_id'],
                    "title" => $type['title'],
                    "leads_bank_id" => $LeadsBank->id
                ]);
            }
        }

        return Response::successResponse($LeadsBank,"Lead has been created success");
    }

    public function index_available_leads_bank(){
        $user_id = Auth::id();
        $Leads = LeadsBank::where("user_id",$user_id)->with(["ServiceTypes" => function($q){
            $q->select("id","leads_bank_id","title");
        }])->WhereDoesntHave("Clients")->paginate(10);

        return Response::successResponse(AvailableLeadsBankPaginatResource::make($Leads),"Leads have been fetched success");
    }

    public function change_archive_status($leads_id){
        $user_id = Auth::id();
        $Lead = LeadsBank::where("user_id",$user_id)->WhereDoesntHave("Clients")->find($leads_id);
        if(!$Lead){
            return Response::errorResponse("you don't have lead with this id");
        }

        $Lead->is_archive = !$Lead->is_archive;
        $Lead->save();

        return Response::successResponse($Lead,"Lead has been changed archive status success");
    }

    public function showAvailableLead($lead_id){
        $user_id = Auth::id();
        $Lead = LeadsBank::where("user_id",$user_id)->with("ServiceTypes")
            ->WhereDoesntHave("Clients")->find($lead_id);

        if(!$Lead){
            return Response::errorResponse("you don't have lead with this id");
        }

        return Response::successResponse($Lead,"Lead has been fetched success");
    }

    public function updateAvailableLead($lead_id,$request){
        $user_id = Auth::id();
        $Lead = LeadsBank::where("user_id",$user_id)->WhereDoesntHave("Clients")->find($lead_id);

        if(!$Lead){
            return Response::errorResponse("you don't have lead with this id");
        }

        $stateId  = $request->state_id;
        $cityName = $request->city_name;

        if ($request->is_alter_address) {
            $stateId  = $request->alter_state;
            $cityName = $request->alter_city;
        }

        $city = City::where([
            ["state_id", "=", $stateId],
            ["name", "like", "%" . $cityName . "%"]
        ])->first();

        $state = State::find($stateId);

        if (!$city) {
            $city = City::create([
                "name"     => $cityName,
                "state_id" => $stateId
            ]);
        };

        $location = $request->location;

        if ($request->is_alter_address) {
            $location = $request->alter_address . ', ' . $city->name . ', ' . $state->name . ' ' . $request->alter_zip_code . ', USA';
        }

        $lat = null;
        $lng = null;

        if ($request->has('building_coordinates')
            && $request->filled('building_coordinates')
            && is_array($request->building_coordinates)) {
            $lat = $request->building_coordinates['lat'];
            $lng = $request->building_coordinates['lng'];
        }

        $LeadsBank = $Lead->update([
            'customer_type'             => $request->customer_type,
            'first_name'                => $request->first_name,
            'last_name'                 => $request->last_name,
            'phone'                     => $request->phone,
            'is_phone_receives_txt'     => $request->is_phone_receives_txt,
            'mobile'                    => $request->mobile,
            'is_mobile_receives_txt'    => $request->is_mobile_receives_txt,
            'email'                     => $request->email,
            'preferred_language'        => $request->preferred_language,
            'location'                  => $location,
            'street'                    => ($request->is_alter_address) ? $request->alter_address : $request->street,
            'unit'                      => $request->unit,
            'country_id'                => $request->country_id,
            'state_id'                  => ($request->is_alter_address) ? $request->alter_state : $request->state_id,
            'city_id'                   => $city->id,
            'is_active'                 => $request->is_active,
            'is_hoa'                    => $request->is_hoa,
            'company_name'              => $request->company_name,
            'company_business_model'    => $request->company_business_model,
            'user_id'                   => Auth::id(),
            'source'                    => $request->source,

            //----
            'building_type'             => $request->building_type,
            'time_to_contact'           => $request->time_to_contact,
            'way_to_contact'            => $request->way_to_contact,
            'is_decision_maker_present' => $request->is_decision_maker_present,
            'house_ownership'           => $request->house_ownership,
            'zip_code'                  => ($request->is_alter_address) ? $request->alter_zip_code : $request->zip_code,
            'lat'                       => $lat,
            'lng'                       => $lng,
            'citizenship_status'        => $request->citizenship_status,
            'county'                    => $request->county,

            'rate'                      => $request->rate,
            'last_time_communicated'    => $request->last_time_communicated,
            'transaction_type'          => $request->transaction_type,
            'price_percentage'          => $request->price_percentage,
            'commission_based'          => $request->commission_based,
            'commission_type'           => $request->commission_type,
            'description'               => $request->description
        ]);

        $ServiceTypes = $Lead->ServiceTypes;

        foreach ($ServiceTypes as $serviceType){
            $serviceType->delete();
        }

        if($request->service_types && count($request->service_types) > 0){
            foreach ($request->service_types as $type){
                $service_type = ServiceType::create([
                    "service_id" => $type['service_id'],
                    "title" => $type['title'],
                    "leads_bank_id" => $Lead->id
                ]);
            }
        }

        return Response::successResponse($LeadsBank,"lead has been updated success");
    }

    public function deleteAvailableLead($lead_id){
        $user_id = Auth::id();
        $Lead = LeadsBank::where("user_id",$user_id)->WhereDoesntHave("Clients")->find($lead_id);

        if(!$Lead){
            return Response::errorResponse("you don't have lead with this id");
        }

        $Lead->delete();
        return Response::successResponse([],"Lead has been deleted");
    }

    public function index_sold_leads(){
        $user_id = Auth::id();
        $Leads = LeadsBank::where("user_id",$user_id)->with(["ServiceTypes" => function($q){
            $q->select("id","leads_bank_id","title");
        }])->whereHas("Clients")->paginate(10);

        return Response::successResponse(SoldLeadsBankPaginatResource::make($Leads),"Leads have been fetched success");
    }

    public function show_sold_leads($lead_id){
        $user_id = Auth::id();
        $Lead = LeadsBank::where("user_id",$user_id)->with(["ServiceTypes" => function($q){
            $q->select("id","leads_bank_id","title");
        },"Clients"])->whereHas("Clients")->find($lead_id);

        if(!$Lead){
            return Response::errorResponse("you don't have lead with this id");
        }

        return Response::successResponse($Lead,"Lead has been fetched success");
    }
}
