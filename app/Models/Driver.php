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
        'otp_requested_time'
    ];

}
