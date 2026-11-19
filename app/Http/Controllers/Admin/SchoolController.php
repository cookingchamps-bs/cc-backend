<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\{Auth,Hash,Log};
use App\Models\SchoolVideo;
use App\Helpers\Helper;

use Illuminate\Support\Facades\Storage;
  
class SchoolController extends Controller
{

    public function getSchool(){
        $data = School::withCount('videos')->latest()->get();

        return view('admin.school.index', compact('data'));
    }
    
    public function addSchool(){
        
    $title = "Add-School" ;   
    return view('admin.school.add',compact('title'));    
    }
 
    public function storeSchool(Request $request)
    {
        $request->validate([
            'school_name' => 'required',
            'school_logo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'contact_person_name' => 'required',
            'email' => 'required|email|unique:schools,email',
        ]);
    
        $data = [
            'school_name' => $request->school_name,
            'contact_person_name' => $request->contact_person_name,
            'email' => $request->email,
            'password' => null,
            'status' => 1,
        ];
    
        if ($request->hasFile('school_logo')) {
            $file = $request->file('school_logo');
            $filename = time().'_'.preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->storeAs('schools', $filename, 'public');
            $data['school_logo'] = 'storage/schools/'.$filename;
        }
 
        $school = School::create($data);
    
     
           Password::broker('schools')->sendResetLink([
            'email' => $school->email
        ]);
        return back()->with('success', 'School added & email sent');
    }
    

    public function create(Request $request, string $token)
    {
        return view('admin.password.reset_password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function store(Request $request)
    {
       
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);
    
        
        $status = Password::broker('schools')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($school, $password) {
    
                
                $school->password = Hash::make($password);
                $school->save();
            }
        );
    
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('school.login')->with('success', 'Password set successfully')
            : back()->withErrors(['email' => 'Invalid token or email']);
    }


    public function  editSchool($id)
    {
        $school = School::findOrFail($id);
        $title = 'Edit School Data';
        return view('admin.school.edit', compact('school', 'title'));
    }
    
    public function storeSchoolData(Request $request, $id){
        
        $school = School::findOrFail($id);

        $request->validate([
            'school_name' => 'required',
            'contact_person_name' => 'nullable',
            'school_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // update fields (email intentionally skipped)
        $school->school_name = $request->school_name;
        $school->contact_person_name = $request->contact_person_name;

        // logo upload
        if ($request->hasFile('school_logo')) {
            Helper::deletePublicFile($school->getRawOriginal('school_logo') ?? null);

            $file = $request->file('school_logo');
            $filename = time().'_'.preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->storeAs('schools', $filename, 'public');
            $school->school_logo = 'storage/schools/'.$filename;
        }

        $school->save();

        return redirect()
            ->back()
            ->with('success', 'School updated successfully');
    }
        

    public function destroy($id)
    {
        $school = School::findOrFail($id);

        $school->delete();
    
        return redirect()->back()->with('success', 'School deleted successfully');
    }
     
    
    public function viewSchool(string $id)
    {
        $school = School::findOrFail($id);
        $videos = SchoolVideo::where('school_id', $school->id)->latest()->get();

        return view('admin.school.view', compact('school', 'videos'));
    }

    //*********************************** school dashboard login **************************************************
    public function schoolAdminLogin(request $request){
        if ($request->isMethod('post')) {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $school = School::where('email', $credentials['email'])->first();
            if ($school && !$school->isAccountActive()) {
                return back()->with('error_message', 'This school account has been deactivated. Please contact support.');
            }

            $remember = $request->boolean('remember');

            if (Auth::guard('school')->attempt($credentials, $remember)) {
                $user = Auth::guard('school')->user();
                if ($user && !$user->isAccountActive()) {
                    Auth::guard('school')->logout();

                    return back()->with('error_message', 'This school account has been deactivated. Please contact support.');
                }

                $request->session()->regenerate();
                $this->forgetInsecureCredentialCookies();

                return redirect()->route('school.dashboard');
            }

            return back()->with('error_message', 'Invalid Credentials!');
        }

        $this->forgetInsecureCredentialCookies();

        return view('school_admin.login');
    }
    
    // school 
    public function schoolDashboard(){
     
        $user = Auth::guard('school')->user();
        $userId = $user->id; 
        $data = SchoolVideo::where('school_id',$userId)->count();
        return view('school_admin.dashboard',compact('data')); 
    }
    
    public function schoolLogout(request $request){
        Auth::guard('school')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $this->forgetInsecureCredentialCookies();

        return redirect()->route('school.login');
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
    
    public function getVideo(){
      
        $user = Auth::guard('school')->user();
        $userId = $user->id; 
        $data = SchoolVideo::where('school_id',$userId)->get();
          
          return view('school_admin.video.index',compact('data'));

    }
    
    public function addVideo(){
        
     return view('school_admin.video.add');
        
    }
    
    public function storeVideo(request $request){
        $school = Auth::guard('school')->user();
        abort_unless($school, 403);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm|max:102400',
            'thumbnail' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $videoPath = $request->file('video')->store('school_videos', 'public');
        
         $thumbnailPath = $request->file('thumbnail')
                        ->store('school_video_thumbnails', 'public');
        
        SchoolVideo::create([
            'school_id'   => $school->id,
            'title'       => $request->title,
            'description' => $request->description,
            'video_path'  => $videoPath,
            'thumbnail_image'    => $thumbnailPath, 
        ]);

        //Redirect with success
        return redirect()
            ->route('get.video')
            ->with('success', 'Video uploaded successfully');
    }
    
    public function edit($id){
            $school = auth('school')->user();
            if (!$school) {
                abort(403, 'Unauthorized access');
            }
            $video = SchoolVideo::where('id', $id)
                        ->where('school_id', $school->id)
                        ->firstOrFail();
            
            $title = 'Edit Video';
            return view('school_admin.video.edit', compact('video', 'title'));
        }
        
 
    public function update(Request $request, $id){
        $school = auth('school')->user();
        if (! $school) {
            abort(403, 'Unauthorized access');
        }

        $video = SchoolVideo::where('id', $id)
            ->where('school_id', $school->id)
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm|max:102400',
            'status' => 'required|in:0,1',
        ]);
    
        $video->title = $request->title;
        $video->description = $request->description;
        $video->is_active = $request->status ;
    
        if ($request->hasFile('video')) {
    
            // delete old video
            if ($video->video_path && file_exists(public_path('storage/'.$video->video_path))) {
                unlink(public_path('storage/'.$video->video_path));
            }
    
            $path = $request->file('video')->store('school_videos', 'public');
            $video->video_path = $path;
        }
        $video->save();
    
        return redirect()
            ->route('get.video')
            ->with('success', 'Video updated successfully');
    }

    public function destroyVideo(Request $request, $id)
    {
        $school = auth('school')->user();
        if (! $school) {
            abort(403, 'Unauthorized access');
        }

        $video = SchoolVideo::where('id', $id)
            ->where('school_id', $school->id)
            ->first();
    
        if (!$video) {
            return redirect()->back()->with('error', 'Video not found');
        }
        
        if ($video->video_path && Storage::disk('public')->exists($video->video_path)) {
            Storage::disk('public')->delete($video->video_path);
        }
        $video->delete();
        return redirect()
            ->route('get.video')
            ->with('success', 'Video deleted successfully');
    }
      
    
    public function changeStatus($id)
    {
        $school = School::findOrFail($id);
    
        // toggle status
        $school->status = $school->status == 1 ? 0 : 1;
        $school->save();
    
        return back()->with('success_message', 'Status updated successfully');
    }
    
    
    public function changePassword(){
        
      return view('school_admin.change-password');   
   
    }
    

public function storeChangePassword(Request $request)
{
   
    $request->validate([
        'current_password' => 'required',
        'new_password'     => 'required|min:6',
        'confirm_pwd'      => 'required',
    ]);

    $school = Auth::guard('school')->user();

    
    if (!Hash::check($request->current_password, $school->password)) {
        return back()->with('error_message', 'Old password is incorrect');
    }

    
    if ($request->new_password !== $request->confirm_pwd) {
        return back()->with('error_message', 'New password and confirm password do not match');
    }

   
    $school->password = Hash::make($request->new_password);
    $school->save();

   
    Auth::guard('school')->logout();

    return redirect()
        ->route('school.login')
        ->with('success_message', 'Password changed successfully. Please login again.');
}

    public function uploadSummernoteImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $path = Helper::handleFileUpload($request, 'image', 'uploads/summernote/school', null);

            return response()->json(['image_url' => Helper::mediaAsset($path)]);
        }

        return response()->json(['error' => 'No image uploaded'], 400);
    }
}

