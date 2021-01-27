<?php

namespace App\Http\Controllers;

use App\Http\Resources\CardCollection;
use App\Http\Resources\TransactionCollection;
use App\Http\Resources\Transaction;
use App\Models\Card;
use App\Models\TransHeader;
use App\Models\TransLine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransHeaderController extends Controller
{
    /**
     * Display a list of all transactions for a currently logged Agent .
     *
     * @return TransactionCollection|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $handler_id = Auth::guard('sanctum')->user()->id;

        if ($handler_id == null ){
            return response()->json([
                'success' => false,
                'message' => 'not authenticated'
            ], 401);
        }

        $customer = User::find($handler_id);
        if ($customer == null) {
            return response()->json([
                'success' => false,
                'message' => 'Customer dont exist'
            ], 403);
        }

        $transactions = TransHeader::where('trans_by', $handler_id)->get();

        return new TransactionCollection($transactions);
    }

    /**
     * Display a list of all transactions.
     *
     * @return TransactionCollection
     */
    public function all_transactions()
    {
        return new TransactionCollection(TransHeader::all());
    }

    /**
     * Display a list of all transactions for a particular customer id.
     *
     * @return CardCollection|\Illuminate\Http\JsonResponse
     */
    public function customer_transactions(int $customer_id)
    {
        $customer = User::find($customer_id);
        if ($customer == null) {
            return response()->json([
                'success' => false,
                'message' => 'Customer dont exist'
            ], 403);
        }

        $transactions = TransHeader::where('customer_id', $customer_id)->get();
        return new CardCollection($transactions);
    }

    public function cards(int $card_id)
    {
        $card = Card::find($card_id);
        if ($card == null) {
            return response()->json([
                'success' => false,
                'message' => 'Card dont exist'
            ], 403);
        }

        $transactions = TransHeader::where('card_id', $card_id)->get();
        return new CardCollection($transactions);
//        return response()->json([
//            'success' => true,
//            'data' => $transactions
//        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $card_id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
//    public function store_deposit(int $card_id, Request $request)
//    {
//        //001 withdrawal
//        //002 deposit
//
//        $validator = Validator($request->all(), [
//            'customer_id' => 'required|numeric',
//            'trans_type' => 'required',
//            'amount' => 'required|numeric',
//            'no_days' => ($request->trans_type == 001 ? '' : 'required|numeric'),
//        ]);
//
//        $handler_id = Auth::guard('sanctum')->user()->id;
//        $totalDays = 0;
//
//        //validate if required fields are provided
//        if ($validator->fails()) {
//            return response()->json([
//                'success' => false,
//                'message' => $validator->errors()->toArray()
//            ], 400);
//        }
//
//        //check if cards exists.
//        $card = Card::find($card_id);
//
//        if ($card == null) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Card does not exist'
//            ], 400);
//        }
//
//
//        if ($request->trans_type == 002) {
//            //check if proper amount was given
//            if ($card->start_amount != ($request->amount / $request->no_days)) {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Amount must be a multiple of card initial amount'
//                ], 400);
//            }
//
//            //check if total no of days exceeds 31 days
//            $totalDays = $card->card_count + $request->no_days;
//            if ($totalDays > 31) {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Total days exceeds 31.'
//                ], 400);
//            }
//
//            if ($request->amount == 0 || $request->no_days == 0) {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Amount or number of days must be greater than 0.'
//                ], 400);
//            }
//
//        }
//
//
//        $customer = User::find($request->customer_id);
//        if ($customer == null) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Customer doesnt exist.'
//            ], 400);
//        }
//
//        if ($request->trans_type == 001 && $request->amount > $customer->w_balance) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Insufficient balance.'
//            ], 400);
//        }
//
//
//        $tHeader = [
//            'customer_id' => $request->customer_id,
//            'trans_type' => $request->trans_type,
//            'amount' => $request->amount,
//            'trans_by' => $handler_id,
//            'no_days' => $request->no_days,
//            'trans_status' => ($request->trans_type == 002 ? 'completed' : 'pending'),
//            'card_id' => ($request->trans_type == 002 ? $card_id : NULL)
//        ];
//
//        $response = DB::transaction(function () use ($request, $card_id, $tHeader, $card, $totalDays, $customer) {
//
//            $transHeader = TransHeader::create($tHeader);
//
//            $transLines = [
//                'trans_header_id' => $transHeader->id,
//                'card_id' => $card_id,
//                'amount' => $request->amount
//            ];
//
//            $amt_per_day = 0;
//            if ($request->trans_type == 002) {
//                $amt_per_day = $request->amount / $request->no_days;
//                $transLine = [
//                    'trans_header_id' => $transHeader->id,
//                    'card_id' => $card_id,
//                    'amount' => $amt_per_day,
//                    'created_at' => Carbon::now(),
//                    'updated_at' => Carbon::now()
//                ];
//
//                $transLines = array_fill(0, $request->no_days, $transLine);
//
//            }
//            //dd($transLines);
//            TransLine::insert($transLines);
//
//            if ($request->trans_type == 002) {
//                //update customer balance
//                $w_balance = ($card->card_w_balance == 0 ? $customer->w_balance + ($request->amount - $amt_per_day) : ($customer->w_balance + $request->amount));
//                $customer->balance = $customer->balance + $request->amount;
//                $customer->w_balance = $w_balance;
//                $customer->save();
//
//                //Update Card Count
//                $card_balance = ($card->card_balance + $request->amount);
//                $card_w_balance = $card_balance - $amt_per_day;
//
//                $card->card_count = $totalDays;
//                $card->card_balance = $card_balance;
//                $card->card_w_balance = $card_w_balance;
//                $card->is_active = $totalDays == 31 ? false : true;
//                $card->save();
//
//            }
//
//            return $transHeader;
//
//        }, 3);
//
//        //dd($response);
//        if ($response == null) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Failed to insert data'
//            ], 403);
//        }
//        return response()->json([
//            'success' => true,
//            'data' => $card
//        ], 200);
//    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $card_id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_deposit(int $card_id, Request $request)
    {
        //001 withdrawal
        //002 deposit

        $validator = Validator($request->all(), [
            'customer_id' => 'required|numeric',
            'trans_type' => 'required',
            'amount' => 'required|numeric',
            'no_days' => 'required|numeric',
        ]);

        $handler_id = Auth::guard('sanctum')->user()->id;
        $totalDays = 0;

        //validate if required fields are provided
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->toArray()
            ], 400);
        }

        //check if cards exists.
        $card = Card::find($card_id);

        if ($card == null) {
            return response()->json([
                'success' => false,
                'message' => 'Card does not exist'
            ], 400);
        }


        if ($request->trans_type == 002) {
            //check if proper amount was given
            if ($card->start_amount != ($request->amount / $request->no_days)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount must be a multiple of card initial amount'
                ], 400);
            }

            //check if total no of days exceeds 31 days
            $totalDays = $card->card_count + $request->no_days;
            if ($totalDays > 31) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total days exceeds 31.'
                ], 400);
            }

            if ($request->amount == 0 || $request->no_days == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount or number of days must be greater than 0.'
                ], 400);
            }

        }

        if($request->customer_id != $card->customer_id)
        {
            return response()->json([
                'success' => false,
                'message' => 'The selected customer is not owner of card'
            ], 400);
        }


        $customer = User::find($request->customer_id);
        if ($customer == null) {
            return response()->json([
                'success' => false,
                'message' => 'Customer doesnt exist.'
            ], 400);
        }

        $tHeader = [
            'customer_id' => $request->customer_id,
            'trans_type' => $request->trans_type,
            'amount' => $request->amount,
            'trans_by' => $handler_id,
            'no_days' => $request->no_days,
            'trans_status' => ($request->trans_type == 002 ? 'completed' : 'pending'),
            'card_id' => ($request->trans_type == 002 ? $card_id : NULL)
        ];

        $response = DB::transaction(function () use ($request, $card_id, $tHeader, $card, $totalDays, $customer) {

            $transHeader = TransHeader::create($tHeader);

            $transLines = [
                'trans_header_id' => $transHeader->id,
                'card_id' => $card_id,
                'amount' => $request->amount
            ];

            $amt_per_day = 0;
            if ($request->trans_type == 002) {
                $amt_per_day = $request->amount / $request->no_days;
                $transLine = [
                    'trans_header_id' => $transHeader->id,
                    'card_id' => $card_id,
                    'amount' => $amt_per_day,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];

                $transLines = array_fill(0, $request->no_days, $transLine);

            }
            //dd($transLines);
            TransLine::insert($transLines);

            if ($request->trans_type == 002) {
                //update customer balance
                $w_balance = ($card->card_balance == 0 ? $customer->w_balance + ($request->amount - $amt_per_day) : ($customer->w_balance + $request->amount));
                $customer->balance = $customer->balance + $request->amount;
                $customer->w_balance = $w_balance;
                $customer->save();

                //Update Card Count
                $card_balance = ($card->card_balance + $request->amount);
                $card_w_balance = $card_balance - $amt_per_day;

                $card->card_count = $totalDays;
                $card->card_balance = $card_balance;
                $card->card_w_balance = $card_w_balance;
                $card->is_active = $totalDays == 31 ? false : true;
                $card->save();

            }

            return $transHeader;

        }, 3);

        //dd($response);
        if ($response == null) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to insert data'
            ], 403);
        }
        return response()->json([
            'success' => true,
            'data' => $card
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_withdrawal(Request $request)
    {
        //001 withdrawal
        //002 deposit

        $validator = Validator($request->all(), [
            'customer_id' => 'required|numeric',
            'trans_type' => 'required',
            'amount' => 'required|numeric',
        ]);

        $handler_id = Auth::guard('sanctum')->user()->id;

        //validate if required fields are provided
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->toArray()
            ], 400);
        }
        $tHeader = [];

        $response = DB::transaction(function () use ($request, $handler_id) {
            //check if user exists
            $customer = User::find($request->customer_id);
            if ($customer == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer doesnt exist.'
                ], 400);
            }

            if ($request->amount > $customer->w_balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance.'
                ], 400);
            }


            $tHeader = [
                'customer_id' => $request->customer_id,
                'trans_type' => '001',
                'amount' => $request->amount,
                'trans_by' => $handler_id,
                'no_days' => 0,
                'trans_status' => 'pending',
                'card_id' => NULL
            ];

            //get transaction header
            $transHeader = TransHeader::create($tHeader);

            if ($transHeader == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to insert data'
                ], 403);
            }

            //update balance to p_balance.
            $customer = User::find($request->customer_id);
            if ($customer == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer doesnt exist'
                ], 400);
            }

            $customer->balance = $customer->balance - $transHeader->amount;
            $customer->w_balance = $customer->w_balance - $transHeader->amount;
            $customer->p_balance = $customer->p_balance + $request->amount;

            $customer->save();

        }, 3);

        if ($response == null )
        {
            return response()->json([
                'success' => false,
                'message' => 'An error occured account not debited'
            ], 403);
        }
        return response()->json([
            'success' => true,
            'data' => $tHeader
        ], 200);


    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\TransHeader $transHeader
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $transHeaderId)
    {
        $transHeader = TransHeader::find($transHeaderId);
        if ($transHeader == null) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction doesnt exist'
            ], 400);
        }


        $transLines = TransLine::where('trans_header_id', $transHeaderId)->get();
        $transHeader->transLines = $transLines;

        return response()->json([
            'success' => true,
            'data' => $transHeader
        ], 200);
    }

    /**
     * Get Pending withdrawal requests
     *
     * @param \App\Models\TransHeader $transHeader
     * @return TransactionCollection|\Illuminate\Http\JsonResponse
     */
    public function show_pending_withdrawals()
    {
        $transHeader = TransHeader::where('trans_status', 'pending')->where('trans_type', '001')->get();
        if ($transHeader == null) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction doesnt exist'
            ], 400);
        }

        return new TransactionCollection($transHeader);
    }

    /**
     * Get Pending withdrawal requests
     *
     * @param \App\Models\TransHeader $transHeader
     * @return TransactionCollection|\Illuminate\Http\JsonResponse
     */
    public function my_pending_withdrawals()
    {
        $handler_id = Auth::guard('sanctum')->user()->id;
        if ($handler_id == null )
        {
            return response()->json([
                'success' => false,
                'message' => 'Handler doesnt exist'
            ], 400);
        }
        $transHeader = TransHeader::where('trans_status', 'pending')
            ->where('trans_type', '001')
            ->where('trans_by', $handler_id)
            ->get();

        if ($transHeader == null) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction doesnt exist'
            ], 400);
        }

        return new TransactionCollection($transHeader);
    }

    /**
     * Update pending withdrawal transaction to completed
     *
     * @param \App\Models\TransHeader $transHeader
     * @return \Illuminate\Http\JsonResponse
     */
    public function mark_as_completed(int $trans_header_id)
    {
        $transHeader = TransHeader::find($trans_header_id);
        if ($transHeader == null) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction doesnt exist'
            ], 400);
        }

        $customer = User::find($transHeader->customer_id);
        if ($customer == null) {
            return response()->json([
                'success' => false,
                'message' => 'Customer doesnt exist.'
            ], 400);
        }
        return DB::transaction(function() use ($customer, $transHeader){
            $customer->p_balance = $customer->p_balance - $transHeader->amount;
            $customer->save();

            $transHeader->trans_status = 'completed';
            $transHeader->save();

            return response()->json([
                'success' => true,
                'data' => $transHeader
            ], 200);
        });
    }

    /**
     * Deletes a particular transaction from the db
     *
     * @param \App\Models\TransHeader $transHeader
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $transHeaderId)
    {
        //get transaction header
        $transHeader = TransHeader::find($transHeaderId);
        if ($transHeader == null) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction doesnt exist'
            ], 400);
        }

        //reduce customer balance
        $customer = User::find($transHeader->customer_id);

        if ( $customer == null )
        {
            return response()->json([
                'success' => false,
                'message' => 'Customer doesnt exist'
            ], 400);
        }

        $card_id = $transHeader->card_id;
        $card = Card::find($card_id);

        if ( $card == null )
        {
            return response()->json([
                'success' => false,
                'message' => 'Card doesnt exist'
            ], 400);
        }
        return DB::Transaction(function() use ($transHeader, $card, $customer){




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
            $transLines = TransLine::where('trans_header_id', $transHeader->id);
            $transLines->delete();

            $cust_bal  = $customer->balance;
            $cust_wbal = $customer->w_balance;

            //delete amount from customer
            $customer->balance = $cust_bal - $amount;
            $customer->w_balance = ($cust_wbal - ($amount - $start_amount));

            $customer->save();

            $transHeader->delete();


            return response()->json([
                'success' => true,
                'data' => $card,
                'message'=>'Card reset was successful'
            ], 200);
        });
    }

}
