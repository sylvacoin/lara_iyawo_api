<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\TransHeader;
use App\Models\TransLine;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    /**
     * Gets a list of all cards belonging to a customer.
     *
     * @param int $customer_id
     * @return JsonResponse
     */
    public function index(int $customer_id)
    {

        $customer = User::where('customer_no', '<>', NULL)->find($customer_id);
        if ($customer == null) {
            return response()->json([
                'success' => false,
                'message' => 'Customer dont exist'
            ], 403);
        }


        $cards = Card::where('customer_id', $customer_id)->get();
        return response()->json([
            'success' => true,
            'data' => $cards
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $customer_id
     * @param Request $request
     * @return JsonResponse
     */
    public function store(int $customer_id, Request $request)
    {
        $customer = User::where('customer_no', '<>', NULL)->find($customer_id);

        if ($customer == null) {
            return response()->json([
                'success' => false,
                'message' => 'Customer dont exist'
            ], 403);
        }

        $validator = Validator($request->all(), [
            'card_name' => 'required|unique:cards,card_name',
            //'card_count' => 'required|numeric',
            'start_amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->toArray()
            ], 400);
        }

        $card_no = 'ICN-' . date('ymdhi');

        //dd($request);

        $card = Card::create([
            'card_no' => $card_no,
            'card_name' => $request->card_name,
            'card_count' => ($request->card_count == null ? 1 : $request->card_count),
            'start_amount' => $request->start_amount,
            'customer_id' => $customer_id
        ]);

        return response()->json([
            'success' => true,
            'data' => $card
        ], 200);
    }

    /**
     * Deletes a particular transaction from the db
     *
     * @param \App\Models\TransHeader $transHeader
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(int $cardId)
    {
        //get transaction header
        $card = Card::find($cardId);
        if ($card == null) {
            return response()->json([
                'success' => false,
                'message' => 'Card doesnt exist'
            ], 400);
        }

        //reduce customer balance
        $customer = User::find($card->customer_id);

        if ( $customer == null )
        {
            return response()->json([
                'success' => false,
                'message' => 'Customer doesnt exist'
            ], 400);
        }

        return DB::Transaction(function() use ($card, $customer){

            $start_amount = $card->start_amount;
            $card_count = $card->card_count;
            $amount = $start_amount * $card_count;
            $card_bal = $card->card_balance;
            $card_wbal = $card->card_w_balance;



            if ( $amount <= $card_bal )
            {
                $card->card_balance = 0;
                $card->card_w_balance = 0;
                $card->card_count = 0;
            }else{
                $card->card_balance = $card_bal - $amount;
                $card->card_w_balance = $card_wbal - $amount;
                $card->card_count = $card_count - ($amount / $start_amount);
            }
            $card->save();

            //delete transaction lines
            $transHeaders = TransHeader::where('card_id', $card->id);
            $transHeaders->delete();

            //delete transaction lines
            $transLines = TransLine::where('card_id', $card->id);
            $transLines->delete();

            $cust_bal  = $customer->balance;
            $cust_wbal = $customer->w_balance;

            //delete amount from customer
            $customer->balance = $cust_bal - $amount;
            $customer->w_balance = ($cust_wbal - ($amount - $start_amount));

            $customer->save();

            return response()->json([
                'success' => true,
                'data' => $card,
                'message'=>'Card reset was successful'
            ], 200);
        });
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param \App\Models\Card $card
     * @return JsonResponse
     */
    public function update(int $card, Request $request)
    {
        $validator = Validator($request->all(), [
            'card_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->toArray()
            ], 400);
        }

        $card_no = 'ICN-' . date('ymdhi');

        $card = Card::find($card);

        if ($card == null) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found'
            ], 403);
        }

        $card->card_name = $request->card_name;
        $card->save();

        return response()->json([
            'success' => true,
            'data' => $card
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Card $card
     * @return JsonResponse
     */
    public function destroy(int $card)
    {
        $card = Card::find($card);

        if ($card == null) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found'
            ], 403);
        }

        $card->delete();

        return response()->json([
            'success' => true,
            'data' => $card
        ], 200);
    }

    public function get_handler_customer_cards()
    {
        $handler = Auth()->user();
        if ($handler == null)
        {
            return response()->json([
                'success' =>false,
                'message'=>'handler doesnt exist'
            ], 400);
        }
        $handler_id = $handler->id;

        $cards =DB::table('cards')
            ->join('users','users.id', '=', 'cards.customer_id')
            ->where('users.handler_id', $handler_id)
            ->select('cards.*')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $cards
        ], 200);

    }
}
