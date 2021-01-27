<?php

namespace App\Http\Controllers;

use App\Mail\NewCustomer;
use App\Mail\NewHandler;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Resources\UserCollection;
use App\Http\Resources\User as UserResource;

class UserController extends Controller
{

    /**
     * Gets all the list of users based on type either customer or handlers
     * @param string $type
     * @return UserCollection|\Illuminate\Http\JsonResponse
     */
    public function index(string $type = 'customer')
    {

        //dd($type);
        if ($type == 'admin')
        {
            $users = User::Where('user_group_id', 2)->get();
        }elseif ($type == 'handler'){
            $users = User::Where('user_group_id', 3)->get();
        }elseif ($type == 'all'){
            $users = User::whereIn('user_group_id', [2,3])->get();
        }else{
            $users = User::Where('user_group_id', 4)->get();
        }

        return new UserCollection($users);
//        return response()->json([
//            'success' => true,
//            'data' => $users
//        ], 200);
    }

    /**
     * Create either a customer or a user $type refers to the type either customer | handler
     *
     * @param String $type (customer | user)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(String $type, Request $request)
    {
        if (strtolower( $type ) == 'user')
        {
            return $this->create_user($request);
        }else{
            return $this->create_customer($request);
        }
    }

    /**
     * Creates a customer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function create_customer(Request $request)
    {
        $validator = Validator($request->all(), [
            'first_name' => 'min:3',
            'last_name' => 'min:3',
            'email' => 'required|email:rfc|unique:users,email',
            'gender' => 'required',
            'phone' => 'required|unique:users,phone',
        ]);

        $handler_id = Auth::guard('sanctum')->user()->id;

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->toArray()
            ], 400);
        }

        $password = Str::random(8);
        $oldCount = User::where('customer_no','<>', NULL)->count();
        $customerNo = 'IYW-'.date('Y') . ($oldCount + 1);

        $user = User::Create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'phone' => $request->phone,
            'gender' => $request->gender,
            'user_group_id' => 4, /* TODO: Set Default group id as group id */
            'customer_no' => $customerNo, /* TODO::Auto Increment Customer No Here */
            'handler_id' => $handler_id,
            'has_alert' => $request->has_alert
        ]);

        $link = url('customer/dashboard');

        //TODO: Add a mail to notify user that they have been created send password also.
        Mail::to($user->email)->send(new NewCustomer($user, $password, $link));

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);

    }

    /**
     * Creates a user. A user can be an admin or a handler based on user group selected.
     *
     * @param Request $request
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    private function create_user(Request $request)
    {
        $validator = Validator($request->all(), [
            'first_name' => 'min:3',
            'last_name' => 'min:3',
            'email' => 'required|email:rfc|unique:users,email',
            'gender' => 'required',
            'phone' => 'required|unique:users,phone',
            'user_group_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->toArray()
            ], 400);
        }

        $password = Str::random(8);

        $user = User::Create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'phone' => $request->phone,
            'gender' => $request->gender,
            'user_group_id' => $request->user_group_id
        ]);


        UserDetail::Create([
            'user_id' => $user->id,
            'nok_name' => $request->nok_name,
            'nok_phone' => $request->nok_phone,
            'shortee_name' => $request->shortee_name,
            'shortee_phone' => $request->shortee_phone,
            'address' => $request->address,
            'account_no'=> $request->account_no,
            'bank_name'=>$request->bank_name,
            'bvn'=>$request->bvn
        ]);


        $link = url('handler/dashboard');

        //TODO: Add a mail to notify user that they have been created send password also.
        Mail::to($user->email)->send(new NewHandler($user, $password, $link));

        return new UserResource(User::findOrFail($user->id));

    }

    /**
     * Gets a single user based on user id
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        $user = User::find($id);
        if ($user == null )
        {
            return response()->json([
                'success' =>false,
                'message'=>'Account not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }

    /**
     * Updates a specific users profile
     *
     * @param int $id
     * @param Request $request
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function edit(int $id, Request $request)
    {
        $user = User::find($id);

        if ($user == null )
        {
            return response()->json([
                'success' =>false,
                'message'=>'Account not found'
            ], 404);
        }

        if (!empty($request->first_name)) {
            $user->first_name = $request->first_name;
        }
        if (!empty($request->last_name)) {
            $user->last_name = $request->last_name;
        }
//        if (!empty($request->email)) {
//            $user->email    = $request->email;
//        }
        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }
//        if (!empty($request->phone)) {
//            $user->phone = $request->phone;
//        }
        if (!empty($request->gender)) {
            $user->gender = $request->gender;
        }
        if (isset($request->has_alert)) {
            $user->has_alert = $request->has_alert;
        }
        if (!empty($request->handler_id)) {
            $user->handler_id = $request->handler_id;
        }
        if (!empty($request->password) && !empty($request->old_password)) {
            if (Hash::check($user->password , $request->old_password))
            {
                return response()->json([
                    'success' =>false,
                    'message'=>'Old password does not match'
                ], 203);
            }
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $userdetail = UserDetail::where('user_id', $id)->first();
        if ($userdetail == null)
        {
            UserDetail::Create(['user_id'=>$id]);
            $userdetail = UserDetail::where('user_id', $id)->first();
        }

        $ud = $request->user_details;
        //Fill User Details
        if (!empty($ud['nok_name'])) {
            $userdetail->nok_name = $ud['nok_name'];
        }
        if (!empty($ud['nok_phone'])) {
            $userdetail->nok_phone = $ud['nok_phone'];
        }
        if (!empty($ud['shortee_name'])) {
            $userdetail->shortee_name = $ud['shortee_name'];
        }
        if (!empty($ud['shortee_phone'])) {
            $userdetail->shortee_phone = $ud['shortee_phone'];
        }
        if (!empty($ud['shortee_phone'])) {
            $userdetail->shortee_phone = $ud['shortee_phone'];
        }
        if (!empty($ud['address'])) {
            $userdetail->address = $ud['address'];
        }
        if (!empty($ud['account_no'])) {
            $userdetail->account_no = $ud['account_no'];
        }
        if (!empty($ud['bank_name'])) {
            $userdetail->bank_name = $ud['bank_name'];
        }
        if (!empty($ud['bvn'])) {
            $userdetail->bvn = $ud['bvn'];
        }

        $userdetail->save();

        return new UserResource(User::findOrFail($id));

    }

    /**
     * Bars a user
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function bar(int $id)
    {
        $user = User::find($id);
        if ($user == null )
        {
            return response()->json([
                'success' =>false,
                'message'=>'Account not found'
            ], 404);
        }

        $user->is_flagged = true;

        $user->save();

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }

    /**
     * Removes bar from a barred user
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function unbar(int $id)
    {
        $user = User::find($id);
        if ($user == null )
        {
            return response()->json([
                'success' =>false,
                'message'=>'Account not found'
            ], 404);
        }

        $user->is_flagged = false;

        $user->save();

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }

    /**
     * Deletes an already flagged user using the user id
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        $user = User::find($id);
        if ($user == null )
        {
            return response()->json([
                'success' =>false,
                'message'=>'Account not found'
            ], 404);
        }

        $user->is_flagged = false;

        $user->delete();

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }

    /**
     * Gets a handler's customer
     * @param int $handler_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler_customers(int $handler_id = 0)
    {
        if ($handler_id == 0)
        {
            $handler_id = Auth('sanctum')->user()->id;
        }

        //users with handler_id not null are customers
        $handler = User::where('handler_id', NULL)->find($handler_id);
        if ($handler == null )
        {
            return response()->json([
                'success' =>false,
                'message'=> 'Handler doesnt exist'
            ], 403);
        }
        $customers = User::where('handler_id', $handler_id)->get();
        return response()->json([
            'success' => true,
            'data' => array(
                'profile'=>$handler,
                'customers'=>$customers
            )
        ], 200);
    }
}
