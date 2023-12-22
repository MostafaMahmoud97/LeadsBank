<?php


namespace App\Services;


use App\Models\Client;
use App\Models\CommissionCart;
use App\Models\ImmediateCart;
use App\Models\Invoice;
use App\Traits\ConsumesExternalService;
use Illuminate\Support\Facades\Response;

class CheckOutService
{
    use ConsumesExternalService;

    public $baseUri;
    public $api_key;

    public function __construct()
    {
        $this->baseUri = config("gateway_services.crm.base_uri");
        $this->api_key = config("gateway_services.crm.api_key");
    }

    public function checkOut($request){
        if ($request->transaction_type == "immediate"){
            $ImmediateCarts = $this->checkImmediateCart($request);

            if ($ImmediateCarts["status"] == "error" || $ImmediateCarts["status"] == "other_invoice_created"){

                return [
                    "status" => false,
                    "message" => $ImmediateCarts["message"]
                ];
            }else{
                $totalCoins = 0;

                foreach ($ImmediateCarts["data"] as $cart){
                    $totalCoins += $cart["LeadBank"]->price_x;
                }

                if ($totalCoins > $request->balance){
                    return [
                        "status" => false,
                        "message" => "You do not have enough coins to buy these pens"
                    ];
                }



            }


            $Invoice = Invoice::create([
                "client_id" => $request->client_id,
                "total_coins" => $totalCoins,
            ]);

            $LeadsBank = [];
            foreach ($ImmediateCarts["data"] as $cart){
                array_push($LeadsBank,$cart["LeadBank"]);
                $cart->invoice_id = $Invoice->id;
                $cart->save();
            }



            return [
                "status" => true,
                "data" => [
                    "client_id" => $request->client_id,
                    "total_coins" => $totalCoins,
                    "leads_bank" => $LeadsBank
                ]
            ];

        }else{
            $CommissionCarts = $this->checkCommissionBasedCart($request);

            if ($CommissionCarts["status"] == "error" || $CommissionCarts["status"] == "other_user_created"){

                return [
                    "status" => false,
                    "message" => $CommissionCarts["message"]
                ];
            }else{
                $LeadsBank = [];
                foreach ($CommissionCarts["data"] as $cart){
                    array_push($LeadsBank,$cart["LeadBank"]);
                    $cart->is_take = 1;
                    $cart->save();
                }

                return [
                    "status" => true,
                    "data" => [
                        "client_id" => $request->client_id,
                        "leads_bank" => $LeadsBank
                    ]
                ];
            }
        }



    }

    public function CreateClient($request){
        foreach ($request->leads_data as $data){
            $Client = Client::create([
                "leads_bank_id" => $data["leads_bank_id"],
                "leads_id" => $data["lead_id"],
                "client_id" => $request->client_id,
                "name" => $request->name,
                "email" => $request->email,
                "phone" => $request->phone
            ]);
        }

        return [
            "status" => true,
            "data" => "Client has been created"
        ];
    }

    private function checkImmediateCart($request){
        $ImmediateCarts = ImmediateCart::with("LeadBank")
            ->where("client_id",$request->client_id)
            ->whereDoesntHave("Invoice")->get();

        if ($ImmediateCarts->count() == 0){
            return [
                "status" => "error",
                "message" => "you don't have leads in Immediate Cart"
            ];
        }

        foreach ($ImmediateCarts as $cart){
            $ImmediateCart = ImmediateCart::with(["LeadBank"])
                ->where("client_id","!=",$request->client_id)
                ->where("leads_bank_id" , $cart->leads_bank_id)
                ->where("invoice_id","!=",0)->first();

            if ($ImmediateCart){
                return [
                    "status" => "other_invoice_created",
                    "message" => "you can't buy this lead ".$ImmediateCart["LeadBank"]->first_name." ".$ImmediateCart["LeadBank"]->last_name
                ];
            }
        }

        return [
            "status" => "success",
            "data" => $ImmediateCarts
        ];
    }

    private function checkCommissionBasedCart($request){
        $CommissionCarts = CommissionCart::with("LeadBank")
            ->where(["client_id"=>$request->client_id,"is_take" => 0])->get();

        if ($CommissionCarts->count() == 0){
            return [
                "status" => "error",
                "message" => "you don't have leads in Commission Cart"
            ];
        }

        foreach ($CommissionCarts as $cart){
            if ($cart["LeadBank"]->commission_type == "exclusive"){
                $CommissionCart = CommissionCart::with(["LeadBank"])
                    ->where("client_id","!=",$request->client_id)
                    ->where("leads_bank_id" , $cart->leads_bank_id)
                    ->where("is_take","!=",0)->first();

                if ($CommissionCart){
                    return [
                        "status" => "other_user_created",
                        "message" => "you can't take this lead ".$cart["LeadBank"]->first_name." ".$cart["LeadBank"]->last_name
                    ];
                }

            }else{
                $CommissionCartCount = CommissionCart::with(["LeadBank"])
                    ->where("client_id","!=",$request->client_id)
                    ->where("leads_bank_id" , $cart->leads_bank_id)
                    ->where("is_take","!=",0)->get()->count();

                if ($CommissionCartCount >= 4){
                    return [
                        "status" => "other_user_created",
                        "message" => "you can't take this lead ".$cart["LeadBank"]->first_name." ".$cart["LeadBank"]->last_name
                    ];
                }
            }

        }

        return [
            "status" => "success",
            "data" => $CommissionCarts
        ];
    }
}
