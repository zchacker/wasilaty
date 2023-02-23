<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Orders as OrdersModel;
use Illuminate\Http\Request;

class Orders extends Controller
{
    
    public function list(Request $request)
    {        
        $query = OrdersModel::orderByDesc('created_at');
        $sum = $query->count('id');
        $orders = $query->paginate(100);
        // dd($query->first()->client->name);

        return view('admin_dashboard.orders.list', compact('orders','sum'));
    }

}
