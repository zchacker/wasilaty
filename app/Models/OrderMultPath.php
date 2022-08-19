<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class OrderMultPath extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'order_multi_path';
    
    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'end_lat', 'end_lng' , 'location_description' ,
        'user_id','status' , 'payment_method' ,
        'passengers', 'driver_gender', 'price', 'total_distance'
    ];


}
