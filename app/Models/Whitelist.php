<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Whitelist extends Model
{
    use HasFactory;    

    public $timestamps = false;
    
    protected $table ="whitelist_ips";

        protected $fillable = [
        'otp',
        'user_id',
        'ip',
        'is_active'
    ];
  
}
