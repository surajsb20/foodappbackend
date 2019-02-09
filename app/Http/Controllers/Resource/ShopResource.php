<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Route;
use Exception;
use GuzzleHttp\Client;
use App\ShopTiming;
use App\Shop;
use Setting;
use App\ShopBanner;
use App\Favorite;
use App\Product;

class ShopResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $Shops = $this->filter($request);
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
            if ($request->ajax()) {
                return ['shops' => $Shops, 'banners' => $BannerImage, 'currency' => Setting::get('currency')];
            }
            return view(Route::currentRouteName(), compact('Shops'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => trans('form.whoops')], 500);
            }
            return back()->with('flash_error', trans('form.whoops'));
        }
    }

    public function enquiry(Request $request)
    {
        try {
            $Shops = $this->filterEnquiry($request);

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
            if ($request->ajax()) {
                return ['shops' => $Shops, 'banners' => $BannerImage, 'currency' => Setting::get('currency')];
            }
            return view('admin.shops.enquiry', compact('Shops'));
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
        $Days = [
            'ALL' => 'Everyday',
            'SUN' => 'Sunday',
            'MON' => 'Monday',
            'TUE' => 'Tuesday',
            'WED' => 'Wednesday',
            'THU' => 'Thursday',
            'FRI' => 'Friday',
            'SAT' => 'Saturday'
        ];
        return view(Route::currentRouteName(), compact('Days'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:shops',
            'phone' => 'required|string|max:255',
            'latitude' => 'required|string|max:255',
            'longitude' => 'required|string|max:255',
            'cuisine_id' => 'required|array',
            'day' => 'required|array',
            'phone' => 'required|numeric',
            'password' => 'required|string|min:6|confirmed',
            'maps_address' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'avatar' => 'required|image|max:2120',
        ]);

        try {
            $Shop = $request->all();
            if ($request->hasFile('avatar')) {
                $Shop['avatar'] = asset('storage/' . $request->avatar->store('shops'));
            }


            $Shop['password'] = bcrypt($Shop['password']);
            $Shop = Shop::create($Shop);

            //Cuisine
            if ($request->has('cuisine_id')) {
                foreach ($request->cuisine_id as $cuisine) {
                    $Shop->cuisines()->attach($cuisine);
                }
            }

            //ShopTimings
            if ($request->has('day')) {
                $start_time = $request->hours_opening;
                $end_time = $request->hours_closing;
                foreach ($request->day as $key => $day) {
                    $timing[] = [
                        'start_time' => $start_time[$day],
                        'end_time' => $end_time[$day],
                        'shop_id' => $Shop->id,
                        'day' => $day
                    ];
                }
                ShopTiming::insert($timing);
            }
            return back()->with('flash_success', trans('shop.created_success', ['name' => $Shop->name]));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('form.whoops'));
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
            $user_id = $request->get('user_id') ?: null;
            $Shop_details = Shop::list($user_id);
            $Shop = $Shop_details->find($id);
            $Shop->currency = Setting::get('currency');
            $FeaturedProduct = Product::listfeatured_image($id, $request->user_id);
            if ($request->ajax()) {
                return [
                    'categories' => [$Shop],
                    'featured_products' => $FeaturedProduct
                ];
            }
            return view(Route::currentRouteName(), compact('Shop'));
        } catch (ModelNotFoundException $e) {
            // return redirect()->route('admin.shops.index')->with('flash_error', 'Shop not found!');
            return back()->with('flash_error', 'Shop not found!');
        } catch (Exception $e) {
            // return redirect()->route('admin.shops.index')->with('flash_error', trans('form.whoops'));
            return back()->with('flash_error', trans('form.whoops'));
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
        $Days = [
            'ALL' => 'Everyday',
            'SUN' => 'Sunday',
            'MON' => 'Monday',
            'TUE' => 'Tuesday',
            'WED' => 'Wednesday',
            'THU' => 'Thursday',
            'FRI' => 'Friday',
            'SAT' => 'Saturday'
        ];
        try {
            $Shop = Shop::findOrFail($id);
            return view(Route::currentRouteName(), compact('Shop', 'Days'));
        } catch (ModelNotFoundException $e) {
            // return redirect()->route('admin.shops.index')->with('flash_error', 'Shop not found!');
            return back()->with('flash_error', 'Shop not found!');
        } catch (Exception $e) {
            // return redirect()->route('admin.shops.index')->with('flash_error', trans('form.whoops'));
            return back()->with('flash_error', trans('form.whoops'));
        }
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
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:255',
            'cuisine_id' => 'required|array',
            'day' => 'required|array',
            'latitude' => 'required|string|max:255',
            'longitude' => 'required|string|max:255',
            'phone' => 'required|numeric',
            'maps_address' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'avatar' => 'image|max:2120',
        ]);

        try {
            $Shop = Shop::findOrFail($id);
            $Update = $request->all();
            if ($request->hasFile('avatar')) {
                $Update['avatar'] = asset('storage/' . $request->avatar->store('shops'));
            } else {
                unset($Update['avatar']);
            }
            if ($request->hasFile('default_banner')) {
                $Update['default_banner'] = asset('storage/' . $request->default_banner->store('shops'));
            } else {
                unset($Update['default_banner']);
            }
            if ($request->has('password')) {
                $Update['password'] = bcrypt($request->password);
            } else {
                unset($Update['password']);
            }
            if ($request->has('pure_veg')) {
                $Update['pure_veg'] = $request->pure_veg == 'no' ? 0 : 1;
            }
            if ($request->has('rating_status')) {
                $Update['rating_status'] = 1;
            } else {
                $Update['rating_status'] = 0;
            }

            $Shop->update($Update);
            //Cuisine
            $Shop->cuisines()->detach();
            if (count($request->cuisine_id) > 0) {
                foreach ($request->cuisine_id as $cuisionk) {
                    $Shop->cuisines()->attach($cuisionk);
                }
            }
            //ShopTimings
            if ($request->has('day')) {
                $start_time = $request->hours_opening;
                $end_time = $request->hours_closing;
                ShopTiming::where('shop_id', $id)->delete();
                foreach ($request->day as $key => $day) {
                    $timing[] = [
                        'start_time' => $start_time[$day],
                        'end_time' => $end_time[$day],
                        'shop_id' => $Shop->id,
                        'day' => $day
                    ];
                }
                ShopTiming::insert($timing);
            }

            return back()->with('flash_success', trans('shop.updated_success', ['name' => $Shop->name]));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', trans('form.not_found'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('form.whoops'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $Shop = Shop::findOrFail($id);
            $Shop->phone = $Shop->phone . '-' . uniqid();
            $Shop->email = $Shop->email . '-' . uniqid();
            $Shop->save();
            $Shop->delete();
            return back()->with('flash_success', 'Shop has been deleted!');
        } catch (ModelNotFoundException $e) {
            // return redirect()->route('admin.shops.index')->with('flash_error', 'Shop not found!');
            return back()->with('flash_error', trans('form.not_found'));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('form.whoops'));
        }
    }

    /**
     * Display the Shop with Request parameter.
     *
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request)
    {

        \Log::info($request);
        $distance = Setting::get('search_distance');
        $user_id = $request->get('user_id') ?: null;
        $cat = $request->get('cat') ?: null;
        $subcat = $request->get('subcat') ?: null;
        $prodname = $request->get('prodname') ?: null;
        if ($request->has('q')) {
            $prodname = $request->q;
        }

        $prodtype = $request->get('prodtype') ?: null;
        if (Setting::get('SUB_CATEGORY') == 1) {
            $Shops = Shop::listsubcategory($user_id, $cat, $subcat);
        } else {
            //\DB::enableQueryLog();
            $Shops = Shop::list($user_id, $cat, $prodname, $prodtype);
        }
        /*dd(\DB::getQueryLog());exit;
        return $Shops;*/
        // near by location 
        if ($request->has('latitude') && $request->has('longitude')) {
            $longitude = $request->longitude;
            $latitude = $request->latitude;
            if (Setting::get('search_distance') > 0) {
                $Shops->select('shops.*')
                    ->selectRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance")
                    ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance");
                $Shops->where('status', 'active');
                $Shops->orderBy('distance', 'asc');
            }
        }
        // pureveg wise search
        if ($request->has('pure_veg')) {
            $Shops->where('pure_veg', $request->pure_veg);
        }
        // offers wise search
        if ($request->has('offer')) {
            $Shops->WhereNotNull('offer_percent');
        }
        // cuisine miltiple wise search
        if ($request->has('cuisine')) {
            $Shops->whereHas('cuisines', function ($query) use ($request) {
                $query->whereIn('cuisines.id', $request->cuisine);
            });
        }
        if ($request->has('name')) {
            $Shops->where('name', 'LIKE', '%' . $request->name . '%');
        }
        if ($request->segment(1) == 'restaurants') {
            return $Shops;
        }
        return $Shops->get();
    }

    public function filterEnquiry(Request $request)
    {

        \Log::info($request);
        $distance = Setting::get('search_distance');
        $user_id = $request->get('user_id') ?: null;
        $cat = $request->get('cat') ?: null;
        $subcat = $request->get('subcat') ?: null;
        $prodname = $request->get('prodname') ?: null;
        if ($request->has('q')) {
            $prodname = $request->q;
        }

        $prodtype = $request->get('prodtype') ?: null;
        if (Setting::get('SUB_CATEGORY') == 1) {
            $Shops = Shop::listsubcategory($user_id, $cat, $subcat);
        } else {
            //\DB::enableQueryLog();
            $Shops = Shop::list($user_id, $cat, $prodname, $prodtype);
        }
        /*dd(\DB::getQueryLog());exit;
        return $Shops;*/
        // near by location
        if ($request->has('latitude') && $request->has('longitude')) {
            $longitude = $request->longitude;
            $latitude = $request->latitude;
            if (Setting::get('search_distance') > 0) {
                $Shops->select('shops.*')
                    ->selectRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance")
                    ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance");
                $Shops->where('status', 'active');
                $Shops->orderBy('distance', 'asc');
            }
        }
        // pureveg wise search
        if ($request->has('pure_veg')) {
            $Shops->where('pure_veg', $request->pure_veg);
        }
        // offers wise search
        if ($request->has('offer')) {
            $Shops->WhereNotNull('offer_percent');
        }
        // cuisine miltiple wise search
        if ($request->has('cuisine')) {
            $Shops->whereHas('cuisines', function ($query) use ($request) {
                $query->whereIn('cuisines.id', $request->cuisine);
            });
        }
        if ($request->has('name')) {
            $Shops->where('name', 'LIKE', '%' . $request->name . '%');
        }
        if ($request->segment(1) == 'restaurants') {
            return $Shops;
        }

        $Shops->where('status', 'onboarding');

        return $Shops->get();
    }
}
