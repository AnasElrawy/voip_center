<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'timezone',
        'is_active',
    ];

}
