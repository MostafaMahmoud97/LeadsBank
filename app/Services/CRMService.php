<?php


namespace App\Services;


use App\Http\Resources\CRM\LeadsBankPaginate;
use App\Http\Resources\CRM\LeadsBankResource;
use App\Http\Resources\CRM\StateResource;
use App\Models\CommissionCart;
use App\Models\ImmediateCart;
use App\Models\LeadsBank;
use App\Models\State;
use Illuminate\Support\Facades\Response;

class CRMService
{
    public function GetStatesAndTransactionType(){
        $state = State::where("country_id",231)->get();
        $transaction_type = [
            "immediate",
            "commission based"
        ];

        $state = StateResource::collection($state);

        $data = [
            "transaction_type" => $transaction_type,
            "states" => $state
        ];

        return $data;
    }

    public function GetLeadsBankBasedOnStateAndTransactionType($request){
        $Leads_bank_check = LeadsBank::select("id")
            ->where(["state_id" => $request->state_id,"transaction_type"=>"commission based", "commission_type" => "shared"])
            ->withCount("Clients")->get();

        $leads_bank_ids = [];

        foreach ($Leads_bank_check as $item){
            if($item->clients_count >= 4){
                array_push($leads_bank_ids,$item->id);
            }
        }



        $Leads_bank = LeadsBank::select("id","first_name","last_name","rate","transaction_type","commission_based","commission_type","price_percentage")
        ->where(["state_id" => $request->state_id,"transaction_type" => $request->transaction_type])->orderBy("sort_status","ASC");

        if ($request->transaction_type == "immediate"){
            $Leads_bank = $Leads_bank->whereDoesntHave("Clients")->paginate(10);
        }else{
            $Leads_bank = $Leads_bank->where(function ($q) use ($leads_bank_ids){
                $q->where(function ($q){
                    $q->where("commission_type" , "exclusive")->whereDoesntHave("Clients");
                })->OrWhere(function ($q) use ($leads_bank_ids){
                    $q->where("commission_type" , "shared")->whereNotIn("id",$leads_bank_ids);
                });
            })->paginate(10);
        }


        return LeadsBankPaginate::make($Leads_bank);
    }



    public function addLeadToCart($request){
        $Leads = LeadsBank::where("transaction_type" , $request->transaction_type)
            ->whereIn("id",$request->leads_bank_ids)->get();

        if($request->transaction_type == "immediate"){

            foreach ($Leads as $lead){
                $clients = $lead->Clients;
                $ImmediateCart = ImmediateCart::where("leads_bank_id",$lead->id)
                    ->where("client_id",$request->client_id)->first();
                if (!$ImmediateCart && count($clients) == 0){
                    ImmediateCart::create([
                        "client_id" => $request->client_id,
                        "leads_bank_id" => $lead->id,
                    ]);
                }
            }

        }elseif ($request->transaction_type == "commission based"){

            foreach ($Leads as $lead){
                $clients = $lead->Clients;
                $CommissionCart = CommissionCart::where("leads_bank_id",$lead->id)
                    ->where("client_id",$request->client_id)->first();
                if (!$CommissionCart && (($lead->commission_type == "exclusive" && count($clients) == 0) || ($lead->commission_type == "shared" && count($clients) < 4))){
                    CommissionCart::create([
                        "client_id" => $request->client_id,
                        "leads_bank_id" => $lead->id
                    ]);
                }
            }

        }

        return [];
    }

    public function showLeadsDetailsInDashboard($client_id){
        $ImmediateCartCount = ImmediateCart::where(["client_id"=>$client_id,"invoice_id"=>0])->get()->count();
        $CommissionCartCount = CommissionCart::where(["client_id"=>$client_id,"is_take"=>0])->get()->count();

        $data = [
            [
                "title" => "immediate",
                "number_leads" => $ImmediateCartCount
            ],[
                "title" => "commission based",
                "number_leads" => $CommissionCartCount
            ]
        ];

        return $data;
    }

    public function openCartDetails($request){
        if($request->transaction_type == "immediate"){
            $CartDetails = ImmediateCart::where("client_id",$request->client_id)->get();
        }elseif ($request->transaction_type == "commission based"){
            $CartDetails = CommissionCart::where("client_id",$request->client_id)->get();
        }

        $Leads = [];

        foreach ($CartDetails as $detail){
            $LeadsBank = $detail->LeadBank;
            array_push($Leads,$LeadsBank);
        }


        return LeadsBankResource::collection($Leads);
    }

    public function removeLeadFromCart($request){
        if($request->transaction_type == "immediate"){
            $CartDetails = ImmediateCart::where(["client_id"=>$request->client_id,"leads_bank_id" => $request->lead_id])->first();
        }elseif ($request->transaction_type == "commission based"){
            $CartDetails = CommissionCart::where(["client_id"=>$request->client_id,"leads_bank_id" => $request->lead_id])->first;
        }

        if (!$CartDetails){
            return "No Lead by this id";
        }

        $CartDetails->delete();

        return [];
    }
}
