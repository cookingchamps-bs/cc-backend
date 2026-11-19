<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Product,ProductCategory,ProductImage,Order};
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index()
    {
        $common = Helper::getCommonData('Products');
    
        $panel = $this->panel();
        $layout = $panel === 'admin' ? 'admin.layouts.master' : 'admin.layouts.vendor_master';
    
        $productQuery = Product::with('vendor');
    
            if ($panel === 'vendor') {
                $productQuery->where('vendor_id', Auth::guard('vendor')->id());
            }
        
        $products = $productQuery->orderBy('created_at', 'desc')->get();
    
        return view('admin.vendors.products.index', compact('common', 'products', 'layout'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       $common = Helper::getCommonData('Add Products', null, 'Submit');
       
       $panel = $this->panel();
       $layout = $panel === 'admin' ? 'admin.layouts.master' : 'admin.layouts.vendor_master';
       
       $productCategories = ProductCategory::whereStatus(1)->get();
       return view('admin.vendors.products.create', compact('common','productCategories','layout'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $panel = $this->panel();
        $actor = $this->resolveActor($panel);
    
        // validate
        $request->validate([
            'title'            => 'required|string|max:255',
            'product_category' => 'required|exists:product_categories,id',
            'image' => 'required|file|mimetypes:image/jpeg,image/png,image/avif',
            'gallery_images'   => 'required|array',
          'gallery_images.*' => 'file|mimetypes:image/jpeg,image/png,image/avif',
            'quantity'         => 'required|integer|min:1',
            'description'      => 'required|string',
            'selling_price'    => 'required|numeric|min:0',
            'cost_price'       => 'required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0'
        ]);
        
        $addProducts = Product::create([
             'vendor_id' => $actor['vendor_id'],
             'product_cat_id' => $request->product_category,
             'title' => $request->title,
             'image' => Helper::handleFileUpload($request, 'image', 'products/featured_image'),
             'description' => $request->description,
             'added_by' => $actor['added_by'],
             'quantity' => $request->quantity,
             'selling_price' => $request->selling_price,
             'cost_price' => $request->cost_price,
             'discount_price' => $request->discounted_price,
             'status' => 1
        ]);
        
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $image) {
        
                $destinationPath = public_path('storage/products/gallery_image');
        
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
        
                $fileName = time().'_'.$index.'.'.$image->getClientOriginalExtension();
        
                $image->move($destinationPath, $fileName);
        
                $path = 'storage/products/gallery_image/'.$fileName;
        
                ProductImage::create([
                    'product_id' => $addProducts->id,
                    'images'     => $path,
                ]);
            }
        }
        
        
        $notification = [
            'message' => 'Product added Successfully!',
            'alert-type' => 'success',
        ];
        
        return Helper::redirectFunction($panel. '.products.index', $notification);
    }

 
    public function edit(string $id)
    {
        $common = Helper::getCommonData('Edit Products', null, 'update');
        
        $panel = $this->panel();
        $layout = $panel === 'admin' ? 'admin.layouts.master' : 'admin.layouts.vendor_master';
        
        $productCategories = ProductCategory::whereStatus(1)->get();
        $products = $this->findOwnedProduct($id, $panel);
    
        return view('admin.vendors.products.edit', compact('common','products','productCategories','layout'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $panel = $this->panel();
        $products = $this->findOwnedProduct($id, $panel);
        
        // validate
        $request->validate([
            'title'            => 'required|string|max:255',
            'product_category' => 'required|exists:product_categories,id',
            'image'            => 'nullable|image|mimes:jpeg,jpg,png',
            'gallery_images'   => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,jpg,png',
            'quantity'         => 'required|integer|min:1',
            'description'      => 'required|string',
            'selling_price'    => 'required|numeric|min:0',
            'cost_price'       => 'required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0'
        ]);

        $vendorId = $panel === 'vendor'
            ? Auth::guard('vendor')->id()
            : $products->vendor_id;
        
        $products->update([
             'vendor_id' => $vendorId,
             'product_cat_id' => $request->product_category,
             'title' => $request->title,
             'image' => $request->file('image') ? Helper::handleFileUpload($request, 'image', 'products/featured_image') : $products->image,
             'description' => $request->description,
             'added_by' => $products->added_by,
             'quantity' => $request->quantity,
             'selling_price' => $request->selling_price,
             'cost_price' => $request->cost_price,
             'discount_price' => $request->discounted_price,
             'status' => 1
        ]);
        
        // gallery images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $image) {
                $fileName = time() . '_' . $index . '.' . $image->getClientOriginalExtension();
                $ImagePath = $image->storeAs('products/gallery_image', $fileName, 'public');
                $path = 'storage/'.$ImagePath;
        
                ProductImage::create([
                    'product_id' => $products->id,
                    'images'     => $path,
                ]);
            }
        }
        
        $notification = [
            'message' => 'Product Updated Successfully!',
            'alert-type' => 'success',
        ];
        
        return Helper::redirectFunction($panel. '.products.index', $notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $panel = $this->panel();
            
            $products = $this->findOwnedProduct($id, $panel, withImages: true);
            
            Helper::deletePublicFile($products->getRawOriginal('image') ?? $products->image);
            
            foreach ($products->productImages as $img) {
                Helper::deletePublicFile($img->images);
            }
            
            $products->delete();
            
            $notification = [
                'message' => 'Products deleted Successfully!',
                'alert-type' => 'success',
            ];
            
            return Helper::redirectFunction($panel. '.products.index', $notification);
        }catch(\Exception $e){
            \Illuminate\Support\Facades\Log::error('Product delete failed: '.$e->getMessage());
            return response()->json(['status' => false, 'message' => 'Unable to delete product.'], 500);
        }
    }
    
    public function orderList()
    {
        $vendor = Auth::guard('vendor')->user();
        $vendorId = $vendor->id;

        $orders = Order::with(['user', 'items.product'])
            ->whereHas('items.product', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->latest()
            ->get()
            ->map(function (Order $order) use ($vendorId) {
                $vendorItems = $order->items->filter(function ($item) use ($vendorId) {
                    return $item->product && (int) $item->product->vendor_id === (int) $vendorId;
                });

                $order->setRelation('items', $vendorItems->values());
                $order->vendor_subtotal = $vendorItems->sum(function ($item) {
                    return (float) ($item->subtotal ?? (($item->price ?? 0) * ($item->quantity ?? 0)));
                });

                return $order;
            });

        return view('admin.vendors.orderList', compact('orders'));
    }
    
    
    public function updateProductStatus(Request $request)
    {
        if (! $request->ajax()) {
            abort(404);
        }

        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $panel = $this->panel();
        $query = Product::query()->where('id', $request->product_id);
        if ($panel === 'vendor') {
            $query->where('vendor_id', Auth::guard('vendor')->id());
        }

        $product = $query->firstOrFail();
        $product->status = $product->status == 1 ? 0 : 1;
        $product->save();

        return response()->json(['status' => $product->status]);
    }

    private function panel(): string
    {
        return request()->is('admin/*') ? 'admin' : 'vendor';
    }

    private function resolveActor(string $panel): array
    {
        if ($panel === 'vendor') {
            $vendor = Auth::guard('vendor')->user();
            abort_unless($vendor, 403);

            return [
                'added_by' => 'vendor',
                'vendor_id' => $vendor->id,
            ];
        }

        // Admin panel must not inherit a concurrent vendor session.
        return [
            'added_by' => 'admin',
            'vendor_id' => null,
        ];
    }

    private function findOwnedProduct(string $encryptedId, string $panel, bool $withImages = false): Product
    {
        $query = Product::query();
        if ($withImages) {
            $query->with('productImages');
        } else {
            $query->with('productImages');
        }

        $product = $query->findOrFail(decrypt($encryptedId));

        if ($panel === 'vendor') {
            $vendorId = Auth::guard('vendor')->id();
            abort_unless($vendorId && (int) $product->vendor_id === (int) $vendorId, 403);
        }

        return $product;
    }
}
