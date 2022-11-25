<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProvider extends Model
{
    use HasFactory;
    protected $table = 'user_provider';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'provider_id',
        'is_favorite',
        'api_key',
        'is_enabled',
        'is_valid_key'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function provider() {
        return $this->belongsTo('App\Models\Provider');
    }
}
