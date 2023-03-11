<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;

class Drivers extends Controller
{
    
    public function list(Request $request)
    {        
        $query = Driver::orderByDesc('created_at');
        $sum = $query->count('id');
        $users = $query->paginate(100);
        return view('admin_dashboard.drivers.list', compact('users','sum'));
    }

    public function details(Request $request)
    {
        $driver_id  = $request->driver_id;
        $driver     = Driver::where(['id' => $driver_id])->first();

        return view('admin_dashboard.drivers.details', compact('driver'));        
    }

    public function status(Request $request)
    {
        $driver_id = $request->driver_id;
        $status = $request->status;

        $driver = Driver::find($driver_id);
        $driver->isApproved = $status;

        if($driver->save()){
            return back()->with(['success' => "تم الحفظ بنجاح"]);    
        } else {                
            return back()->withErrors(['error' => "حدث خطأ ما, لم يتم تحديث الحالة"])->withInput($request->all());
        }
        
    }

}
