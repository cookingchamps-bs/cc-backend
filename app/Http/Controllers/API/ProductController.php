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
    WishList,
    AddToCart,
    Address
};

class ProductController extends Controller
{
    /**
     * product category list
     */ 
    public function productCategory()
    {
        try{
            $productCategories = ProductCategory::where('status', 1)
                ->select(['id', 'name', 'image', 'status'])
                ->get();
            if(!$productCategories){
                return Helper::responseFunction(true, 200, 'Product categories not found!', $productCategories, 404);
            }
            
            return Helper::responseFunction(true, 200, 'Product categories retrieve successfully!', $productCategories, 200);
        }catch(\Exception $e){
            return response()->json(['status' => false, 'status_code' => 500, 'message' => 'An error occurred: '.$e->getMessage()], 500);
        }
    }
    
    
    /*
     * shop api
     */
    public function shops(Request $request)
    {
        try{
            $user = auth()->user();
            $userConstraint = $this->shopUserConstraint($user);
            
            $productCategoriesQuery = ProductCategory::where('status', 1)
                ->select(['id', 'name', 'image', 'status']);
            $productsQuery = Product::where('status', 1)
                ->select([
                    'id',
                    'product_cat_id',
                    'title',
                    'image',
                    'description',
                    'quantity',
                    'selling_price',
                    'discount_price',
                ])
                ->with([
                    'wishlist' => $userConstraint,
                    'cart' => $userConstraint,
                ]);
            
            // SEE ALL CATEGORIES (Paginated)
            if ($request->type === 'see_all_categories') {
                $paginated = $productCategoriesQuery->paginate(10);
                $paginatedData = ['total_page'=>$paginated->lastPage(), 'items'=>$paginated->items()];
                return Helper::responseFunction(true, 200, 'Data retrieve successfully!', $paginatedData, 200);
            }
    
            // SEE ALL PRODUCTS 
            if($request->type === 'see_all_products'){
                $paginated = $productsQuery->paginate(10);
                $paginatedData = [
                    'total_page'=>$paginated->lastPage(),
                    'items'=>$this->mapShopListProducts($paginated->getCollection()),
                ];
                return Helper::responseFunction(true, 200, 'Data retrieve successfully!', $paginatedData, 200);
            }
            
            // SEE ALL WISHLIST PRODUCTS
            if($request->type === 'see_all_wishlist_products'){
                $wishlistData = $productsQuery
                    ->whereHas('wishlist', $userConstraint)
                    ->paginate(10);
                    
                $paginatedData = [
                    'total_page'=>$wishlistData->lastPage(),
                    'items'=>$this->mapShopListProducts($wishlistData->getCollection()),
                ];
                return Helper::responseFunction(true, 200, 'Data retrieve successfully!', $paginatedData, 200);
            }
            
            if ($request->filled('cat_id')) {
                $productsQuery->where('product_cat_id', $request->cat_id);
            }
            
            if($request->filled('search')) {
                $productsQuery->where('title', 'LIKE', '%' . $request->search . '%');
            }
            
            $totalWishlistItems = WishList::query()->where($userConstraint)->count();
            $totalCartItems = AddToCart::query()->where($userConstraint)->count();
            
            $paginatedProducts = $productsQuery->paginate(10);
            
            $paginatedData = [
                'total_page'=>$paginatedProducts->lastPage(), 
                'current_page'=>$paginatedProducts->currentPage(), 
                'items'=>$this->mapShopListProducts($paginatedProducts->getCollection()),
            ];
            
            $data = [
                'total_wishlist_items' => $totalWishlistItems,
                'total_cart_items'     => $totalCartItems,
                'products'             => $paginatedData,
            ];
            
            return Helper::responseFunction(true, 200, 'Data retrieve successfully!', $data, 200);
        }catch(\Exception $e){
            return response()->json(['status' => false, 'status_code' => 500, 'message' => 'An error occurred: '.$e->getMessage()], 500);
        }
    }
    
    /*
     * product detail api
     */
    public function productDetail(Request $request)
    {
        try{
            $user = auth()->user();
            
            $productDetails = Product::find($request->product_id);
            
            if(!$productDetails){
                return Helper::responseFunction(true, 200, 'Product not found!', "", 404);
            }
            
            $productDetails = $this->appendProductImageVariants($productDetails);
            $productDetails->load([
                'productImages',
                'cart' => function ($query) use ($user) {
                    if ($user->role === 'parent') {
                        $query->where('parent_id', $user->id)
                            ->whereNull('kid_id')
                            ->where('role', 'parent');
                    } elseif ($user->role === 'kid') {
                        $query->where('kid_id', $user->id)
                            ->where('parent_id', $user->parent_id)
                            ->where('role', 'kid');
                    }
                },
            ]);
            $productDetails->productImages->transform(function ($image) {
                $variants = Helper::getImageVariants($image->images);
                $image->images_medium = $variants['medium'];
                $image->images_thumb = $variants['thumb'];

                return $image;
            });
            
            return Helper::responseFunction(true, 200, 'Data retrieve successfully!', $productDetails, 200);
        }catch(\Exception $e){
            return response()->json(['status' => false, 'status_code' => 500, 'message' => 'An error occurred: '.$e->getMessage()], 500);
        }
    }
    
     /**
     * add remove wishlist
     */ 
    public function addToWishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }
        
        $user = auth()->user();
        
        
        $wishlists = WishList::when($user->role === 'parent', function($query) use($user,$request){
                  $query->where('product_id', $request->product_id)
                  ->where('parent_id', $user->id)
                  ->whereNull('kid_id')
                  ->where('role', $user->role);
            })->when($user->role === 'kid', function($query) use($user,$request) {
                $query->where('product_id', $request->product_id)
                  ->where('kid_id', $user->id)
                  ->where('parent_id', $user->parent_id)
                  ->where('role', $user->role);
            })->first();
            
        // return $wishlists;
        if($wishlists){
            $wishlists->delete();
            return Helper::responseFunction(true, 200, 'Product remove form wishlist successfully!', "", 200);
        }
        
        $wishlist = WishList::create([
            'product_id'=> $request->product_id,
            'parent_id' => $user->role == 'parent' ? $user->id : $user->parent_id,
            'kid_id' => $user->role == 'kid' ? $user->id : null,
            'role'      => $user->role,
            'sub_role'  => $user->sub_role
        ]);
        
        return Helper::responseFunction(true, 200, 'Product added to wishlist successfully!', $wishlist, 200);
    }
    
    /**
     * get wishlist items
     */
    public function getWishlistData(Request $request)
    {
        $user = auth()->user();
        
        $wishlist = WishList::when($user->role === 'parent', function($query) use($user,$request) {
                        $query->where('role', $user->role)
                              ->where('parent_id', $user->id);
                    })->when($user->role === 'kid', function($query) use($user,$request) {
                        $query->where('role', $user->role)
                              ->where('kid_id', $user->id);
                    })
                    ->with('products')
                    ->paginate(10);
                    
        $data = [
            'total_page'   => $wishlist->lastPage(),
            'current_page' => $wishlist->currentPage(),
            'items'        => $wishlist->items()
        ];
                            
        if($wishlist->isEmpty()) {
            return Helper::responseFunction(true, 200, 'Wishlist not found!', $data, 200);
        }
        
        return Helper::responseFunction(true, 200, 'Wishlist retrieve successfully!', $data, 200);                    
    }
    
    /**
     * Add to cart
     */
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }
        
        $user = auth()->user();
        
        $product = Product::select('selling_price', 'quantity')->find($request->product_id);
        $qty = max(1, (int) ($request->quantity ?? 1));
        if (!empty($product->quantity)) {
            $qty = min($qty, (int) $product->quantity);
        }
       
        $cartQuery = AddToCart::when($user->role === 'parent', function($query) use($user,$request){
                  $query->where('product_id', $request->product_id)
                  ->where('parent_id', $user->id)
                  ->whereNull('kid_id')
                  ->where('role', $user->role);
            })->when($user->role === 'kid', function($query) use($user,$request) {
                $query->where('product_id', $request->product_id)
                  ->where('kid_id', $user->id)
                  ->where('parent_id', $user->parent_id)
                  ->where('role', $user->role);
            });

        $existingCart = $cartQuery->first();
            
        if ($existingCart) {
            $existingCart->update(['quantity' => $qty]);
            return Helper::responseFunction(true, 200, 'Cart quantity updated successfully!', $existingCart, 200);
        }
        
        $addToCart = AddToCart::create([
            'product_id'=> $request->product_id,
            'parent_id' => $user->role == 'parent' ? $user->id : $user->parent_id,
            'kid_id'    => $user->role == 'kid' ? $user->id : null,
            'role'      => $user->role,
            'sub_role'  => $user->sub_role,
            'price'     => $product->selling_price,
            'quantity'  => $qty
        ]);
        
        return Helper::responseFunction(true, 200, 'Product added to cart successfully!', $addToCart, 200);
    }
    
    /*
    * remove cart
    */
    public function removeCart(Request $request)
    {
        $request->validate([
            // 'cart_id'    => 'required',
            'product_id' => 'required'
        ]);
        
        $user = auth()->user();
        
        $carts = AddToCart::when($user->role === 'parent', function($query) use($user,$request){
                  $query->where('product_id', $request->product_id)
                  ->where('parent_id', $user->id);
            })->when($user->role === 'kid', function($query) use($user,$request) {
                $query->where('product_id', $request->product_id)
                  ->where('kid_id', $user->id)
                  ->where('parent_id', $user->parent_id)
                  ->where('role', $user->role);
            })->get();
            
        foreach($carts as $val){
            $val->delete();
        }
        
        return Helper::responseFunction(true, 200, 'Product remove from cart successfully!', null, 200);
    }
    
    /*
    * add address
    */
    public function addAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string',
            'address'      => 'required',
            'address_type' => 'required|string'
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }
        
        $user = auth()->user();
    
        $addressData = [
            'parent_id'   => $user->id,
            'role'        => $user->role,
            'sub_role'    => $user->sub_role === 'individual' ? 'individual' : null,
            'name'        => $request->name,
            'address'     => $request->address,
            'address_type'=> $request->address_type
        ];
    
        // insert data
        Address::updateOrCreate(
            [
                'parent_id'    => $user->id,
                'address'      => $request->address,
                'address_type' => $request->address_type
            ],
            $addressData
        );
    
        $allAddress = Address::where('parent_id', $user->id)->get();
    
        return Helper::responseFunction(true, 200, 'Address added successfully!', $allAddress, 200);
    }
    
    /*
    * edit address
    */
    public function editAddress(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'name' => 'nullable|string',
            'address' => 'nullable',
            'address_type' => 'nullable'
        ]);
        
        $user = auth()->user();
        
        $address = Address::where('id', $request->address_id)
                      ->where('parent_id', $user->id)
                      ->first();
                      
        if (!$address) {
            return Helper::responseFunction(true, 200, 'Address not found.', null, 404);
        }
        
        $editAddress = $address->update([
              'name' => $request->name ?? $address->name,
              'address' => $request->address ?? $address->address,
              'address_type' => $request->address_type ?? $address->address_type,
        ]);
                                
        return Helper::responseFunction(true, 200, 'Address updated successfully!', null, 200);
    }
    
    /*
    * delete address
    */
    public function deleteAddress(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);
        
        $user = auth()->user();
    
        $address = Address::where('id', $request->address_id)
                      ->where('parent_id', $user->id)
                      ->first();
                      
        if(!$address){
            return Helper::responseFunction(true, 200, 'Address not found.', null, 404);
        }
                      
        $address->delete();
                   
        return Helper::responseFunction(true, 200, 'Address deleted successfully!', null, 200);
    }
    
    /*
    * address list
    */
    public function AddressList(Request $request)
    {
        $user = auth()->user();
        
        $addressList = Address::where('parent_id', $user->id)->get();
        
        if($addressList->isEmpty()) {
            return Helper::responseFunction(true, 200, 'Address not found!', [], 404);
        }
        
        return Helper::responseFunction(true, 200, 'Address list retrieve successfully!', $addressList, 200);
    }
    
    /**
     * show cart list
     */ 
    public function cartList(Request $request)
    {
        $user = auth()->user();
        
        $taxAmt = Helper::getPlatformCharges();
        
        $cartItems = AddToCart::when($user->role === 'parent', function ($query) use ($user) {
            $query->where('parent_id', $user->id);
        })
        ->when($user->role === 'kid', function ($query) use ($user) {
            $query->where('kid_id', $user->id)
                  ->where('parent_id', $user->parent_id)
                  ->where('role', $user->role);
        })
        ->with('product')
        ->get();
        
        
        if ($user->role === 'parent') {
            // Group by product_id for both parent and their kids
            $grouped = $cartItems->groupBy('product_id')->map(function ($items) {
                $first = $items->first();
                
                $totalQty = $items->sum('quantity');
                $totalPrice = $items->sum(function($item) {
                    return $item->quantity * $item->price;
                });
                
                return [
                    'cart_id'     => $first->id,
                    'product_id'  => $first->product_id,
                    'product_name'=> $first->product->title ?? '',
                    'image'       => $first->product->image ?? '',
                    'image_thumb' => ! empty($first->product->image) ? Helper::getImageVariants($first->product->image)['thumb'] : '',
                    'price'       => $first->price,
                    'quantity'    => (string) $totalQty,
                    'total_price' => (string) $totalPrice,
                ];
            })->values();
        } else {
            $grouped = $cartItems->map(function ($item) {
                return [
                    'cart_id'    => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->title ?? '',
                    'image'      => $item->product->image ?? '',
                    'image_thumb'=> ! empty($item->product->image) ? Helper::getImageVariants($item->product->image)['thumb'] : '',
                    'price'      => $item->price,
                    'quantity'   => (string) $item->quantity,
                    'total_price'=> (string) ($item->price * $item->quantity),
                ];
            });
        }
    
        // Totals
   // ✅ Subtotal calculate karo
    $subtotal = $grouped->sum(function ($item) {
        return (float) $item['total_price'];
    });

    // ✅ Tax logic
    $tax = 0;

    if (!empty($taxAmt['tax_amount_per'])) {
        $tax = ($subtotal * $taxAmt['tax_amount_per']) / 100;
    } elseif (!empty($taxAmt['tax_amount'])) {
        $tax = $taxAmt['tax_amount'];
    }
    
    $platform_fee = $taxAmt['platform_fee'];
    $shipping_charge = $taxAmt['shipping_charges'];
    
    $total = $subtotal + $tax + $platform_fee + $shipping_charge ;
    
        return response()->json([
            'status' => true,
            'message' => 'Cart fetched successfully.',
            'data' => [
                'role'     => $user->role,
                'items'    => $grouped,
                'subtotal' => (string) $subtotal,
                'tax'      => (string) $tax,
                'platform_fee'=>(string)$platform_fee,
                'shipping_charge'=>(string)$shipping_charge,
                'total'    => (string) $total,
            ]
        ]);
    }
    
    /**
     * update quantity
     */ 
    public function updateQuantity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id'    => 'required|integer|exists:add_to_carts,id',
            'product_id' => 'required|integer|exists:products,id',
            'action'     => 'required|string|in:increment,decrement',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }
        
        $user = auth()->user();
        
        $cartItem = AddToCart::where('id', $request->cart_id)
                    ->where('product_id', $request->product_id)
                    ->first();
                    
        if (!$cartItem) {
            return Helper::responseFunction(false, 404, 'Cart item not found.', null, 404);
        }
        
        // Decide new quantity
        $newQty = $request->action === 'increment' 
                    ? $cartItem->quantity + 1 
                    : $cartItem->quantity - 1;
    
        if ($newQty < 1) {
            return Helper::responseFunction(false, 422, 'Minimum quantity should be one', null, 422);
        }
                    
        $cartItem->update(['quantity' => $newQty]);

    
        return Helper::responseFunction(true, 200, 'Quantity updated successfully!', $cartItem, 200);
    }
    
    private function appendProductImageVariants($product)
    {
        $variants = Helper::getImageVariants($product->image);
        $product->image_medium = $variants['medium'];
        $product->image_thumb = $variants['thumb'];
        
        return $product;
    }

    private function shopUserConstraint($user): \Closure
    {
        return function ($query) use ($user) {
            if ($user->role === 'parent') {
                $query->where('parent_id', $user->id)
                    ->whereNull('kid_id')
                    ->where('role', 'parent');
            } elseif ($user->role === 'kid') {
                $query->where('kid_id', $user->id)
                    ->where('parent_id', $user->parent_id)
                    ->where('role', 'kid');
            }
        };
    }

    private function mapShopListProducts($products)
    {
        return $products
            ->map(fn ($product) => $this->shopListProductPayload($product))
            ->values()
            ->all();
    }

    private function shopListProductPayload($product)
    {
        $variants = Helper::getImageVariants($product->image);
        $stripped = html_entity_decode(
            strip_tags((string) $product->description),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
        $plain = trim((string) preg_replace('/\s+/u', ' ', $stripped));
        if (mb_strlen($plain) > 140) {
            $plain = mb_substr($plain, 0, 140).'…';
        }

        return [
            'id' => $product->id,
            'product_cat_id' => $product->product_cat_id,
            'title' => $product->title,
            'image' => $product->image,
            'image_medium' => $variants['medium'],
            'image_thumb' => $variants['thumb'],
            'description' => $plain,
            'quantity' => $product->quantity,
            'selling_price' => $product->selling_price,
            'discount_price' => $product->discount_price,
            'is_favourite' => (bool) $product->is_favourite,
            'is_cart_added' => (bool) $product->is_cart_added,
            'cart_quantity' => (int) $product->cart_quantity,
        ];
    }

}
