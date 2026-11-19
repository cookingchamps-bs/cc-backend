<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function userList(Request $request)
    {
        $common = [];
        $common['title'] = 'Parent List';
        $common['heading_title'] = 'Parent List';
        if ($request->type == 'kid') {
            return 'hello kid';
        }

        $users = User::all();

        return view('admin.parent.index', compact('common', 'users'));

    }
}
