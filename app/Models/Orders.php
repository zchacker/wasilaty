<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'orders';
    //protected $dateFormat = 'd-m-Y H:i';

    protected $fillable = [
        'start_lat',
        'start_lng',
        'end_lat',
        'end_lng',
        'vehicle_type',
        'user_id',
        'order_type',
        'status',
        'start_point_description',
        'end_point_description',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
        //'created_at',
        'updated_at',
    ];

    protected $casts = [
        //'birthday' => 'date:Y-m-d',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

}
