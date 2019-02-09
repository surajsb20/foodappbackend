<?php

namespace App\Http\Controllers;

use App\Shop;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Route;
use Exception;
use Carbon\Carbon;
use App\Order;
use App\Transporter;
use App\OrderInvoice;
use App\OrderRating;
use App\TransporterShift;
use App\Usercart;
use App\Restuarant;

use Auth;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Order = new Order;
        $RecentOrders = $Order->where('shop_id', Auth::user()->id)->where('status', 'RECEIVED')->orderBy('id', 'Desc')->take(5)->get();
        $DeliveryOrders = $Order->where('shop_id', Auth::user()->id)->where('status', 'COMPLETED')->orderBy('id', 'Desc')->take(4)->get();
        $OrderReceivedToday = $Order->where('shop_id', Auth::user()->id)->where('status', 'RECEIVED')->where('created_at', '>=', Carbon::today())->count();
        $OrderDeliveredToday = $Order->where('shop_id', Auth::user()->id)->where('status', 'COMPLETED')->where('created_at', '>=', Carbon::today())->count();
        $OrderIncomeToday = OrderInvoice::withTrashed()->with('orders')
            ->whereHas('orders', function ($q) {
                $q->where('shop_id', Auth::user()->id);
                $q->where('orders.status', 'COMPLETED');
                $q->where('created_at', '>=', Carbon::today());
            })->sum('net');;
        $now = Carbon::now();
        $start = $now->startOfMonth();
        $end = $now->endOfMonth();
        $OrderIncomeMonthly = OrderInvoice::withTrashed()->with('orders')
            ->whereHas('orders', function ($q) use ($start, $end) {
                $q->where('shop_id', Auth::user()->id);
                $q->where('orders.status', 'COMPLETED');
                //$q->where('created_at', '>=', Carbon::now()->month);
                $q->whereBetween('created_at', [$start, $end]);
            })->sum('net');;
        $complete_cancel = \DB::select("SELECT t.`month`,(CASE WHEN t1.`shop_id`= " . Auth::user()->id . " THEN 1 ELSE NULL END ) as shop_id,SUM(CASE WHEN t1.`status` = 'COMPLETED' THEN 1 ELSE 0 END) AS `delivered`, SUM(CASE WHEN t1.`status` = 'CANCELLED' THEN 1 ELSE 0 END) AS `cancelled` FROM (SELECT DATE_FORMAT(NOW(),'%Y-01') AS `month` UNION SELECT DATE_FORMAT(NOW(),'%Y-02') AS `month` UNION SELECT DATE_FORMAT(NOW(),'%Y-03') AS `month` UNION SELECT DATE_FORMAT(NOW(),'%Y-04') AS `month` UNION SELECT DATE_FORMAT(NOW(),'%Y-05') AS `month` UNION SELECT DATE_FORMAT(NOW(),'%Y-06') AS `month` UNION SELECT DATE_FORMAT(NOW(),'%Y-07') AS `month` UNION SELECT DATE_FORMAT(NOW(),'%Y-08') AS `month` UNION SELECT DATE_FORMAT(NOW(),'%Y-09') AS `month` UNION SELECT DATE_FORMAT(NOW(),'%Y-10') AS `month` UNION SELECT DATE_FORMAT(NOW(),'%Y-11') AS `month` UNION SELECT DATE_FORMAT(NOW(),'%Y-12') AS `month` ) AS t LEFT JOIN orders t1 on(t.`month` =DATE_FORMAT(t1.`created_at`,'%Y-%m')) where (CASE WHEN t1.`shop_id`= " . Auth::user()->id . " THEN " . Auth::user()->id . " ELSE NULL END ) IS  NULL OR (CASE WHEN t1.`shop_id`= " . Auth::user()->id . " THEN " . Auth::user()->id . " ELSE NULL END ) = " . Auth::user()->id . "   group by shop_id,t.`month` ");

        return view('shop.home', compact('RecentOrders', 'DeliveryOrders', 'OrderReceivedToday', 'OrderDeliveredToday', 'OrderIncomeMonthly', 'OrderIncomeToday', 'Order', 'complete_cancel'));
    }


    public function register(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|max:255|unique:shops',
            'password' => 'required|min:6|confirmed',
            'phone' => 'required',
            'hours_opening' => 'required',
            'hours_closing' => 'required',
            'address' => 'required'

        ]);

        try {

            Shop::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
                'hours_opening' => $request->hours_opening,
                'hours_closing' => $request->hours_closing,
                'address' => $request->address

            ]);
            return back()->with('flash_success', trans('home.delivery_boy.created'));

        } catch (Exception $e) {

            dd($e);

            return back()->with('flash_error', trans('form.whoops'));
        }
    }


}
