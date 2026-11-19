<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function deleteAccount(Request $request)
    {
        if($request->isMethod('post')){
            return "Account Deleted Successfully";
        }
        return view('delete_account');
    }
    
    public function deleteAccountPage(Request $request)
    {
        return view('delete_account_page');
    }
}
