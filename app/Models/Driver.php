<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Driver extends Model
{
    use HasFactory, HasApiTokens, Notifiable;

      /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'driver';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'avatar',
        'first_name',
        'last_name',
        'email',
        'birth_date',
        'id_photo',
        'driver_license_front',
        'driver_license_back',
        'military_service_certificate',
        'vehicle_model',
        'vehicle_color',
        'vehicle_type',
        'vehicle_made_year',
        'vehicle_passengers',
        'vehicle_license_front',
        'vehicle_license_back',
        'phone_numeber',
        'one_time_password',
        'otp_requested_time',
        'isActive',
        'isApproved',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'otp_requested_time',
        'one_time_password',
        'deleted_at',
        //'created_at',
        //'updated_at',
        'isActive',
        'isApproved',
    ];

    protected $casts = [
        //'birthday' => 'date:Y-m-d',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

}
