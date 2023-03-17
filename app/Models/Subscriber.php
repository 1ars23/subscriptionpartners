<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $table = 'subscribers';
    protected $fillable = ['subscription_status', 'subscription_id', 'msisdn'];
}
