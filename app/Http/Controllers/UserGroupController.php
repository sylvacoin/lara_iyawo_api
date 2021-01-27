<?php

namespace App\Http\Controllers;

use App\Models\UserGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => UserGroup::all()
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {

        $validator = Validator($request->all(), [
            'user_group' => 'min:3|required',
            'account_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->toArray()
            ], 400);
        }

        if (isset($request->is_default) && $request->is_default== true)
        {
            UserGroup::where('is_default', 1)->update('is_default', 0);
        }

        $user_group = UserGroup::Create([
            'user_group' => $request->user_group,
            'user_group_menus' => $request->user_group_menus,
            'is_default' => $request->is_default,
            'can_edit' => 0,
            'account_type' => $request->account_type,
        ]);

        return response()->json([
            'success' => true,
            'data' => $user_group
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param int $user_group_id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $user_group_id, Request $request)
    {
        $validator = Validator($request->all(), [
            'user_group' => 'min:3|required',
            'account_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->toArray()
            ], 400);
        }

        $user_group = UserGroup::find($user_group_id);
        if ($user_group == null)
        {
            return response()->json([
                'success' => false,
                'message' => 'User group not found'
            ], 403);
        }

        if (isset($request->is_default) && $request->is_default== true)
        {
            UserGroup::where('is_default', 1)->update(['is_default'=> 0]);
        }

        $user_group->user_group = $request->user_group;
        $user_group->user_group_menus = $request->user_group_menus;
        $user_group->is_default = $request->is_default;
        $user_group->can_edit = $request->can_edit;
        $user_group->account_type = $request->account_type;
        $user_group->save();

        return response()->json([
            'success' => true,
            'data' => $user_group
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $user_group_id
     * @return JsonResponse
     */
    public function destroy(int $user_group_id)
    {
        $user_group = UserGroup::find($user_group_id);

        if ($user_group == null) {
            return response()->json([
                'success' => false,
                'message' => 'User group not found'
            ], 403);
        }

        $user_group->delete();

        return response()->json([
            'success' => true,
            'data' => $user_group
        ], 200);
    }

    public function set_default(int $user_group_id)
    {
        $user_group = UserGroup::find($user_group_id);

        if ($user_group == null) {
            return response()->json([
                'success' => false,
                'message' => 'User group not found'
            ], 403);
        }

        UserGroup::where('is_default', 1)->update(['is_default'=> 0]);
        UserGroup::where(['id' => $user_group_id])->update(['is_default'=> 1]);

        return response()->json([
            'success' => true,
            'data' => $user_group
        ], 200);
    }
}
