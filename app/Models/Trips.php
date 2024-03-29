<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trips extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trips';


    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $primaryKey = 'id';


    protected $fillable = [
        'start_time',
        'end_time',
        'passengers',    
        'start_lat',
        'start_lng',
        'end_lat',
        'end_lng',
        'start_location_description',
        'end_location_description',
        'driver_id'        
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'driver_id',    
    ];


    public function driver(){
        return $this->hasOne(Driver::class , 'id', 'driver_id');        
    }

}
