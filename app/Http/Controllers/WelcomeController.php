<?php

namespace App\Http\Controllers;

use App\Document;
use App\ShopTiming;
use App\Transporter;
use App\TransporterDocument;
use Exception;
use Illuminate\Http\Request;
use App\Shop;
use App\EnquiryTransporter;
use App\User;
use App\Order;
use App\newsletter;
use Illuminate\Support\Facades\Storage;
use Session;
use App\Http\Controllers\Resource\ShopResource;

class WelcomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        $Shop = Shop::take(4)->get();
        $shop_total = Shop::count();
        $user_total = User::count();
        $order_total = Order::where('status', 'COMPLETED')->count();
        //dd($Shop);
        return view('welcome', compact('Shop', 'shop_total', 'user_total', 'order_total'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function faq(Request $request)
    {
        if ($request->ajax()) {
            $static = 1;
        }
        return view('faq', compact('static'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function aboutus()
    {
        return view('aboutus');
    }

    public function contact()
    {
        return view('contact');
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function termcondition(Request $request)
    {
        $static = 0;
        if ($request->has('static')) {
            $static = 1;
        }
        return view('term_condition', compact('static'));
    }

    public function newsletter(Request $request)
    {


        $this->validate($request, [

            'email_newsletter_2' => 'required|email',

        ]);
        try {

            newsletter::create([
                'email' => $request->email_newsletter_2
            ]);
            return back()->with('flash_success', trans('home.delivery_boy.created'));

        } catch (Exception $e) {

            return back()->with('flash_error', trans('form.whoops'));
        }

    }

    public function search(Request $request)
    {
        $Shops = [];
        $url = url()->previous();
        $url_segment = explode('/', $url);

        if ($request->segment(1) != $url_segment[3]) {
            Session::put('search_return_url', $url);
        }
        if ($request->has('q')) {
            $request->request->add(['prodname', $request->q]);

            $shops = (new ShopResource)->filter($request);
            //dd($shops);
            foreach ($shops as $val) {
                if (preg_match("/" . $request->q . "/i", $val->name, $matches)) {
                    $Shops[$val->id] = $val;
                } else {
                    foreach ($val->categories as $valcat) {
                        if (count($valcat->products) > 0) {
                            $Shops[$val->id] = $val;
                        }
                    }
                }
            }
            //dd($Shops);
        }

        return view('search', compact('Shops'));
    }


    public function delivery()
    {  // dd('jjjj');
        //$Shop = Shop::take(4)->get();
        //dd($Shop);
        return view('delivery_enquiry');
    }

    public function partner()
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

        return view('partner', compact('Days'));
    }

    public function partnerStore(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:shops',
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
            return back()->with('flash_success', trans('Your request submitted successfully.', ['name' => $Shop->name]));
        } catch (Exception $e) {
            return back()->with('flash_error', trans('form.whoops'));
        }

    }

    public function delivery_store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|max:255|unique:transporters',
            'phone' => 'required|unique:transporters',
            'address' => 'required',
        ]);
        try {

            $transporter = Transporter::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => '12345678'
            ]);

            $documents = Document::all();

            foreach ($documents as $document) {
                $transporterDoc = new TransporterDocument();
                $transporterDoc->transporter_id = $transporter->id;
                $transporterDoc->document_id = $document->id;
                $transporterDoc->unique_id = rand(100000, 999999);
                $transporterDoc->url = $request['doc_' . $document->id]->store('provider/documents');
                $transporterDoc->save();
            }

            if ($request->hasFile('avatar')) {
                $transporter['avatar'] = asset('storage/' . $request->avatar->store('transporter/profile'));
            }

            return back()->with('flash_success', trans('home.delivery_boy.created'));

        } catch (Exception $e) {

            dd($e);

            return back()->with('flash_error', trans('form.whoops'));
        }

    }

    //
    public function contactPost(Request $request)
    {

        // send main to admin

        return back()->with('flash_success', 'Thank you for reaching out. We will contact you soon.');
    }


}
