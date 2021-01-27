<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\TransHeader;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public $system_stats = array(
        'sys_ovr_bal' => 0,
        'sys_ovr_profit' => 0,
        'sys_ovr_total' => 0,
        'sys_handler_count' => 0,
        'sys_customer_count' => 0,
        'sys_withdrawal_count' => 0,
        'sys_pending_withdrawal' => 0
    );

    public $handler_stats = array(
        'ovr_bal' => 0,
        'ovr_profit' => 0,
        'ovr_total' => 0,
        'daily_profit' => 0,
        'daily_total' => 0,
        'monthly_profit' => 0,
        'monthly_total' => 0,
        'customer_count' => 0,
        'daily_withdrawal' => 0
    );

    public $customer_stats = array(
        'my_balance' => 0,
        'my_cards' => 0,
        'my_pending_withdrawals' => 0,
    );

    public function get_stat()
    {
        $uid = Auth::guard('sanctum')->user()->id;
        $user = User::find($uid);

        if ( $user == null )
        {

            return response()->json([
                'success' => false,
                'message' => 'User does not exist'
            ], 200);
        }

        //0 is system admin
        //1 is super admin
        //2 is admin
        //3 is agent / handler
        //4 is customer
        if ( $user->user_group_id == 1 || $user->user_group_id == 0 || $user->user_group_id == 2) {
            $system = $this->get_system_stats();
            $handler = $this->get_my_stats();

            return response()->json([
                'success' => true,
                'data' => array_merge($system, $handler)
            ], 200);
        }elseif ($user->user_group_id == 3 )
        {
            $handler = $this->get_my_stats();

            return response()->json([
                'success' => true,
                'data' => $handler
            ], 200);
        }elseif ($user->user_group_id == 4)
        {
            $customer = $this->get_customer_stats();

            return response()->json([
                'success' => true,
                'data' => $customer
            ], 200);
        }else{
            $customer = $this->get_customer_stats();

            return response()->json([
                'success' => true,
                'data' => $customer
            ], 200);
        }

    }

    //get system stats
    private function get_system_stats()
    {
        $transactions  = TransHeader::all();
        $customers = User::all();

        $all_customer_balance = $customers->where('user_group_id', 4)->sum('balance');
        $all_customer_withdrawables = $customers->where('user_group_id', 4)->sum('w_balance');

        $this->system_stats['sys_customer_count'] = $customers->where('user_group_id', 4)->count();
        $this->system_stats['sys_handler_count'] = $customers->where('user_group_id', 3)->count();
        $this->system_stats['sys_ovr_bal'] = $all_customer_withdrawables;
        $this->system_stats['sys_ovr_profit'] = $all_customer_balance - $all_customer_withdrawables;
        $this->system_stats['sys_ovr_total'] = $all_customer_balance;
        $this->system_stats['sys_withdrawal_count'] = $transactions->where('trans_type', '001')->where('trans_status', 'pending')->count();
        $this->system_stats['sys_pending_withdrawal'] = $transactions->where('trans_type', '001')->where('trans_status', 'pending')->sum('amount');
        return $this->system_stats;
    }

    //get admin stats
    private function get_my_stats()
    {
        $my_handler_id = Auth::guard('sanctum')->user()->id;


        $transaction = TransHeader::all();
        $customer = User::where('user_group_id', 4);


        $this->handler_stats['customer_count'] = $customer->where('handler_id', $my_handler_id)->count();
        $this->handler_stats['ovr_bal'] = $customer->where('handler_id', $my_handler_id)->sum('w_balance');
        $this->handler_stats['ovr_total'] = $customer->where('handler_id', $my_handler_id)->sum('balance');
        $this->handler_stats['ovr_profit'] = $this->handler_stats['ovr_total'] - $this->handler_stats['ovr_bal'];

        $today_cards = TransHeader::whereDate('trans_headers.created_at', '=', date('Y-m-d'))
            ->join('users', 'users.id', '=', 'customer_id')
            ->where('handler_id', $my_handler_id);
        $this->handler_stats['daily_total'] = $today_cards->sum('amount');

        $this->handler_stats['daily_withdrawal'] = TransHeader::whereDate('created_at', '=', date('Y-m-d'))
            ->where('trans_type','001')
            ->where('trans_status', 'completed')
            ->sum('amount');

        $month_cards = Card::whereMonth('cards.created_at', '=', date('m'))
            ->join('users', 'users.id', '=', 'cards.customer_id')
            ->where('handler_id', $my_handler_id);

        //dd($month_cards->get());
        $this->handler_stats['monthly_total'] = $month_cards->sum('card_balance');
        $this->handler_stats['monthly_profit'] = $this->handler_stats['monthly_total'] - $month_cards->sum('card_w_balance');


        $daily_trans = DB::table('trans_headers')
            ->selectRaw('sum(amount) as amount, DAYNAME(created_at) as label')
            ->whereRaw('created_at >= SUBDATE(CURRENT_DATE(), 10) AND created_at <= ADDDATE(CURRENT_DATE(), 1)')
        ->whereRaw('trans_by = ?', [$my_handler_id])
            ->groupByRaw('DAY(created_at)')->get();

//        $monthly_trans = DB::table('trans_headers')
//            ->selectRaw('sum(amount) as amount, MONTHNAME(created_at) as label')
//            ->whereRaw('trans_by = ?', [$my_handler_id])
//            ->groupByRaw('MONTHNAME(created_at)')->get();

        $monthly_trans = DB::table(DB::Raw("(SELECT '01' AS mname, 'January' as mtitle
          UNION ALL SELECT '02','Febrauary'
          UNION ALL SELECT '03','March'
          UNION ALL SELECT '04','April'
          UNION ALL SELECT '05','May'
          UNION ALL SELECT '06','June'
          UNION ALL SELECT '07','July'
          UNION ALL SELECT '08','August'
          UNION ALL SELECT '09','September'
          UNION ALL SELECT '10','October'
          UNION ALL SELECT '11','November'
          UNION ALL SELECT '12','December') const_month"))
            ->leftJoin('trans_headers','trans_headers.created_at','>=', "'2021-01-01' + INTERVAL ( const_month.mname - 1 ) Month ")
            ->selectRaw('const_month.mtitle AS `label`, IFNULL(sum(amount),0) AS `amount`')
            ->whereRaw('created_at >= SUBDATE(CURRENT_DATE(), 10) AND created_at <= CURRENT_DATE() AND trans_by = ? AND trans_type = ?', [$my_handler_id, '002'])
            ->groupByRaw('const_month.mname')->get();

        $this->handler_stats['chart'] = [
            $daily_trans,
            $monthly_trans,
        ];
       return $this->handler_stats;
    }

    //get customer stats
    public function get_customer_stats()
    {
        $user_id = Auth::id();

        $transactions  = TransHeader::where('customer_id', $user_id)->where('trans_status', 'pending')->count();
        $customers = User::find($user_id);
        $cards = Card::where('customer_id', $user_id)->get();

        $this->customer_stats['my_pending_withdrawals'] = $transactions;
        $this->customer_stats['my_balance'] = $customers->balance;
        $this->customer_stats['my_cards'] = $cards->count();

        return response()->json([
            'data' => $this->customer_stats
        ], 200);
    }
}
