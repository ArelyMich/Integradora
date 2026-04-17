<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAcceso extends Model
{
    protected $table = 'log_accesos';

    protected $fillable = [
        'email',
        'username',
        'ip_address',
        'user_agent',
        'resultado',
        'logout_at',
    ];

    public $timestamps = true;
}
