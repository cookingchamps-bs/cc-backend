<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\{Validator,Auth};
use App\Models\{
    Kid,
    User,
    Product,
    ProductCategory,
    Order
};
use Carbon\Carbon;

class OrderController extends Controller
{
    // my orders
    public function myOrders(Request $request)
    {
        $user = auth()->user();
        
        $deliveryStatus = match ($request->type) {
            'delivered' => [3],
             'orders'     => [0, 1,2,4],
        };
        
        
        $orders = Order::select('id', 'unique_id', 'total_amount', 'delivery_status', 'created_at')
            ->where('user_id', $user->id)
            ->whereIn('delivery_status', $deliveryStatus)
            ->orderBy('created_at','desc')
            ->get()
            ->map(fn($order) => [
                'id'           => $order->id,
                'unique_id'    => $order->unique_id,
                'total_amount' => $order->total_amount,
                'delivery_status' => $order->delivery_status,
                'order_date'   => $order->formatted_order_date,
        ]);


        return Helper::responseFunction(true, 200, 'Orders retrieve successfully!', $orders, 200);
    }
    
    // order details
    public function orderDetails(Request $request)
    {
       
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);
        
        $user = auth()->user();
        $orderDetail = Order::with(['orderItem', 'deliveryAddress'])
            ->where('id', $request->order_id)
            ->where('user_id', $user->id)
            ->first();
        if (! $orderDetail) {
            return Helper::responseFunction(false, 404, 'Orders details not found!', null, 404);
        }
        
        $data = $orderDetail->toArray();
        $data['net_amount'] = (string) ($orderDetail->total_amount + $orderDetail->tax_amount);
        
        return Helper::responseFunction(true, 200, 'Orders details retrieve successfully!', $data, 200);
    }
}
