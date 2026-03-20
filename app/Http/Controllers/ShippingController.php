<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\ShippingService;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(private ShippingService $shipping) {}

    public function index()
    {
        $tenantId  = auth()->user()->tenant_id;
        $shipments = Shipment::where('tenant_id', $tenantId)
            ->latest()
            ->paginate(30);

        return view('shipping.index', compact('shipments'));
    }

    public function checkRate(Request $request)
    {
        $request->validate([
            'origin'      => 'required|string',
            'destination' => 'required|string',
            'weight'      => 'required|numeric|min:0.1',
            'courier'     => 'required|string',
        ]);

        $rates = $this->shipping->getRates(
            $request->origin,
            $request->destination,
            $request->weight,
            $request->courier
        );

        return response()->json($rates);
    }

    public function track(Request $request)
    {
        $request->validate(['tracking_number' => 'required|string', 'courier' => 'required|string']);
        $result = $this->shipping->track($request->courier, $request->tracking_number);
        return response()->json($result);
    }

    public function store(Request $request)
    {
        $request->validate([
            'courier'          => 'required|string',
            'service'          => 'required|string',
            'origin_city'      => 'required|string',
            'destination_city' => 'required|string',
            'weight_kg'        => 'required|numeric',
            'shipping_cost'    => 'required|numeric',
        ]);

        $shipment = Shipment::create([
            'tenant_id'        => auth()->user()->tenant_id,
            'sales_order_id'   => $request->sales_order_id,
            'courier'          => $request->courier,
            'service'          => $request->service,
            'origin_city'      => $request->origin_city,
            'destination_city' => $request->destination_city,
            'weight_kg'        => $request->weight_kg,
            'shipping_cost'    => $request->shipping_cost,
            'recipient_name'   => $request->recipient_name,
            'recipient_address'=> $request->recipient_address,
            'status'           => 'pending',
        ]);

        ActivityLog::record('shipment_created', "Pengiriman via {$shipment->courier} dibuat", $shipment);

        return back()->with('success', 'Data pengiriman disimpan.');
    }
}
