<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdersAssignedToDrivers extends Model
{
    use HasFactory, SoftDeletes;    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'orders_assigned_to_drivers';

    protected $fillable = [
        'order_id',
        'driver_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

}
