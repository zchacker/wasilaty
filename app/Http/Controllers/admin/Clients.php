<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class Clients extends Controller
{
    
    public function list(Request $request)
    {        
        $query = User::orderByDesc('created_at');
        $sum = $query->count('id');
        $users = $query->paginate(100);
        return view('admin_dashboard.clients.list', compact('users','sum'));
    }
    

}
