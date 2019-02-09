<?php

namespace App\Http\Controllers\UserResource;

use App\Card;
use App\CartAddon;
use App\Http\Controllers\BamboraController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SendPushNotification;
use App\Order;
use App\OrderInvoice;
use App\OrderRating;
use App\OrderTiming;
use App\Product;
use App\ProductPrice;
use App\Promocode;
use App\Settings;
use App\Shop;
use App\UserAddress;
use App\UserCart;
use App\WalletPassbook;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Setting;

class OrderResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $Order = Order::orderBy('id', 'DESC');;
            $Order->where('orders.user_id', $request->user()->id);
            if ($request->type == 'today') {

                $Order->where('created_at', '>=', Carbon::today());

            } elseif ($request->type == 'weekly') {

                $Order->where('created_at', '>=', Carbon::now()->weekOfYear);

            } elseif ($request->type == 'monthly') {

                $Order->where('created_at', '>=', Carbon::now()->month);
            }
            $Orders = $Order->get();

            if ($request->ajax()) {
                return $Orders;
            }

            if ($request->segment(1) == 'payments') {
                return view('user.orders.payments', compact('Orders'));
            } else {

//                dd($Orders);

                return view('user.orders.index', compact('Orders'));
            }
        } catch (Exception $e) {
            return response()->json(['error' => trans('form.whoops')], 500);
        }

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function orderprogress(Request $request)
    {
        try {
            $Orders = Order::where('user_id', $request->user()->id)->orderBy('updated_at', 'DESC')->progress();
            if ($request->ajax()) {
                return $Orders;
            }
            return view('user.home', compact('Orders'));
        } catch (Exception $e) {
            return response()->json(['error' => trans('form.whoops')], 500);
        }

    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function reorder(Request $request)
    {
        $this->validate($request, [
            'order_id' => 'required'
        ]);
        try {
            $order = Order::findOrFail($request->order_id);
            (new CartResource)->clearCart($request);
            $usercart = UserCart::with('cart_addons')->withTrashed()->where('order_id', $request->order_id)->get();
            foreach ($usercart as $item) {
                $request->request->add(['product_id' => $item->product_id]);
                $request->request->add(['quantity' => $item->quantity]);
                $CartProduct = UserCart::create([
                    'user_id' => $request->user()->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'note' => $item->note
                ]);
                if (count($item->cart_addons) > 0) {
                    foreach ($item->cart_addons as $key => $value) {
                        CartAddon::create([
                            'addon_product_id' => $value->addon_product_id,
                            'user_cart_id' => $CartProduct->id,
                            'quantity' => $value->quantity,
                        ]);
                    }
                }
            }
            if ($request->has('user_address_id')) {
                if ((new CartResource)->index($request)) {
                    return redirect('restaurant/details?name=' . $order->shop->name . '&myaddress=home')->with('flash_success', trans('order.reorder_created'));
                }
            } else {
                return (new CartResource)->index($request);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('order.invalid')], 422);
        } catch (Exception $e) {
            return response()->json(['error' => trans('form.whoops')], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

//        $request->pickup = 1;

        try {

            $this->validate($request, [
                'user_address_id' => 'required|exists:user_addresses,id,deleted_at,NULL',
                //'payment_mode' => 'required'
            ]);

            $User = $request->user()->id;
            $CartItems = UserCart::with('cart_addons')->where('user_id', $User)->get();
            $payment_status = 'pending';
            $tot_qty = 0;
            $tot_price = 0;
            $tax = 0;
            $discount = 0;
            $net = 0;
            $total_pay_user = 0;
            $ripple_price = 0;

            if (!$CartItems->isEmpty()) {
                try {

                    // Shop finding logic goes here.
                    $Shop_id = Product::findOrFail($CartItems[0]->product_id)->shop_id;

                    $Useraddress = UserAddress::findOrFail($request->user_address_id);
                    $longitude = $Useraddress->longitude;
                    $latitude = $Useraddress->latitude;
                    $distance = Setting::get('search_distance');
                    if (Setting::get('search_distance') > 0) {
                        $Shop = Shop::select('shops.*')
                            ->selectRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance")
                            ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                            ->where('status', 'active')->findOrFail($Shop_id);
                    } else {
                        $Shop = Shop::findOrFail($Shop_id);
                    }

                    //  for calculating total amount
                    // this is repeated code because of validation
                    foreach ($CartItems as $Product) {
                        $tot_qty = $Product->quantity;
                        $tot_price += $Product->quantity * ProductPrice::where('product_id', $Product->product_id)->first()->price;
                        $tot_price_addons = 0;
                        if (count($Product->cart_addons) > 0) {
                            foreach ($Product->cart_addons as $Cartaddon) {

                                $tot_price_addons += $Cartaddon->quantity * $Cartaddon->addon_product->price;
                            }
                        }
                        $tot_price += $tot_qty * $tot_price_addons;
                        if ($Product->promocode_id) {
                            $discount += $discount;
                        }

                        if ($request->has('promocode_id')) {
                            $find_promo = Promocode::find($request->promocode_id);
                            $discount += $find_promo->discount;
                        }

                        //$Product->order_id = $Order->id;
                        /*$Product->save();
                        $Product->delete();*/

                    }

                    $deliveryCharge = Setting::get('delivery_charge', 3);
                    $baseDistance = Setting::get('base_delivery_km', 3);

                    if ($request->pickup == 0) {

                        $totalDistance = $this->calculate_distance($latitude, $longitude, $Shop->latitude, $Shop->longitude);

                        if ($totalDistance != 'error') {
                            if ($totalDistance > $baseDistance) {
                                $deliveryCharge = (($totalDistance - $baseDistance) * Setting::get('after_base_charges', 1)) + Setting::get('delivery_charge', 3);
                            } else {
                                $deliveryCharge = Setting::get('delivery_charge', 3);
                            }
                        }

                    } else {
                        $totalDistance = 0;
                        $deliveryCharge = 0;
                    }

//                    return response()->json([
//                        'base_distance' => $baseDistance,
//                        'base_delivery_charge' => Setting::get('delivery_charge', 3),
//                        'total_distance' => $totalDistance,
//                        'after_delivery_charge' => Setting::get('after_base_charges', 1),
//                        'delivery_charge' => $deliveryCharge,
//                    ]);

                    $net = $tot_price;
                    if ($Shop->offer_percent) {
                        if ($tot_price > $Shop->offer_min_amount) {
                            //$discount = roundPrice(($tot_price*($Order->shop->offer_percent/100)));
                            $discount = ($tot_price * ($Shop->offer_percent / 100));
                            //if()
                            $net = $tot_price - $discount;
                        }
                    }
                    $total_wallet_balance = 0;
                    $tax = ($net * Setting::get('tax') / 100);

                    if ($request->pickup == 1) {
                        $total_net = $net + $tax;
                    } else {
                        $total_net = $net + $tax + $deliveryCharge;
                    }

                    if ($request->has('tip')) {
                        $total_net = $total_net + $request->tip;
                    }


                    $payable = $total_net;

                    if ($request->wallet) {
                        if ($request->user()->wallet_balance > $total_net) {
                            $total_wallet_balance_left = $request->user()->wallet_balance - $total_net;
                            $request->user()->wallet_balance = $total_wallet_balance_left;
                            $request->user()->save();
                            $total_wallet_balance = $total_net;
                            $payable = 0;
                            $payment_status = 'success';
                            $request->payment_mode = 'wallet';
                            WalletPassbook::create([
                                'user_id' => $request->user()->id,
                                'amount' => $total_wallet_balance,
                                'status' => 'DEBITED',
                                'message' => trans('form.invoice.message', ['price' => $total_wallet_balance, 'order_id' => ''])
                            ]);
                        } else {
                            //$total_net = $total_net - $request->user()->wallet_balance;
                            $total_wallet_balance = $request->user()->wallet_balance;
                            if ($total_wallet_balance > 0) {
                                $request->user()->wallet_balance = 0;
                                $request->user()->save();
                                $payable = ($total_net - $total_wallet_balance);
                                WalletPassbook::create([
                                    'user_id' => $request->user()->id,
                                    'amount' => $request->user()->wallet_balance,
                                    'status' => 'DEBITED',
                                    'message' => trans('form.invoice.message', ['price' => $request->user()->wallet_balance, 'order_id' => ''])
                                ]);

                            }
                        }
                    }

                } catch (ModelNotFoundException $e) {

                    if ($request->ajax()) {
                        return response()->json(['message' => trans('order.address_out_of_range')], 422);
                    }
                    return back()->with('flash_failure', trans('order.address_out_of_range'));

                } catch (Exception $e) {

                    dd($e);

                    return response()->json(['message' => trans('order.order_shop_not_found')], 404);
                }

                try {
                    if ($request->has('payment_mode')) {
                        if ($request->payment_mode == 'stripe') {
                            if ($request->card_id) {
                                $Card = Card::where('user_id', Auth::user()->id)->where('id', $request->card_id)->firstorFail();
                            } else {
                                $Card = Card::where('user_id', Auth::user()->id)->where('is_default', 1)->firstorFail();
                            }
                        }
                        if ($request->payment_mode == 'bambora') {
                            if ($request->card_id) {
                                $Card = Card::where('user_id', Auth::user()->id)->where('id', $request->card_id)->firstorFail();
                            } else {
                                $Card = Card::where('user_id', Auth::user()->id)->where('is_default', 1)->firstorFail();
                            }
                        }
                        if ($request->payment_mode == 'braintree') {
                            //if($request->payment_card!='PayPalAccount' || $request->payment_card!='CreditCard'){
                            if (!$request->has('payment_card')) {

                                if ($request->has('card_id')) {
                                    $Card = Card::where('user_id', Auth::user()->id)->where('id', $request->card_id)->firstorFail();
                                } else {
                                    $Card = Card::where('user_id', Auth::user()->id)->where('is_default', 1)->firstorFail();
                                }
                            }
                        }
                    }
                } catch (ModelNotFoundException $e) {

//                    dd($e);

                    if ($request->ajax()) {
                        return response()->json(['error' => trans('order.card.no_card_exist')], 422);
                    }
                    return back()->with('flash_failure', trans('order.card.no_card_exist'));
                }

                try {
                    $payment_id = 0;
                    if ($request->has('payment_mode')) {

                        if ($request->payment_mode == 'bambora') {

                            $request->payable = $payable;

                            if ($payable != 0) {

                                $payment = (new BamboraController())->makePayment($request);

                                if (isset($payment['order_number'])) {
                                    $payment_id = $payment['order_number'];
                                    $payment_status = 'success';
                                    $total_pay_user = $payable;
                                } else {
                                    if ($request->ajax()) {
                                        return response()->json(['error' => trans('order.payment.failed')], 422);
                                    } else {
                                        return back()->with('flash_error', $payment);
                                    }
                                }
                            }
                        }

                        if ($request->payment_mode == 'stripe') {
                            $request->payable = $payable;
                            if ($payable != 0) {
                                $payment = (new PaymentController)->payment($request);
                                if (isset($payment['id'])) {
                                    $payment_id = $payment['id'];
                                    $payment_status = 'success';
                                    $total_pay_user = $payable;
                                } else {
                                    if ($request->ajax()) {
                                        return response()->json(['error' => trans('order.payment.failed')], 422);
                                    } else {
                                        return back()->with('flash_error', trans('order.payment.failed'));
                                    }
                                }
                            }
                        }

                        if ($request->payment_mode == 'braintree') {
                            $request->payable = $payable;
                            if ($payable != 0) {
                                $payment = (new PaymentController)->payment($request);
                                if (isset($payment->id)) {
                                    $payment_id = $payment->id;
                                    $payment_status = 'success';
                                    $total_pay_user = $payable;
                                } else {
                                    if ($request->ajax()) {
                                        return response()->json(['error' => trans('order.payment.failed')], 422);
                                    } else {
                                        return back()->with('flash_error', trans('order.payment.failed'));
                                    }
                                }
                            }

                        }
                        if ($request->payment_mode == 'ripple') {
                            $request->payable = $payable;
                            if ($payable != 0) {

                                if (isset($request->payment_id)) {
                                    $payment_id = $request->payment_id;
                                    $payment_status = 'success';
                                    $total_pay_user = $payable;
                                    $ripple_price = $request->amount;
                                } else {
                                    if ($request->ajax()) {
                                        return response()->json(['error' => trans('order.payment.failed')], 422);
                                    } else {
                                        return back()->with('flash_error', trans('order.payment.failed'));
                                    }
                                }
                            }

                            //exit;
                        }

                        if ($request->payment_mode == 'eather') {
                            $request->payable = $payable;
                            if ($payable != 0) {

                                if (isset($request->payment_id)) {
                                    $payment_id = $request->payment_id;
                                    $payment_status = 'success';
                                    $total_pay_user = $payable;
                                    $ripple_price = $request->amount;
                                } else {
                                    if ($request->ajax()) {
                                        return response()->json(['error' => trans('order.payment.failed')], 422);
                                    } else {
                                        return back()->with('flash_error', trans('order.payment.failed'));
                                    }
                                }
                            }

                            exit;
                        }
                    }
                } catch (Exception $e) {

                    if ($request->ajax()) {
                        return response()->json(['message' => trans('form.whoops')], 422);
                    }
                    return back()->with('flash_failure', trans('form.whoops'));
                }


                try {
                    $details = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $Shop->latitude . "," . $Shop->longitude . "&destination=" . $Useraddress->latitude . "," . $Useraddress->longitude . "&mode=driving&key=" . env('GOOGLE_MAP_KEY');
                    $json = curl($details);
                    $details = json_decode($json, TRUE);
                    if (count($details['routes']) > 0) {
                        $route_key = $details['routes'][0]['overview_polyline']['points'];
                    } else {
                        $route_key = '';
                    }

                    if ($request->has('delivery_date')) {

                        $delivery_date = $request->delivery_date;
                        if (Carbon::parse($delivery_date)->format('Y-m-d') . ' 00:00:00' == Carbon::today()) {
                            $schedule_status = 0;
                        } else {
                            $schedule_status = 1;
                        }

                    } else {

                        $delivery_date = date('Y-m-d H:i');
                        $schedule_status = 0;
                    }
                    $newotp = rand(100000, 999999);
                    $Order = Order::create([
                        'invoice_id' => Uuid::uuid4()->toString(),
                        'user_id' => $User,
                        'shop_id' => $Shop->id,
                        'user_address_id' => $request->user_address_id,
                        'route_key' => $route_key,
                        'note' => $request->note,
                        'schedule_status' => $schedule_status,
                        'delivery_date' => $delivery_date,
                        'order_otp' => $newotp
                    ]);
                } catch (ModelNotFoundException $e) {
                    if ($request->ajax()) {
                        return response()->json(['error' => trans('order.card.no_card_exist')], 422);
                    }
                    return back()->with('flash_failure', trans('order.card.no_card_exist'));
                } catch (Exception $e) {

//                    dd($e);

                    if ($request->ajax()) {
                        return response()->json(['error' => trans('order.not_created')], 422);
                    }
                    return back()->with('flash_failure', trans('order.not_created'));
                }

                try {

                    if ($Order->id && $tot_qty) {

                        $Order_invoice = OrderInvoice::create([
                            'order_id' => $Order->id,
                            'quantity' => $tot_qty,
                            'gross' => $tot_price,
                            'discount' => $discount,
                            'wallet_amount' => $total_wallet_balance,
                            'tax' => $tax,
                            'net' => $total_net,
                            'payable' => $payable,
                            'paid' => ($payment_status == 'success') ? 1 : 0,
                            'status' => $payment_status,
                            'payment_id' => $payment_id,
                            'total_pay' => $total_pay_user,
                            'ripple_price' => $ripple_price,
                            'payment_mode' => $request->payment_mode ? $request->payment_mode : 'cash'
                        ]);

                        if ($request->pickup == 1) {
                            $Order_invoice->delivery_charge = 0;
                        } else {
                            $Order_invoice->delivery_charge = $deliveryCharge;
                        }

                        $Order_invoice->save();

                        //site_sendmail($Order);
                    } else {
                        $Order->delete();
                    }

                } catch (ModelNotFoundException $e) {
                    if ($request->ajax()) {
                        return response()->json(['error' => trans('order.invoice_not_created')], 422);
                    }
                    return back()->with('flash_failure', trans('order.invoice_not_created'));
                } catch (Exception $e) {

//                    dd($e);

                    if ($request->ajax()) {
                        return response()->json(['error' => trans('order.not_created')], 422);
                    }
                    return back()->with('flash_failure', trans('order.not_created'));
                }

                // todo add tip
                // check for pickup
                if ($request->has('tip')) {
                    $Order->tip = $request->tip;
                    $Order->save();
                }

                if ($request->pickup == 1) {
                    $Order->pickup = 1;
                    $Order->save();
                }

                $Order->total_distance = $totalDistance;
                $Order->save();

                OrderTiming::create([
                    'order_id' => $Order->id,
                    'status' => 'ORDERED'
                ]);

//                dd($deliveryCharge);

                $push_message = trans('order.order_created', ['id' => $Order->id]);
                (new SendPushNotification)->sendPushToUser($User, $push_message);

                if ($Order->id && $Order_invoice->id) {
                    foreach ($CartItems as $Product) {
                        $Product->order_id = $Order->id;
                        $Product->save();
                        $Product->delete();
                    }
                    // order otp push notification
                    $push_message = trans('order.order_otp', ['otp' => $newotp]);
                    (new SendPushNotification)->sendPushToUser($User, $push_message);

                    if ($request->ajax()) {
                        return Order::find($Order->id);
                    }
                    return redirect('orders/' . $Order->id)->with('flash_success', trans('order.created'));
                } else {
                    if ($request->ajax()) {
                        return response()->json(['message' => trans('form.whoops')], 422);
                    }
                    return back()->with('flash_failure', trans('form.whoops'));
                }
            } else {
                if ($request->ajax()) {
                    return response()->json(['message' => trans('form.order.cart_empty')], 422);
                }
                return back()->with('flash_failure', trans('form.whoops'));
            }

        } catch (Exception $e) {
//            dd($e);
        }
    }

    public function calculate_distance($s_latitude, $s_longitude, $d_latitude, $d_longitude)
    {

        try {

            $details = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $s_latitude . "," . $s_longitude . "&destinations=" . $d_latitude . "," . $d_longitude . "&mode=driving&sensor=false&key=AIzaSyAuxmmUPDIXgiw84E9AX7bbbdFzkd0xd50";

            $json = curl($details);

            $details = json_decode($json, TRUE);

            $meter = $details['rows'][0]['elements'][0]['distance']['value'];

            $kilometer = round($meter / 1000);

            return $kilometer;

        } catch (Exception $exception) {
            return "error";
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            $Order = Order::findOrFail($id);
            if ($request->ajax()) {
                return $Order;
            }

            return view('user.orders.confirm', compact('Order'));
        } catch (ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json(['message' => trans('order.invalid')], 422);
            }
            return back()->with('flash_failure', trans('form.whoops'));
        } catch (ModelNotFoundException $e) {

            if ($request->ajax()) {
                return response()->json(['message' => trans('order.invalid')], 422);
            }
            return back()->with('flash_failure', trans('form.whoops'));

        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $Order = Order::findOrFail($id);
        if ($Order->status == 'ORDERED') {

            $Order->status = 'CANCELLED';
            $Order->reason = $request->reason;
        }
        $Order->save();
        OrderTiming::create([
            'order_id' => $Order->id,
            'status' => 'CANCELLED'
        ]);
        return $Order;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function rate_review(Request $request)
    {
        $this->validate($request, [
            'order_id' => 'required|integer|exists:orders,id,user_id,' . Auth::user()->id,
            'rating' => 'required|integer|in:1,2,3,4,5',
            'comment' => 'max:255',
            'type' => 'required|in:shop,transporter',
        ]);
        $Order = Order::find($request->order_id);
        $OrderRequests = OrderInvoice::where('order_id', $request->order_id)
            ->where('status', 'success')
            ->where('paid', 0)
            ->first();
        if ($OrderRequests) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('order.not_paid')], 500);
            } else {
                return back()->with('flash_error', trans('order.not_paid'));
            }
        }
        try {
            $OrderRating = OrderRating::where('order_id', $request->order_id)->first();
            if (!$OrderRating) {
                if ($request->type == 'transporter') {
                    OrderRating::create([
                        'transporter_id' => $Order->transporter_id,
                        'order_id' => $Order->id,
                        'transporter_rating' => $request->rating,
                        'transporter_comment' => $request->comment,
                    ]);
                } else {
                    OrderRating::create([
                        'shop_id' => $Order->shop_id,
                        'order_id' => $Order->id,
                        'shop_rating' => $request->rating,
                        'shop_comment' => $request->comment,
                    ]);
                }
            } else {
                if ($request->type == 'transporter') {
                    $OrderRating->transporter_id = $Order->transporter_id;
                    $OrderRating->transporter_rating = $request->rating;
                    $OrderRating->transporter_comment = $request->comment;
                    $OrderRating->save();
                } else {
                    $OrderRating->shop_id = $Order->shop_id;
                    $OrderRating->shop_rating = $request->rating;
                    $OrderRating->shop_comment = $request->comment;
                    $OrderRating->save();
                }
            }
            // Send Push Notification to Provider 
            if ($request->ajax()) {
                return response()->json(['message' => trans('form.rating.rating_success')]);
            } else {
                return back()->with('flash_success', trans('form.rating.rating_success'));
            }
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('form.whoops')], 500);
            } else {
                return back()->with('flash_error', trans('form.whoops'));
            }
        }

    }


    public function chatWithUser(Request $request)
    {
        try {

            if ($request->has('dispute_id')) {
                $id = $request->dispute_id;
                $OrderDispute = OrderDispute::findOrFail($id);
                $Order = Order::findOrFail($OrderDispute->order_id);
            }
            if ($request->has('order_id')) {
                $id = $request->order_id;
                $OrderDispute = [];//OrderDispute::findOrFail($id);
                $Order = Order::findOrFail($id);
            }

            $dispute_manager = \App\Admin::role('Dispute Manager')->pluck('id', 'id')->toArray();


            return view('user.orders.chat', compact('OrderDispute', 'Order', 'dispute_manager'));
        } catch (ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('form.whoops')]);
            }
            return back()->with('flash_error', trans('form.whoops'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('form.whoops')]);
            }
            return back()->with('flash_error', trans('form.whoops'));
        }
    }
}
