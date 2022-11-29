<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class LoginHistory extends Model
{
    use HasFactory;
    protected $table = 'login_history';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'agency',
        'ip_address',
        'browser',
        'device',
        'platform',
        'location'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
