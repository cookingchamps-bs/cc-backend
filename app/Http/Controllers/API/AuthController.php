<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordMail;
use App\Models\Kid;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Models\FcmToken;
use App\Models\FollowRequest;
use App\Services\SocialAuthVerifier;
use App\Support\PasswordRules;
use Carbon\Carbon;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // register parent
    public function registerParent(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            // 'role' => 'required|in:parent',
            'first_name' => 'required',
            'last_name'  => 'required',
            'dob'        => 'nullable',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->whereNull('deleted_at'),
            ],
            'country_code'  => 'required',
            'mobile_number' => 'required|numeric|digits_between:9,15',
            // 'image'      => 'required|mimes:jpeg,jpg,png',
            'password'      => ['required', 'confirmed', PasswordRules::parent()],
            'fcm_token'     => 'nullable|string',
            'device_id'     => 'required',
            'device_type'   => 'required|in:android,ios,ipad',
            // 'confirm_password' => 'required|min:6|same:password'
        ]);

        if ($validateUser->fails()) {
            return Helper::validationResponse($validateUser, 200);
        }

        $deviceType = $request->device_type === 'ipad' ? 'ios' : $request->device_type;

        $user = User::create([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'dob'           => $request->dob ?? null,
            // 'role'       => $request->role,
            'email'         => $request->email,
            // 'image'      => Helper::handleFileUpload($request, 'image', 'image', $existingPath = null),
            'country_code'  => $request->country_code,
            'mobile_number' => $request->mobile_number,
            'password'      => Hash::make($request->password),
        ]);

        // Create token
        $user->token = $user->createToken('cooking-champs')->plainTextToken;

        FcmToken::create([
            'user_id'     => $user->id,
            'child_id'    => null,
            'role'        => $request->role ?? 'parent',
            'fcm_token'   => $request->fcm_token ?: 'fcm_unavailable',
            'device_id'   => $request->device_id,
            'device_type' => $deviceType,
        ]);

        return Helper::responseFunction(true, 200, 'Parent profile created successfully!', $user, 200);
    }

    // kids registration (parent token only; parent_id comes from auth)
    public function registerKid(Request $request)
    {
        $parent = $request->user();
        if (! $parent instanceof User) {
            return Helper::responseFunction(false, 403, 'Not authorized.', null, 403);
        }

        $validateKid = Validator::make($request->all(), [
            'role'      => 'required|in:kid',
            'name'      => 'required|string|max:255',
            'image'     => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'avatar'    => 'nullable',
            // 'day'    => 'nullable|integer|between:1,31',
            'day'       => 'nullable',
            'month'     => 'nullable|string',
            'year'      => 'required|integer',
            'grade'     => 'required',
            'username'  => [
                'required',
                Rule::unique('kids')->whereNull('deleted_at'),
            ],
            'password'  => ['required', PasswordRules::kid()],
            'fcm_token' => 'nullable|string',
            'device_id' => 'required',
            'device_type'=> 'required|in:android,ios,ipad',
        ]);

        if ($validateKid->fails()) {
            return Helper::validationResponse($validateKid, 200);
        }
        
        if($request->filled('avatar')) {
            $avatarPath = $request->avatar;
        }

        $deviceType = $request->device_type === 'ipad' ? 'ios' : $request->device_type;

        $kids = Kid::create([
            'parent_id' => $parent->id,
            'name'      => $request->name,
            'role'      => $request->role,
            'kid_image' => $request->avatar ? $avatarPath :  Helper::handleFileUpload($request, 'image', 'kid_image', $existingPath = null),
            'day'       => $request->day,
            'month'     => $request->month,
            'year'      => $request->year,
            'grade'     => $request->grade,
            'username'  => $request->username,
            'password'  => Hash::make($request->password),
        ]);

        // Create token
        $kids->token = $kids->createToken('cooking-champs')->plainTextToken;

        FcmToken::create([
            'user_id'     => $parent->id,
            'child_id'    => $kids->id,
            'role'        => $request->role,
            'fcm_token'   => $request->fcm_token ?: 'fcm_unavailable',
            'device_id'   => $request->device_id,
            'device_type' => $deviceType,
        ]);

        $msg = 'Child profile created successfully!';

        return Helper::responseFunction(true, 200, $msg, $kids, 200);
    }

    /**
     * Check whether a kid username is available.
     * Optional kid_id ignores that kid (for profile edit).
     */
    public function checkKidUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'kid_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }

        $username = trim((string) $request->username);
        $query = Kid::where('username', $username)->whereNull('deleted_at');

        if ($request->filled('kid_id')) {
            $query->where('id', '!=', $request->kid_id);
        }

        $available = ! $query->exists();

        return Helper::responseFunction(
            true,
            200,
            $available ? 'Username is available.' : 'Username is already taken.',
            [
                'available' => $available,
                'username' => $username,
            ],
            200
        );
    }

    // Login
    public function login(Request $request)
    {
        // validation rules
        $rules = [
            $request->has('email') && ! is_null($request->email) ? 'email' : 'username' => 'required',
            'password' => 'required',
        ];

        if ($request->has('email') && ! is_null($request->email)) {
            $rules['email'] .= '|email|exists:users';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }

        // Authenticate user based on email or username
        $authAttempt = $request->has('email') && ! is_null($request->email) ?
            Auth::attempt(['email' => $request->email, 'password' => $request->password]) :
            Auth::guard('kid')->attempt(['username' => $request->username, 'password' => $request->password]);

        if (! $authAttempt) {
            return Helper::responseFunction(false, 200, 'Invalid Credentials!', null, 401);
        }

        // Get the authenticated user
        $user = $request->has('email') && ! is_null($request->email) ? Auth::user() : Auth::guard('kid')->user();
        
        if ($user->role == 'parent') {
            $user['name'] = $user->first_name.' '.$user->last_name;
        }

        // Kid image is already normalized via Kid::getImageAttribute (appended as `image`).
        // Do not overwrite with raw kid_image (breaks public/ prefix and defaults).

        $userTypeKey = $request->has('email') && ! is_null($request->email) ? 'user_id' : 'child_id';

        // Update or create FCM token
        $fcmToken = FcmToken::updateOrCreate(
            ['user_id' => $user->role == 'parent' ? $user->id : $user->parent_id, 'role' => $user->role, 'device_id' => $request->device_id],
            [
                'user_id'     => $user->role == 'parent' ? $user->id : $user->parent_id,
                'child_id'    => $user->role == 'kid' ? $user->id : null,
                'device_id'   => $request->device_id,
                'device_type' => $request->device_type,
                'fcm_token'   => $request->fcm_token,
                'role'        => $user->role,
            ]
        );

        // Append FCM token data to the user
        $fcmData = FcmToken::where($userTypeKey, $user->id)->first();
        $user->fcm_token   = $fcmData->fcm_token ?? '';
        $user->device_id   = $fcmData->device_id ?? '';
        $user->device_type = $fcmData->device_type ?? '';

        // Create token
        $user->token = $user->createToken('cooking-champs')->plainTextToken;
        
        if(is_null($user->role)) {
            return Helper::responseFunction(true, 200, 'Registration Incomplete, please complete all steps for login!', $user, 200);
        }

        return Helper::responseFunction(true, 200, 'Login Successful!', $user, 200);
    }
    
    /**
     * update roles
     */
    public function updateRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:parent,individual',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }

        $user = auth()->user();

        // Kids must not escalate to parent/individual accounts.
        if ($user instanceof Kid || ($user->role ?? null) === 'kid') {
            return Helper::responseFunction(false, 200, 'Not authorized to update role.', null, 403);
        }

        if (! $user instanceof User) {
            return Helper::responseFunction(false, 200, 'Not authorized to update role.', null, 403);
        }

        // Only allow during onboarding or for existing parent accounts adjusting sub_role.
        $currentRole = $user->role;
        if (! empty($currentRole) && $currentRole !== 'parent') {
            return Helper::responseFunction(false, 200, 'Role can only be set during onboarding.', null, 403);
        }

        // Persist explicitly so switching individual → parent clears sub_role.
        // role column stays 'parent'; request "individual" is stored as sub_role.
        $user->role = 'parent';
        $user->sub_role = $request->role === 'individual' ? 'individual' : null;
        $user->save();
        $user->refresh();

        return Helper::responseFunction(true, 200, 'Role updated successfully!', $user, 200);
    }
    
    
    /**
     * social login api
     */ 
    public function socialLogin(Request $request, SocialAuthVerifier $socialAuthVerifier)
    {
        try {
            $validator = Validator::make($request->all(), [
                'login_type'   => 'required|in:google,apple,Google,Apple',
                'id_token'     => 'required|string',
                'first_name'   => 'nullable|min:2|max:255',
                'last_name'    => 'nullable|min:2|max:255',
                'role'         => 'required',
                'image'        => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'fcm_token'    => 'required',
                'device_id'    => 'required',
                'device_type'  => 'required|in:android,ios',
            ]);

            if ($validator->fails()) {
                return Helper::validationResponse($validator, 200);
            }

            $verified = $socialAuthVerifier->verify((string) $request->login_type, (string) $request->id_token);
            if ($verified === null) {
                return Helper::responseFunction(false, 401, 'Invalid or expired social login token.', null, 401);
            }

            $email = $verified['email'];
            $user = User::where('email', $email)->first();

            if (! $user) {
                $user = User::create([
                    'login_type'   => strtolower((string) $request->login_type),
                    'first_name'   => $request->first_name,
                    'last_name'    => $request->last_name,
                    'role'         => $request->role,
                    'email'        => $email,
                    'image'        => $request->hasFile('image')
                        ? Helper::handleFileUpload($request, 'image', 'image', $existingPath = null)
                        : 'uploads/placeholder/default_user.png',
                    'password'     => Hash::make(Str::random(64)),
                ]);
            } elseif (Hash::check('123456', $user->password)) {
                $user->update(['password' => Hash::make(Str::random(64))]);
            }

            $user->token = $user->createToken('cooking-champs')->plainTextToken;

            FcmToken::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'role' => $request->role,
                    'device_id' => $request->device_id,
                ],
                [
                    'user_id'     => $user->id,
                    'child_id'    => null,
                    'role'        => $request->role,
                    'fcm_token'   => $request->fcm_token,
                    'device_id'   => $request->device_id,
                    'device_type' => $request->device_type,
                ]
            );

            return response()->json(['status' => true, 'status_code' => 200, 'message' => 'Login Successful', 'data' => $user], 200);
        } catch (\Exception $e) {
            Log::error('socialLogin failed', ['error' => $e->getMessage()]);

            return response()->json(['status' => false, 'message' => 'Unable to complete social login.'], 422);
        }
    }

    /**
     * Parent logout api
     */
    public function logout(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'device_id' => 'required',
            ]);

            if ($validator->fails())  return Helper::validationResponse($validator, 200);
            
            $user = Auth::user();
            $user->currentAccessToken()->delete();

            // delete token
            FcmToken::where([
                'device_id' => $request->device_id,
                'role'      => $user->role,
            ])->forceDelete();

            return Helper::responseFunction(true, 200, 'Logout Successfully!', null, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * user detail api
     */
    public function userDetail(Request $request)
    {
        try {
            $user = Auth::user();
            
            if ($user->role === 'parent') {
                $user['name'] = $user->first_name.' '.$user->last_name;
                
                $followersCount = FollowRequest::where('receiver_id', $user->id)
                                        ->where('follow_req_sent_to', 'parent')
                                        ->count();
            
                $IamfollowingCount = FollowRequest::where('sender_id', $user->id)
                                        ->where('follow_req_sent_by', 'parent')
                                        ->count();
            }
            
            if ($user->role === 'kid') {
                // Keep normalized accessor value (`image`), do not replace with raw kid_image.
                
                $followersCount = FollowRequest::where('receiver_id', $user->id)
                                        ->where('follow_req_sent_to', 'kid')
                                        ->count();
                                        
                $IamfollowingCount = FollowRequest::where('sender_id', $user->id)
                                        ->where('follow_req_sent_by', 'kid')
                                        ->count();
            }

            $data = $user->load('userSubscription');
            
            if ($request->has('kid_id')) {
                $data = Kid::with('userSubscription')->where('id', $request->kid_id)
                    ->where('parent_id', $user->id)
                    ->first();

                if (empty($data)) return Helper::responseFunction(true, '404', 'Kid not found!', [], 404);
                
                // Kid model already appends normalized `image`.
            }
            
            $data['followers'] = (string)$followersCount;
            $data['following'] = (string)$IamfollowingCount;

            return Helper::responseFunction(true, 200, 'Profile retrieved successfully!', $data, 200);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 422, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * update profile api
     */
public function updateProfile(Request $request)
    {
        try {
          
            $userId = Auth::id();

            $rules = [
                'first_name'    => 'nullable|string',
                'last_name'     => 'nullable|string',
                'email'         => [
                    'nullable',
                    'email',
                    Rule::unique('users', 'email')->whereNull('deleted_at')->ignore($userId),
                ],
                'dob'           => 'nullable|date',
                'country_code'  => 'nullable|string',
                'mobile_number' => 'nullable|numeric|digits_between:9,15',
                'image'         => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'avatar'        => 'nullable',
                'kid_id'        => 'nullable|integer',
                'name'          => 'nullable|string|max:255',
                //   'kid_image'  => 'nullable|image|mimes:jpeg,jpg,png',
                'day'           => 'nullable|integer|between:1,31',
                'month'         => 'nullable|string',
                'year'          => 'nullable|integer',
                'grade'         => 'nullable',
                'password'      => ['nullable', PasswordRules::kid()],
                'username'      => $request->has('kid_id')
                    ? [
                        'nullable',
                        Rule::unique('kids', 'username')
                            ->whereNull('deleted_at')
                            ->ignore($request->kid_id),
                    ]
                    : 'nullable',
            ];
 
            $validation = Validator::make($request->all(), $rules);
            if ($validation->fails()) {
                return Helper::validationResponse($validation, 200);
            }
            
            if($request->filled('avatar')) {
                $avatarPath = $request->avatar;
            }
 
            if ($request->has('kid_id')) {
                $authUser = Auth::user();
                if ($authUser instanceof Kid) {
                    if ((int) $request->kid_id !== (int) $authUser->id) {
                        return Helper::responseFunction(false, 403, 'Not authorized to edit this profile.', null, 403);
                    }
                    $kid = $authUser;
                } else {
                    $kid = Kid::where('id', $request->kid_id)
                        ->where('parent_id', $authUser->id)
                        ->first();
                }

                if (empty($kid)) {
                    return Helper::responseFunction(false, 404, 'User not found!', [], 404);
                }
                
                // if($request->has('kid_image')){
                //     return "kid image";
                // }
                
                $kidData = [
                    'name'      => $request->name ?? $kid->name,
                    'kid_image' => $request->filled('avatar') ? $avatarPath : Helper::handleFileUpload($request, 'image', 'kid_image', $kid->image),
                    'day'       => $request->day ?? $kid->day,
                    'month'     => $request->month ?? $kid->month,
                    'year'      => $request->year ?? $kid->year,
                    'grade'     => $request->grade ?? $kid->grade,
                    'username'  => $request->username ?? $kid->username,
                ];

                if ($request->filled('password')) {
                    $kidData['password'] = Hash::make($request->password);
                }

                $kid->update($kidData);

                return Helper::responseFunction(true, 200, 'Profile updated successfully!', $kid, 200);
            }
 
            $user = Auth::user();

            // PHP may drop oversized uploads before Laravel sees them.
            if ($request->hasFile('image') === false && $request->file('image') === null) {
                $uploadError = $_FILES['image']['error'] ?? null;
                if ($uploadError !== null && (int) $uploadError !== UPLOAD_ERR_NO_FILE && (int) $uploadError !== UPLOAD_ERR_OK) {
                    return Helper::responseFunction(
                        false,
                        400,
                        'Image upload failed. Please use a JPG/PNG under 5MB.',
                        null,
                        400
                    );
                }
            }

            $imagePath = $request->hasFile('image')
                ? Helper::handleFileUpload($request, 'image', 'image', $user->getRawOriginal('image'))
                : $user->getRawOriginal('image');

            $user->update([
                'first_name'    => $request->first_name ?? $user->first_name,
                'last_name'     => $request->last_name ?? $user->last_name,
                'email'         => $request->email ?? $user->email,
                'dob'           => $request->dob ?? $user->dob,
                'image'         => $imagePath,
                'country_code'  => $request->country_code ?? $user->country_code,
                'mobile_number' => $request->mobile_number ?? $user->mobile_number,
            ]);

            $user->refresh();

            return Helper::responseFunction(true, 200, 'Profile updated successfully!', $user, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 422, 'message' => $e->getMessage()], 422);
        }
    }
 
 
    // file upload function
    private function handleFileUpload(Request $request, $fileKey, $storagePath, $existingPath = null)
    {
        return Helper::handleFileUpload($request, $fileKey, $storagePath, $existingPath);
    }

    /**
     *  change password api
    */
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => ['required', 'confirmed', PasswordRules::parent()],
            ]);

            if ($validator->fails()) {
                return Helper::validationResponse($validator, 200);
            }

            $user = auth()->user();
            if ($user instanceof Kid) {
                return Helper::responseFunction(false, 403, 'Not authorized.', null, 403);
            }

            if (! Hash::check($request->current_password, $user->password)) {
                return Helper::responseFunction(false, 200, 'Current password is incorrect.', null, 400);
            }

            if (Hash::check($request->new_password, $user->password)) {
                return Helper::responseFunction(false, 200, 'New password must be different from the current password.', null, 400);
            }

            $user->update(['password' => Hash::make($request->new_password)]);
            $user->tokens()->delete();

            return Helper::responseFunction(true, 200, 'Password changed successfully!', $user, 200);
        } catch (\Exception $e) {
            Log::error('changePassword failed', ['error' => $e->getMessage()]);

            return response()->json(['status' => false, 'status_code' => 422, 'message' => 'Unable to change password.'], 422);
        }

    }

    /**
     * delete account api
     */
    public function deleteAccount(Request $request)
    {
        try {
            $user = auth::user();

            $imagePath = $user->getRawOriginal('image') ?? $user->getAttributes()['image'] ?? null;
            if (! empty($imagePath)) {
                $relative = ltrim(str_replace(['public/storage/', 'storage/'], '', $imagePath), '/');
                $publicFile = public_path('storage/'.$relative);
                if (is_file($publicFile)) {
                    @unlink($publicFile);
                }
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($relative)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($relative);
                }
            }

            $user->delete();

            return Helper::responseFunction(true, 200, 'Account deleted successfully!', null, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 422, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * forgot password api
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return Helper::validationResponse($validator, 200);
            }

            $email = strtolower((string) $request->email);
            $user = User::where('email', $email)->first();
            if (! $user) {
                return Helper::responseFunction(false, 200, 'Email not found please enter valid mail!', null, 200);
            }

            $otp = (string) random_int(100000, 999999);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => $otp, 'created_at' => now()]
            );

            Cache::forget($this->passwordResetFailureCacheKey($email));

            try {
                Mail::to($email)->send(new ForgotPasswordMail($otp, $user->first_name));
            } catch (\Exception $e) {
                Log::error('forgotPassword mail failed', ['error' => $e->getMessage()]);

                return response()->json(['status' => false, 'status_code' => 500, 'message' => 'Failed to send OTP email.'], 500);
            }

            return Helper::responseFunction(true, 200, 'Otp send successfully to your registered mail!', null, 200);
        } catch (\Exception $e) {
            Log::error('forgotPassword failed', ['error' => $e->getMessage()]);

            return response()->json(['status' => false, 'status_code' => 422, 'message' => 'Unable to process request.'], 422);
        }
    }

    /**
     * otp verification api
     */
    public function otpVerify(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'otp' => 'required|digits:6',
            ]);

            if ($validator->fails()) {
                return Helper::validationResponse($validator, 200);
            }

            $email = strtolower((string) $request->email);
            if ($this->isPasswordResetLocked($email)) {
                return Helper::responseFunction(false, 429, 'Too many failed attempts. Try again later.', null, 429);
            }

            $tokenRow = PasswordResetToken::where('email', $email)->where('token', $request->otp)->first();
            if (! $tokenRow) {
                $this->recordPasswordResetFailure($email);

                return Helper::responseFunction(false, 401, 'Incorrect OTP or otp not found', null, 401);
            }

            $expirationTime = Carbon::parse($tokenRow->created_at)->addMinutes(15);
            if (now()->greaterThan($expirationTime)) {
                return Helper::responseFunction(false, 401, 'OTP has expired. Please request a new one!', null, 401);
            }

            Cache::forget($this->passwordResetFailureCacheKey($email));

            return Helper::responseFunction(true, 200, 'OTP verified successfully!', null, 200);
        } catch (\Exception $e) {
            Log::error('otpVerify failed', ['error' => $e->getMessage()]);

            return response()->json(['status' => false, 'status_code' => 422, 'message' => 'Unable to verify OTP.'], 422);
        }
    }

    /**
     * reset password api
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|digits:6',
                'new_password' => ['required', 'confirmed', PasswordRules::parent()],
            ]);

            if ($validator->fails()) {
                return Helper::validationResponse($validator, 200);
            }

            $email = strtolower((string) $request->email);
            if ($this->isPasswordResetLocked($email)) {
                return Helper::responseFunction(false, 429, 'Too many failed attempts. Try again later.', null, 429);
            }

            $tokenRow = PasswordResetToken::where('email', $email)->where('token', $request->otp)->first();
            if (! $tokenRow) {
                $this->recordPasswordResetFailure($email);

                return Helper::responseFunction(false, 401, 'Invalid or expired OTP.', null, 401);
            }

            $expirationTime = Carbon::parse($tokenRow->created_at)->addMinutes(15);
            if (now()->greaterThan($expirationTime)) {
                PasswordResetToken::where('email', $email)->delete();

                return Helper::responseFunction(false, 401, 'OTP has expired. Please request a new one!', null, 401);
            }

            $user = User::where('email', $email)->first();
            if (! $user) {
                return Helper::responseFunction(false, 400, 'Email not found, please enter valid email', null, 400);
            }

            if (Hash::check($request->new_password, $user->password)) {
                return Helper::responseFunction(false, 400, 'New password must be different from the old password.', null, 400);
            }

            $user->update(['password' => Hash::make($request->new_password)]);
            $user->tokens()->delete();
            PasswordResetToken::where('email', $email)->delete();
            Cache::forget($this->passwordResetFailureCacheKey($email));

            return Helper::responseFunction(true, 200, 'Password reset successfully!', null, 200);
        } catch (\Exception $e) {
            Log::error('resetPassword failed', ['error' => $e->getMessage()]);

            return response()->json(['status' => false, 'status_code' => 422, 'message' => 'Unable to reset password.'], 422);
        }

    }

    private function passwordResetFailureCacheKey(string $email): string
    {
        return 'password_reset_failures:'.hash('sha256', $email);
    }

    private function isPasswordResetLocked(string $email): bool
    {
        return (int) Cache::get($this->passwordResetFailureCacheKey($email), 0) >= 5;
    }

    private function recordPasswordResetFailure(string $email): void
    {
        $key = $this->passwordResetFailureCacheKey($email);
        $attempts = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, now()->addMinutes(30));
    }
}
