<?php

namespace App\Http\Controllers\Admin;

use App\Plan;
use App\User;
use App\Theme;
use App\Gateway;
use App\Setting;
use App\Currency;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();
        $currency = Currency::where('iso_code', $config['1']->config_value)->first();
        $overall_income = Transaction::where('payment_status', 'Success')->sum('transaction_amount');
        $today_income = Transaction::where('payment_status', 'Success')->whereDate('created_at', Carbon::today())->sum('transaction_amount');
        $overall_users = User::where('role_id', 2)->where('status', 1)->count();
        $today_users = User::where('role_id', 2)->where('status', 1)->whereDate('created_at', Carbon::today())->count();
        $themes = Theme::where('status', 1)->count();
        $plans = Plan::where('status', 1)->count();
        $gateways = Gateway::where('status', 1)->count();

        return view('admin.home', compact('overall_income', 'today_income', 'overall_users', 'today_users', 'themes', 'plans', 'gateways', 'currency', 'settings'));
    }
}
