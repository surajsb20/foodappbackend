<?php

namespace App\Http\Controllers\UserResource;

use App\UserAddress;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Auth;
use Setting;
use Exception;
use App\Http\Controllers\Resource\ShopResource;
use App\Http\Controllers\UserResource\CartResource;
use App\Http\Controllers\Resource\CardResource;
use App\Shop;
use App\Product;
use Session;
use App\UserCart;
use App\Cuisine;
use App\Order;
use App\CartAddon;
use App\Category;
use App\Promocode;
use App\ShopBanner;
use Carbon\Carbon;

class SearchResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $user_id = NULL;
            if ($request->has('user_id')) {
                $user_id = $request->user_id ?: NULL;
            }
            if ($request->has('search_loc')) {
                Session::put('search_loc', $request->search_loc);
            }
            if ($request->has('latitude')) {
                Session::put('latitude', $request->latitude);
            }
            if ($request->has('longitude')) {
                Session::put('longitude', $request->longitude);
            }
            $Products = Product::listsearch($user_id, $request->name);

            $Shops = (new ShopResource)->filter($request);

            if ($request->has('latitude') && $request->has('longitude')) {
                $longitude = $request->longitude;
                $latitude = $request->latitude;
                if (Setting::get('search_distance') > 0) {
                    $distance = Setting::get('search_distance');
                    $BannerImage = ShopBanner::with('shop', 'product')
                        ->whereHas('shop', function ($query) use ($latitude, $longitude, $distance) {
                            //$query->where('content', 'like', 'foo%');
                            $query->select('shops.*')
                                ->selectRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(shops.latitude) ) * cos( radians(shops.longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(shops.latitude) ) ) ) AS distance")
                                ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(shops.latitude) ) * cos( radians(shops.longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(shops.latitude) ) ) ) <= $distance");
                        })->get();
                } else {
                    $BannerImage = ShopBanner::with('shop', 'product')->get();
                }
            } else {
                $BannerImage = ShopBanner::with('shop', 'product')->get();
            }

            $Cuisines = Cuisine::all();
            //$shop = $Shops;
            $Shops_new = clone $Shops;
            $Shops_popular = clone $Shops;
            $Shops_superfast = clone $Shops;
            $Shops_offers = clone $Shops;
            $Shops_vegiterian = clone $Shops;
            //print_r(DB::getQueryLog()); exit;
//            $Shops = $Shops->get();

            $data = [
                'products' => $Products,
                'shops' => $Shops
            ];

            if ($request->ajax()) {
                return $data;
            }

            if ($request->get('v') == 'grid') {
                return view('user.shop.index-grid', compact('Shops', 'Cuisines'));
            } else
                if ($request->get('v') == 'map') {
                    return view('user.shop.index-map', compact('Shops', 'Cuisines'));
                } else {
                    return view('user.shop.index', compact('Shops', 'Cuisines', 'BannerImage', 'Shops_popular', 'Shops_superfast', 'Shops_offers', 'Shops_vegiterian', 'Shops_new'));
                }

        } catch (Exception $e) {
            if ($request->ajax()) {

                return response()->json(['error' => trans('form.whoops')], 500);
            }
            return back()->with('flash_error', trans('form.whoops'));
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id = NULL)
    {
        try {
            if ($request->has('veg')) {
                $request->request->add(['food_type' => 'veg']);
            }
            if ($request->has('name')) {
                $request->request->add(['name' => $request->name]);
            }
            $Shop_details = (new ShopResource)->filter($request);
            $Shop = $Shop_details[0];
            $FeaturedProduct1 = Product::with('featured_images', 'categories')->where('shop_id', $Shop->id)->where('featured', '!=', '0');
            if ($request->has('prodname')) {
                $FeaturedProduct1->where('products.name', 'LIKE', '%' . $request->prodname . '%');
            }

            $FeaturedProduct = $FeaturedProduct1->orderBy('featured', 'ASC')->get();
            //print_r(\DB::getQueryLog());
            if (Setting::get('SUB_CATEGORY') == 1) {
                $Categories = Category::with('subcategories')->where('parent_id', 0)->where('shop_id', $Shop->id)->orderBy(\DB::raw('ISNULL(position), position'), 'ASC')->get();
            } else {
                \DB::enableQueryLog();
                $Categories = Category::where('shop_id', $Shop->id);
                $Categories->orderBy(\DB::raw('ISNULL(position), position'), 'ASC')->get();
            }

            //print_r(\DB::getQueryLog());
            // echo 44444;exit;
            $Cart = Session::get('shop') ?: [];
            if (Auth::user()) {
                $shop_id = @key($Cart);
                if (isset($Cart[$shop_id])) {
                    foreach ($Cart[$shop_id] as $item) {
                        $CartProduct = UserCart::create([
                            'user_id' => $request->user()->id,
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                            'note' => $item['note']
                        ]);
                        if (count($item['addons']) > 0) {
                            foreach ($item['addons'] as $key => $val) {
                                CartAddon::create([
                                    'addon_product_id' => $val['addon_product_id'],
                                    'user_cart_id' => $CartProduct->id,
                                    'quantity' => $val['quantity'],
                                ]);
                            }

                        }
                    }
                    Session::pull('shop');
                }

                $Cart = (new CartResource)->index($request);
                //dd($Cart);
                if (isset($request->myaddress)) {
                    if (Setting::get('payment_mode') == 'braintree') {
                        $request->merge([
                            'type' => "braintree"
                        ]);

                    }
                    if (Setting::get('payment_mode') == 'stripe') {
                        $request->merge([
                            'type' => "stripe"
                        ]);

                    } else {
                        $request->merge([
                            'type' => "bambora"
                        ]);
                    }

                    $eather_response = '';
                    $ripple_response = '';

                    $client = new \GuzzleHttp\Client();
                    $request_ripple = $client->get('https://www.bitstamp.net/api/v2/ticker/xrpusd');
                    $ripple_response = json_decode($request_ripple->getBody());

                    $client = new \GuzzleHttp\Client();
                    $request_ether = $client->get('https://api.etherscan.io/api?module=stats&action=ethprice&apikey=' . Setting::get('ETHER_KEY'));
                    $ether_response = json_decode($request_ether->getBody());


                    $cards = (new CardResource)->index($request);

                    $Promocode = Promocode::with('pusage');
                    $Promocodes = $Promocode->where('status', 'ADDED')->where('expiration', '>', date("Y-m-d"))->get();
                    //dd($Promocodes);


                    //-----------------
                    if ($request->has('address_id')) {
                        $address_id = $request->address_id;
                        $Useraddress = UserAddress::find($request->address_id);
                    } else {
                        $Useraddress = UserAddress::where('user_id', Auth::user()->id)->where('type', $request->myaddress)->first();
                    }
                    $longitude = $Useraddress->longitude;
                    $latitude = $Useraddress->latitude;

                    $deliveryCharge = Setting::get('delivery_charge', 3);
                    $baseDistance = Setting::get('base_delivery_km', 3);

                    $totalDistance = $this->calculate_distance($latitude, $longitude, $Shop->latitude, $Shop->longitude);

                    if ($totalDistance != 'error') {
                        if ($totalDistance > $baseDistance) {
                            $deliveryCharge = (($totalDistance - $baseDistance) * Setting::get('after_base_charges', 1)) + Setting::get('delivery_charge', 3);
                        } else {
                            $deliveryCharge = Setting::get('delivery_charge', 3);
                        }
                    }

                    return view('user.shop.delivery_address', compact('Shop', 'Cart', 'cards', 'Promocodes',
                        'ripple_response', 'ether_response', 'deliveryCharge', 'totalDistance', 'address_id', 'Useraddress'));
                }
            }
            //dd($Shop);
            return view('user.shop.show', compact('Shop', 'Cart', 'FeaturedProduct', 'Categories'));
        } catch (Exception $e) {

//            dd($e);

            if ($request->ajax()) {
                return response()->json(['error' => trans('form.whoops')], 500);
            }
            return back()->with('flash_error', $e->getMessage());
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
    public function destroy($id)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function ordertrack(Request $request, $id)
    {
        try {
            $Order = Order::findOrFail($id);;
            if ($request->ajax()) {
                return $Order;
            }
            return view('user.orders.track_order', compact('Order'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('form.whoops')], 500);
            }
            return back()->with('flash_error', trans('form.whoops'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function productDetails(Request $request, $id, $cartid, $shopname, $productname)
    {
        try {
            $ids = explode('-', base64_decode($id));
            $shop_id = $ids[1];
            $product_id = $ids[0];
            $Shop = Shop::findOrFail($shop_id);
            $Product = Product::with('images', 'addons', 'cart')->where('id', $product_id)->firstOrFail();

            $Cart = Session::get('shop') ?: [];
            if (Auth::user()) {
                $shop_id = @key($Cart);
                if (isset($Cart[$shop_id])) {
                    foreach ($Cart[$shop_id] as $item) {
                        $CartProduct = UserCart::create([
                            'user_id' => $request->user()->id,
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                            'note' => $item['note']
                        ]);
                        if (count($item['addons']) > 0) {
                            foreach ($item['addons'] as $key => $val) {
                                CartAddon::create([
                                    'addon_product_id' => $val['addon_product_id'],
                                    'user_cart_id' => $CartProduct->id,
                                    'quantity' => $val['quantity'],
                                ]);
                            }
                        }
                    }
                    Session::pull('shop');
                }
            }

            $CartShop = Session::get('shop') ?: [];

            if (Auth::user()) {
                $Cart = UserCart::with('cart_addons')->where('id', $cartid)->first();
            } else {
                $Cart = [];
            }

            return view('user.shop.product_details', compact('Product', 'Shop', 'Cart', 'CartShop'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('form.whoops')], 500);
            }
            return back()->with('flash_error', trans('form.whoops'));
        }
    }

    public function favourites()
    {
        $Shops = Shop::all();
        return view('user.shop.favourites', compact('Shops'));
    }

    public function offers()
    {
        try {
            $Promocodes = Promocode::all();
            return view('user.orders.offers', compact('Promocodes'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('form.whoops'));
        }

    }
}
