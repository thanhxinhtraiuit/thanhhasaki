<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Order;
use Illuminate\Http\Request;
use Auth;
use App\Status;
use App\User;
use App\Person;
use DB;
use App\Doisoat;
class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDetailOrder($orders){

    }
    public function index()
    {
        $user_id = Auth::user()->id;
        // $user_id = 1;
        $orders = Order::where('user_id',$user_id)->with('PickUper','Receiver','getStatus')->get();
        // $aab = $order->getStatus->value;
        $data = [
            'message' => 'ok',
            'status' => '1',
            'results' => $orders
        ];

        return response()->json($data);
    }

    public function getListStatus(){
        $results = Status::all();
        $data = [
            'status' => 1,
            'message' => 'ok',
            'results' =>  $results,
        ];
        return response()->json($data);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request  $request)
    {
        $request->validate([
            
    "pickup_phone" => 'required|numeric',
    "pickup_address" => 'required|string',
    // "pickup_commune" => 'required|string',
    "pickup_district" => 'required|string',
    "pickup_province" => 'required|string',
    "name" => 'required|string',
    "phone" => 'required|numeric',
    // "email" => 'required|string',
    "address" => 'required|string',
    "province" => 'required|string',
    "district" => 'required|string',
    "commune" => 'required|string',
    "amount" => 'required|numeric',
    // "value" => 'required|numeric',
    "weight"  => 'required|numeric',
    "payer" => 'required|numeric',
    "service" => 'required|numeric',
    "config" => 'required|numeric',
        // "soc" => 'required|string',
        // "note" => 'required|string',
    "product_type" => 'required|numeric',
    // "products" => 'required|string',

        ]);
       //{pickup_code,pickup_phone,pickup_address,pickup_province,pickup_district,pickup_commune}
        $user_id = Auth::user()->id;
        //luu nguoi gui
        $arrPerson = [
            'code' => $request->pickup_code ?? null,
            'name' =>  null,
            'email' =>  null,
            'phone' => $request->pickup_phone,
            'sphone' => null,
            'address' => $request->pickup_address,
            'province' => $request->pickup_province,
            'district' => $request->pickup_district,
            'commune' => $request->pickup_commune,
            'type' => 1,
        ];
        DB::beginTransaction();
        try {
       
        $pickuper = new Person($arrPerson);
        $pickuper->save();

        $pickup_id = $pickuper->id;
        //luu nguoi nhan
        $arrPerson = [
            'code' => null,
            'name' =>  $request->name ?? null,
            'email' => $request->email ?? null,
            'phone' => $request->phone,
            'sphone' => $request->sphone ?? null,
            'address' => $request->address,
            'province' => $request->province,
            'district' => $request->district,
            'commune' => $request->commune,
            'type' => 2,
        ];

        $receiver = new Person($arrPerson);
        $receiver->save();

        $receiver_id = $receiver->id;

        $amount = $request->amount;
        $value = $request->value;
        $weight = $request->weight;
        $note = $request->note;
        $service = $request->service;
        $config = $request->config;
        $payer = $request->payer;
        $product_type = $request->product_type;
        $product = $request->product ?? null;
        $products = $request->products ?? null;
        $barter = $request->barter;
        $notes = $request->notes ?? null;

        $id_last = Order::orderBy('id','desc')->first()->id ?? 0;
        $id_last+=1;
        $soc = 'PK.DACN'. $id_last;

        $code = 'MDH>DACN'.$id_last;
        //luu order
        $fee = 50000;
        $status = 1 ;
        $pickup = null;
        $delivery = null;
        $journeys = null;

        $arrOrder =  [
        'code' => $code, 
        'soc' =>  $soc,
        'pickup_id' => $pickup_id,
        'receiver_id' => $receiver_id,
        'amount' => $amount,
        'value' => $value,
        'fee' => $fee,
        'weight' => $weight ,
        'note' => $note,
        'service' => $service,
        'config' => $config,
        'payer' => $payer ,
        'product_type' => $product_type,
        'product' => $product,
        'products' => json_encode($products),
        'barter' => $barter,
        'pickup' => $pickup,
        'delivery' => $delivery,
        'journeys' => $journeys,
        'notes' => $notes,
        'user_id' => $user_id,
        'status' =>$status,
         ];

        $order = Order::create($arrOrder);
        $aab = $order->getStatus->value;
        // dd($aab);

        DB::commit();
        } catch (\Exception $e) {
        DB::rollBack();
        
        \Log::info($e);

        // return "cc";
        }




    // $status_name = "Chờ Duyệt"; 

    $results = [ 
        'code' => $code ,
        'soc' => $soc,
        'phone' => $request->phone,
        'amount' =>$amount,
        'weight' => $weight,
        'fee' => $fee,
        'status'=> $status,
        'status_name' =>$aab,
     ];

     $data = [
        'message' => 'Create Order Success',
        'status' =>'1',
        'results' => $results,
     ];
     // dd($aab);
    return response()->json($data);
}
    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $results = ($order->with('PickUper','Receiver','getStatus')->first()); 
        $data = [
            'message' => 'Ok',
            'status'=>'1',
            'results' => $results,

        ];


        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        
        if($order->status != 1) return response()->json(['Error'=>'No update when status there']);
        $order->update($request->only(['phone','name','email']));

        $results = ($order->with('PickUper','Receiver')->first());
        $data = [
            'status'=>1,
            'message' =>"ok",
            'results' =>$results,
        ];
        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        $order->update([
            'status'=>26,
        ]);
    }

    public function getStatus(Request $request){
        $request->validate([
            'code' => 'required|string',
        ]);

        // dd($request->code);
        $order = Order::where('code',$request->code)->first();
        $statusName = $order->getstatus->value;
        // dd($statusName);
        $results =[
            'code' => $order->code,
            'status' => $order->status,
            'status_name' => $statusName,
        ];
        $data =[
            "status" => 1,
            "message" => "ok",
            'results' => $results,
        ];
        return response()->json($data);

    }

    

    public function updateStatus(Request $request){
        $request->validate([
            'code' => 'required|string',
            'status' => 'required|numeric'
        ]);
        $order = Order::where('code',$request->code)->first();

        $order->update([
            'status'=>$request->status,
        ]);

        $statusName = $order->getstatus->value;
        // dd($statusName);
        $results =[
            'code' => $order->code,
            'status' => $order->status,
            'status_name' => $statusName,
        ];
        $data =[
            "status" => 1,
            "message" => "ok",
            'results' => $results,
        ];
        return response()->json($data);
    }

    public function doisoat1donhang( $code ){
        // $code =$request->code;
        $check= -1;
        $listCodeOrder = Doisoat::select('code')->get();
        foreach ($listCodeOrder as $element) {
            if($element->code ==$code){
                $check=5;
            }
        }

        if($check != 5 ){

            $order = Order::where('code',$code)->first();
            $phibaohiem =0;
            $amount =0;
            $fee=0;
            if(!empty($order->value) && !empty($order->amount) && !empty($order->fee))
            {   $amount = $order->amount;
                $fee = $order->fee;
                if( $order->value > 1000000 ) {
                if($order->value > 10000000) $phibaohiem= $order->value*0.0014;
                else
                $phibaohiem= $order->value*0.0008;
            }}

            $tiendoisoat = $amount - $fee -$phibaohiem ;

            $arrDoiSoat = [
                "code"=>"$code",
                "tiendoisoat"=>" $tiendoisoat",
                "status" => 30,

            ];

            $doisoat = Doisoat::create($arrDoiSoat);
        }


    }

    public function doiSoatToanBoCua1User($user_id){
        
        $listCodeDonHang = Order::select('code')->where('user_id',$user_id)->get();

        foreach($listCodeDonHang as $element){
            $this->doisoat1donhang($element);
        };
       
    }



    public function  doiSoatToanBoOrder(){
        $listIdUser = User::select('id')->get();

        foreach ($listIdUser as $element) {
            $this->doiSoatToanBoCua1User( $element->id);
        }


        $doisoat = Doisoat::all();
        $data = [
            'status'=>1,
            'message'=>'ok',
            'results' => $doisoat,
        ];
        return response()->json($data);
       
    }

    public function getAllDoiSoat(){
        $doisoat = Doisoat::all();
        $data = [
            'status'=>1,
            'message'=>'ok',
            'results' => $doisoat,
        ];
        return response()->json($data);
    }
}
