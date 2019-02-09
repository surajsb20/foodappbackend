<?php

namespace App\Http\Controllers\TransporterResource;

use App\Order;
use App\OrderInvoice;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EarningController extends Controller
{
    //
    public function index(Request $request)
    {
        try {
            $Transporter = $request->user();

            $TransporterShift = $Transporter->shift();

            if (count($TransporterShift) > 0) {
                $id = $TransporterShift[0]->id;

                $Order_total_amount = OrderInvoice::withTrashed()->with('orders')
                    ->whereHas('orders', function ($q) use ($id) {
                        $q->where('orders.shift_id', $id);
                        $q->where('orders.status', 'COMPLETED');
                    })->sum('net');

                $Order_total_tip = Order::withTrashed()
                    ->where('orders.shift_id', $id)
                    ->where('orders.status', 'COMPLETED')
                    ->sum('tip');

                $TransporterShift[0]->total_amount = (int)$Order_total_amount;
                $TransporterShift[0]->total_tip = (int)$Order_total_tip;
            }

            return $TransporterShift;

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('form.whoops')], 500);
        } catch (Exception $e) {
            return response()->json(['error' => trans('form.whoops')], 500);
        }
    }

    //
    public function checkStatus(Request $request)
    {
        try {
            $transporter = $request->user();

            if ($transporter->is_active) {
                return response()->json(['success' => 'Transporter is activated.']);
            } else {
                return response()->json(['error' => 'Transporter is not activated.']);
            }

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => trans('form.whoops')], 500);
        } catch (Exception $e) {
            dd($e);
            return response()->json(['error' => trans('form.whoops')], 500);
        }
    }

}
