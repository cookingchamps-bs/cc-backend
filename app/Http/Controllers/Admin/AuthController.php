<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Admin;
use Illuminate\Support\Facades\{Auth,Hash};
use Storage;

class AuthController extends Controller
{
    /* ----- admin login ----*/
    public function Login(Request $request)
    {
        if ($request->isMethod('post')) {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $remember = $request->boolean('remember');

            if (Auth::guard('admin')->attempt($credentials, $remember)) {
                $request->session()->regenerate();
                $this->forgetInsecureCredentialCookies();

                return redirect()->route('admin.dashboard');
            }

            return back()->with('error_message', 'Invalid Credentials!');
        }

        $this->forgetInsecureCredentialCookies();

        return view('admin.login');
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

    /* --- view profile --- */
    public function viewProfile(Request $request)
    {
        $common = [];
        $common['title'] = 'View Profile';
        $common['button'] = 'Update';
        $adminDetails = Auth::guard('admin')->user();
        abort_unless($adminDetails, 403);

        if ($request->isMethod('post')) {

            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email',
                'image' => 'nullable|mimes:jpeg,jpg,png',
            ]);

            $Imagepath = $adminDetails->image;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $file->storeAs('admin_logo', $fileName, 'public');
                $Imagepath = 'storage/admin_logo/'.$fileName;
            }

            $adminDetails->name = $request->name;
            $adminDetails->email = $request->email;
            $adminDetails->image = $Imagepath;
            $adminDetails->save();

            $notification = [
                'message' => 'Profile Updated Successfully!',
                'alert-type' => 'success',
            ];

            return Helper::redirectFunction('admin.profile', $notification);

        }

        return view('admin.profile', compact('common', 'adminDetails'));
    }

    /* --- change password --- */
    public function changePassword(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'current_pwd' => 'required|string',
                'new_pwd' => 'required|string|min:6|same:confirm_pwd',
                'confirm_pwd' => 'required|string|min:6',
            ]);

            $admin = Auth::guard('admin')->user();
            abort_unless($admin, 403);

            if (! Hash::check($data['current_pwd'], $admin->password)) {
                return back()->with('error_message', 'Old Password is Incorrect!');
            }

            if (Hash::check($data['new_pwd'], $admin->password)) {
                return back()->with('error_message', 'Your New Password must be different from previously used Password');
            }

            $admin->password = Hash::make($data['new_pwd']);
            $admin->save();

            return redirect()->back()->with('success_message', 'Password Updated Successfully!');
        }

        return view('admin.change_password');
    }

    /* --- check current password via ajax-- */
    public function CheckCurrentPassword(Request $request)
    {
        $data = $request->all();
        if (Hash::check($data['current_pwd'], Auth::guard('admin')->user()->password)) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /* ------ logout ---- */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $this->forgetInsecureCredentialCookies();

        return redirect()->route('admin.login');
    }
    
    // upload summernote Img
    public function uploadSummernoteImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image'
        ]);
        

        if ($request->hasFile('image')) {
            $path = Helper::handleFileUpload($request, 'image', 'uploads/summernote', null);

            return response()->json(['image_url' => Helper::mediaAsset($path)]);
        }
    
        return response()->json(['error' => 'No image uploaded'], 400);
    }

    public function getOpenAiApiKey()
    {
        $data = Admin::first();
        $hasKey = filled($data?->openai_api_key);

        return view('admin.openai_api_key', compact('data', 'hasKey'));
    }

    public function storeOpenAiApiKey(Request $request)
    {
        $request->validate([
            'openai_api_key' => 'nullable|string|max:500',
            'clear_openai_api_key' => 'nullable|boolean',
        ]);

        $admin = Auth::guard('admin')->user() ?? Admin::first();
        abort_unless($admin, 404);

        if ($request->boolean('clear_openai_api_key')) {
            $admin->openai_api_key = null;
            $admin->save();

            return back()->with('success_message', 'OpenAI API key cleared successfully');
        }

        $apiKey = trim((string) $request->openai_api_key);
        // Empty submit keeps the existing key (do not echo/replace with blank accidentally).
        if ($apiKey !== '') {
            $admin->openai_api_key = $apiKey;
            $admin->save();
        }

        return back()->with('success_message', 'OpenAI API key saved successfully');
    }

    public function getStripeKeys()
    {
        $common = Helper::getCommonData('Stripe keys', 'Stripe keys', 'Save');
        $data = Admin::first();
        $hasPublishableKey = filled($data?->stripe_key);
        $hasSecretKey = filled($data?->stripe_secret);
        $usingDatabaseKeys = $hasPublishableKey || $hasSecretKey;
        $usingEnvFallback = ! $usingDatabaseKeys && (
            filled(config('services.stripe.STRIPE_KEY')) ||
            filled(config('services.stripe.STRIPE_SECRET'))
        );

        return view('admin.stripe_keys', compact(
            'common',
            'data',
            'hasPublishableKey',
            'hasSecretKey',
            'usingDatabaseKeys',
            'usingEnvFallback'
        ));
    }

    public function storeStripeKeys(Request $request)
    {
        $request->validate([
            'stripe_key' => ['nullable', 'string', 'max:500', 'regex:/^pk_(test|live)_/'],
            'stripe_secret' => ['nullable', 'string', 'max:500', 'regex:/^sk_(test|live)_/'],
            'clear_stripe_key' => 'nullable|boolean',
            'clear_stripe_secret' => 'nullable|boolean',
        ], [
            'stripe_key.regex' => 'Publishable key must start with pk_test_ or pk_live_.',
            'stripe_secret.regex' => 'Secret key must start with sk_test_ or sk_live_.',
        ]);

        $admin = Auth::guard('admin')->user() ?? Admin::first();
        abort_unless($admin, 404);

        $cleared = false;

        if ($request->boolean('clear_stripe_key')) {
            $admin->stripe_key = null;
            $cleared = true;
        }

        if ($request->boolean('clear_stripe_secret')) {
            $admin->stripe_secret = null;
            $cleared = true;
        }

        $publishableKey = trim((string) $request->stripe_key);
        if ($publishableKey !== '' && ! $request->boolean('clear_stripe_key')) {
            $admin->stripe_key = $publishableKey;
        }

        $secretKey = trim((string) $request->stripe_secret);
        if ($secretKey !== '' && ! $request->boolean('clear_stripe_secret')) {
            $admin->stripe_secret = $secretKey;
        }

        $admin->save();

        if ($cleared && $publishableKey === '' && $secretKey === '') {
            return back()->with('success_message', 'Stripe keys cleared successfully');
        }

        return back()->with('success_message', 'Stripe keys saved successfully');
    }

}
