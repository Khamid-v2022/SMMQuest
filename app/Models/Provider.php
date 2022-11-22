<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Service;

class Provider extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'domain',
        'endpoint',
        'api_key',
        'is_valid_key',
        'is_activated',
        'request_by',
        'activated_at'
    ];

    public function services(){
        return $this->hasMany(Service::class);
    }

    public function providers(){
        return $this->hasMany('App\Models\UserProvider');
    }
}
