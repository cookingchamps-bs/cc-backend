<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;

use Illuminate\Http\Request;
use App\Helpers\Helper;

use Illuminate\Support\Facades\{Auth,Hash};

class VendorAuthController extends Controller
{
    // vendor index
    public function vendorDashboard()
    {
      $user =   Auth::guard('vendor')->id();
      
      $data = Product::where('vendor_id',$user)->count();
      
        
        return view('admin.vendor_dashboard',compact('data'));
    }
    
    /* ---------- vendor login ------------ */
    public function vendorLogin(Request $request)
    {
        if ($request->isMethod('post')) {
            $credentials = $request->validate([
                'username' => 'required|string',
                'password' => 'required',
            ]);

            $remember = $request->boolean('remember');

            if (Auth::guard('vendor')->attempt($credentials, $remember)) {
                $request->session()->regenerate();
                $this->forgetInsecureCredentialCookies();

                return redirect()->route('vendor.dashboard');
            }

            return back()->with('error_message', 'Invalid Credentials!');
        }

        $this->forgetInsecureCredentialCookies();

        return view('admin.vendor_login');
    }
    
    /* ------ Vendor logout ---- */
    public function vendorLogout(Request $request)
    {
        Auth::guard('vendor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $this->forgetInsecureCredentialCookies();

        return redirect()->route('vendor.login');
    }

    private function forgetInsecureCredentialCookies(): void
    {
        foreach (['email', 'password', 'username'] as $name) {
            if (isset($_COOKIE[$name])) {
                setcookie($name, '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
                unset($_COOKIE[$name]);
            }
        }
    }
}
