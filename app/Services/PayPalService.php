<?php


namespace App\Services;


use App\Models\ImmediateCart;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Support\Facades\Response;
use Srmklive\PayPal\Services\ExpressCheckout;


class PayPalService
{
    public function checkOut($request){

        $Check = $this->checkImmediateCart($request);

        if ($Check["check"] == "other_invoice_created"){
            $error_mss = [];

            foreach ($Check["ImmediateCart"] as $item){
                $mss = "you can't buy this lead ".$item['LeadBank']->first_name." ".$item['LeadBank']->last_name;
                array_push($error_mss,$mss);
            }

            return [
                'code'    => 320,
                'status'  => 0,
                'errors'  => [],
                'message' => $error_mss,
                'data'    => []
            ];
        }elseif ($Check["check"] == "error") {
            return [
                'code'    => 320,
                'status'  => 0,
                'errors'  => [],
                'message' => [$Check["message"]],
                'data'    => []
            ];
        }elseif ($Check["check"] == "create_new_invoice"){

            $ImmediateCarts = ImmediateCart::where("client_id",$request->client_id)
                ->whereIn("leads_bank_id",$request->leads_ids)
                ->WhereDoesntHave("Invoice")->get();
            $totalPrice = 0;

            foreach ($ImmediateCarts as $cart){
                $totalPrice+= $cart['LeadBank']->price_percentage;
            }

            $Invoice = Invoice::create([
                "client_id" => $request->client_id,
                "total_price" => $totalPrice
            ]);

            foreach ($ImmediateCarts as $cart){
                $cart->invoice_id = $Invoice->id;
                $cart->save();
            }

            $data = [];
            $data['items'] = [];

            foreach ($ImmediateCarts as $cart){
                array_push($data['items'],[
                    'name' => $cart["LeadBank"]->first_name." ".$cart["LeadBank"]->last_name,
                    'price' => $cart["LeadBank"]->price_percentage,
                    'qty' => $cart->quantity,
                    'description' => $cart->description." ".$cart->id
                ]);
            }

            $data['invoice_id'] = $Invoice->id;
            $data['invoice_description'] = 'Bay Invoice';
            $data['return_url'] = config('paypal_services.url.return_url');
            $data['cancel_url'] = config('paypal_services.url.cancel_url');
            $data['total'] = $Invoice->total_price;

            return $this->sendPayment($data);

        }elseif ($Check["check"] == "invoice_created"){
            $Transaction = Transaction::where('invoice_id',$Check["invoice_id"])->first();

            if ($Transaction->status == "success"){
                $Transaction['message'] = 'Transaction is Paid';
                return $Transaction;
                //return Response::successResponse($Transaction,'Transaction is Paid');
            }elseif ($Transaction->status == "canceled"){
                $Transaction['message'] = 'Transaction has canceled';
                return $Transaction;
                //return Response::successResponse($Transaction,'Transaction is canceled');
            }else{
                $Transaction['message'] = 'Transaction has not paid';
                return $Transaction;
                //return Response::successResponse($Transaction,'Transaction is not paid');
            }

        }
    }

    public function sendPayment($data){

        try {
            $provider = new ExpressCheckout;
            $response = $provider->setExpressCheckout($data);
            $response = $provider->setExpressCheckout($data, true);

            $Invoice = Invoice::find($data['invoice_id']);

            Transaction::create([
                'invoice_id' => $data['invoice_id'],
                'client_id' => $Invoice->client_id,
                'token' => $response['TOKEN'],
                'correlation' => $response['CORRELATIONID'],
                'build' => $response['BUILD'],
                'paypal_link' => $response['paypal_link']
            ]);


            return $response;
        }catch (\Exception $e){

            return [
                'code'    => 450,
                'status'  => 0,
                'errors'  => [],
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }
    }

    public function callbackCancel($request){
        try {
            $provider = new ExpressCheckout;
            $response = $provider->getExpressCheckoutDetails($request->token);
            if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING']) && in_array(strtoupper($response['BILLINGAGREEMENTACCEPTEDSTATUS']), ['0'])) {

                $Transaction = Transaction::where('token', $request->token)->first();

                $Transaction->update([
                    'status' => 'canceled'
                ]);

                $Invoice = $Transaction->Invoice;

                $Invoice->update([
                    "status" => "canceled"
                ]);

                  dd('Transaction Canceled');
                //return redirect()->to('https://boxbyld.tech/payment-leads/'.$Transaction->invoice_id."?message=Transaction Canceled");
            }
            dd('Please Try Again Later...');
            //return redirect()->to("https://boxbyld.tech/crm/leads?message=Please Try Again Later...");
            //return Response::successResponse($Transaction,'Transaction Canceled');
        }catch (\Exception $e){

            return [
                'code'    => 450,
                'status'  => 0,
                'errors'  => [],
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }
    }

    public function callbackSuccess($request){
        try {
            $provider = new ExpressCheckout;
            $response = $provider->getExpressCheckoutDetails($request->token);
            $data = $this->initialData($response["INVNUM"]);
            $provider->doExpressCheckoutPayment($data,$request->token,$request->PayerID);
            $response = $provider->getExpressCheckoutDetails($request->token);
            if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING']) && in_array(strtoupper($response['BILLINGAGREEMENTACCEPTEDSTATUS']), ['1']) && in_array(strtoupper($response['CHECKOUTSTATUS']), ['PAYMENTACTIONCOMPLETED'])) {

                $Transaction = Transaction::where('token', $response['TOKEN'])->first();

                $Transaction->update([
                    'status' => 'success'
                ]);

                $Invoice = $Transaction->Invoice;

                $Invoice->update([
                    "status" => "success"
                ]);

                //$MoveCheck = $this->MoveLeadsBankToLead($Transaction->invoice_id, $Transaction->user_id);

//                if ($MoveCheck === true) {
//                    return redirect()->to('https://boxbyld.tech/payment-leads/'.$Transaction->invoice_id."?message=Transaction Success");
//                    dd('Transaction Success');
//                } else {
//                    return redirect()->to("https://boxbyld.tech/crm/leads?message=Error In Move");
//                    dd("Error In Move");
//                }

                return Response::successResponse($Transaction,'Transaction Success');
            }
            //return redirect()->to("https://boxbyld.tech/crm/leads?message=Please Try Again Later...");

             return Response::errorResponse('Please Try Again Later...');
        }catch (\Exception $e){
            return [
                'code'    => 450,
                'status'  => 0,
                'errors'  => [],
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }
    }

    protected function initialData($Invoice_id){
        $Invoice = Invoice::with(['ImmediateCarts' => function($q){
            $q->with("LeadBank");
        }])->find($Invoice_id);
        $data = [];
        $data['items'] = [];

        foreach ($Invoice->ImmediateCarts as $cart){
            array_push($data['items'],[
                'name' => $cart["LeadBank"]->first_name." ".$cart["LeadBank"]->last_name,
                'price' => $cart["LeadBank"]->price_percentage,
                'qty' => $cart->quantity,
                'description' => $cart->description." ".$cart->id
            ]);
        }

        $data['invoice_id'] = $Invoice->id;
        $data['invoice_description'] = 'Bay Invoice';
        $data['return_url'] = config('paypal_services.url.return_url');
        $data['cancel_url'] = config('paypal_services.url.cancel_url');
        $data['total'] = $Invoice->total_price;

        return $data;
    }


    private function checkImmediateCart($request){
        $ImmediateCartsWithInvoiceWithSuccessInvoice = ImmediateCart::with(["LeadBank"=> function($q){
            $q->select("id","first_name","last_name");
        },"Invoice"])->where("client_id","!=",$request->client_id)
            ->whereIn("leads_bank_id",$request->leads_ids)
            ->whereHas("Invoice")->get();


        if ($ImmediateCartsWithInvoiceWithSuccessInvoice->count() != 0){
            return [
                "check" => "other_invoice_created",
                "ImmediateCart" => $ImmediateCartsWithInvoiceWithSuccessInvoice
            ];
        }
        //----------------------------------------------->
        $ImmediateCartsWithInvoice = ImmediateCart::where("client_id",$request->client_id)
            ->whereIn("leads_bank_id",$request->leads_ids)
            ->whereHas("Invoice",function ($q){
                $q->where("status","pending")->OrWhere("status","canceled");
            })->get();

        //-------------------------------------->
        $ImmediateCartsWithoutInvoice = ImmediateCart::where("client_id",$request->client_id)
            ->whereIn("leads_bank_id",$request->leads_ids)
            ->WhereDoesntHave("Invoice")->get();
        //-------------------------------------->

        if ($ImmediateCartsWithInvoice->count() == count($request->leads_ids)){
            $Invoice_x = $ImmediateCartsWithInvoice[0]->Invoice;
            $checkInvoiceCorrect = true;

            foreach ($ImmediateCartsWithInvoice as $item){
                if ($Invoice_x->id != $item->invoice_id){
                    $checkInvoiceCorrect = false;
                    break;
                }
            }

            if (!$checkInvoiceCorrect){
                foreach ($ImmediateCartsWithInvoice as $item){
                    $this->deleteInvoiceAndTransaction($item->invoice_id);
                    $item->invoice_id = 0;
                    $item->save();
                }

                return [
                    "check" => "create_new_invoice",
                    "invoice_id" => 0
                ];
            }

            return [
                "check" => "invoice_created",
                "invoice_id" => $Invoice_x->id
            ];
        }elseif(($ImmediateCartsWithInvoice->count() + $ImmediateCartsWithoutInvoice->count()) == count($request->leads_ids)) {
            foreach ($ImmediateCartsWithInvoice as $item){
                $this->deleteInvoiceAndTransaction($item->invoice_id);
                $item->invoice_id = 0;
                $item->save();
            }

            return [
                "check" => "create_new_invoice",
                "invoice_id" => 0
            ];
        }elseif ($ImmediateCartsWithoutInvoice->count() == count($request->leads_ids)){
            return [
                "check" => "create_new_invoice",
                "invoice_id" => 0
            ];
        }else{
            return [
                "check" => "error",
                "message" => "leads ids have been InCorrect"
            ];
        }

    }

    private function deleteInvoiceAndTransaction($Invoice_id){
        $Invoice = Invoice::find($Invoice_id);
        if ($Invoice){
            $Transaction = $Invoice->Transaction;
            if ($Transaction){
                $Transaction->delete();
            }
            $Invoice->delete();
        }
    }
}
