<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Admin;
use Illuminate\Validation\Rule;



class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $common = Helper::getCommonData('Vendors');
        $vendors = Vendor::latest()->get();

        return view('admin.vendors.index', compact('common', 'vendors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $common = Helper::getCommonData('Add Vendors', null, 'Submit');
        return view('admin.vendors.add_vendor', compact('common'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    
        $request->validate([
            'username' => 'required|string|max:255|unique:vendors,username',
            'password' => 'required|min:6',
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:32',
        ]);
        
        $vendor = Vendor::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'mobile_no'=>$request->mobile_no,
            'company_name'=>$request->company_name,
            'name'=>$request->name
            
        ]);
        
        $notification = [
            'message' => 'Vendor added successfully!.',
            'alert-type' => 'success',
        ];
    
        return Helper::redirectFunction('vendors.index', $notification);
    }

    /**
     * Display vendor profile and their products.
     */
    public function show(string $id)
    {
        try {
            $vendorId = decrypt($id);
        } catch (\Exception $e) {
            abort(404);
        }

        $common = Helper::getCommonData('Vendor details');
        $vendor = Vendor::with([
            'products' => function ($query) {
                $query->with('productCategory')->latest();
            },
        ])->findOrFail($vendorId);

        $orders = Order::query()
            ->whereHas('items.product', function ($q) use ($vendorId) {
                $q->withTrashed()->where('vendor_id', $vendorId);
            })
            ->with([
                'User',
                'items' => function ($q) use ($vendorId) {
                    $q->whereHas('product', function ($q2) use ($vendorId) {
                        $q2->withTrashed()->where('vendor_id', $vendorId);
                    });
                },
            ])
            ->orderByDesc('created_at')
            ->get();

        $vendorItemsForVendor = function ($query) use ($vendorId) {
            $query->whereHas('product', function ($q) use ($vendorId) {
                $q->withTrashed()->where('vendor_id', $vendorId);
            });
        };

        $revenue = [
            'paid_total' => (float) OrderItem::query()
                ->tap($vendorItemsForVendor)
                ->whereHas('order', function ($q) {
                    $q->where('status', 1);
                })
                ->sum('subtotal'),
            'pending_total' => (float) OrderItem::query()
                ->tap($vendorItemsForVendor)
                ->whereHas('order', function ($q) {
                    $q->where('status', 0);
                })
                ->sum('subtotal'),
            'paid_orders_count' => Order::query()
                ->where('status', 1)
                ->whereHas('items.product', function ($q) use ($vendorId) {
                    $q->withTrashed()->where('vendor_id', $vendorId);
                })
                ->count(),
            'units_sold_paid' => (int) OrderItem::query()
                ->tap($vendorItemsForVendor)
                ->whereHas('order', function ($q) {
                    $q->where('status', 1);
                })
                ->sum('quantity'),
        ];

        return view('admin.vendors.show', compact('common', 'vendor', 'orders', 'revenue'));
    }

    /**
     * Show the form for editing the specified vendor.
     */
    public function edit(string $id)
    {
        try {
            $vendorId = decrypt($id);
        } catch (\Exception $e) {
            abort(404);
        }

        $common = Helper::getCommonData('Edit vendor', null, 'Update');
        $vendor = Vendor::findOrFail($vendorId);

        return view('admin.vendors.edit_vendor', compact('common', 'vendor'));
    }

    /**
     * Update the specified vendor in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $vendorId = decrypt($id);
        } catch (\Exception $e) {
            return back()->with('error', 'Invalid vendor.');
        }

        $vendor = Vendor::findOrFail($vendorId);

        $request->validate([
            'username' => ['required', 'string', Rule::unique('vendors', 'username')->ignore($vendor->id)],
            'password' => 'nullable|string|min:6',
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:32',
        ]);

        $data = [
            'username' => $request->username,
            'name' => $request->name,
            'company_name' => $request->company_name,
            'mobile_no' => $request->mobile_no,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $vendor->update($data);

        $notification = [
            'message' => 'Vendor updated successfully.',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('vendors.index', $notification);
    }

    public function getOrder(){
        
        $data = Order::with(['User', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.vendors.order',compact('data'));
   
    }
    
        public function viewOrder($id)
        {
            $data = Order::with(['items.product', 'User', 'deliveryAddress'])
                ->where('id', $id)
                ->firstOrFail();
        
            return view('admin.vendors.view', compact('data'));
        }



    public function updateStatus(Request $request)
    {
        $order = Order::findOrFail($request->order_id);

        $order->delivery_status = $request->delivery_status;
        $order->save();
    
        return redirect()
            ->route('view.order', $order->id)
            ->with('success_message', 'Order status updated successfully!');
    }



    public function destroy($id)
    {
        try {
            $vendorId = decrypt($id);
            
        } catch (\Exception $e) {
            return back()->with('error','Invalid ID');
        }
    
        $vendor = Vendor::findOrFail($vendorId);
    
        // delete all products of vendor
        Product::where('vendor_id', $vendorId)->delete();
    
        // delete vendor
        $vendor->delete();
    
         return redirect('/admin/vendors')
       ->with('success_message','Vendor & all their products deleted successfully');

    }
    
    public function getTax(){
        
    $tax = Admin::first(); // same variable name

    return view('admin.tax', compact('tax'));
     
    }
   
    public function storeTax(Request $request)
    {
        $request->validate([
            'tax_amount' => 'nullable|numeric',
            'tax_amount_per' => 'nullable|numeric'
        ]);
    
        if ($request->tax_amount && $request->tax_amount_per) {
            return back()->with('error_message', 'Only one field allowed!');
        }
    
        if (!$request->tax_amount && !$request->tax_amount_per) {
            return back()->with('error_message', 'Please enter one value!');
        }
    
        // 🔥 CONVERT PERCENT → DECIMAL
        $taxPer = $request->tax_amount_per;
    
        if ($taxPer !== null) {
            $taxPer = $taxPer / 100;
        }
    
        $admin = Admin::first();
    
        if ($admin) {
            $admin->tax_amount = $request->tax_amount ?: null;
            $admin->tax_amount_per = $taxPer;
            $admin->save();
        } else {
            Admin::create([
                'tax_amount' => $request->tax_amount ?: null,
                'tax_amount_per' => $taxPer,
                'created_at' => now()
            ]);
        }
    
        return back()->with('success_message', 'Tax saved successfully');
    }
    
      
    public function getPlatformFee(){
    
     $data = Admin::first(); // same variable name
     return view('admin.platform', compact('data'));
          
     }
     
     public function storePlatformCharge(Request $request){
         
         if (!$request->platform_charge) {
            return back()->with('error_message', 'Please enter platform charge!');
        }
    
        $admin = Admin::first();
    
        if ($admin) {
            $admin->platform_fee = $request->platform_charge;
            $admin->save();
        } else {
            Admin::create([
                'platform_fee' => $request->platform_charge
            ]);
        }
    
        return back()->with('success_message', 'Platform charge saved successfully');
    }
      
      
    public function getShippingFee(){
             $data = Admin::first(); // same variable name
    
        return view('admin.shipping', compact('data'));
      }
      
      
      
    public function storeShippingFee(Request $request){
         
         if (!$request->shipping_per) {
            return back()->with('error_message', 'Please enter shipping charge!');
        }
    
        $admin = Admin::first();
    
        if ($admin) {
            $admin->shipping_charges = $request->shipping_per;
            $admin->save();
        } else {
            Admin::create([
                'shipping_charges' => $request->shipping_per
            ]);
        }
    
        return back()->with('success_message', 'Shipping charge saved successfully');
    }
      
  
  
  
  
  
    
    
}
