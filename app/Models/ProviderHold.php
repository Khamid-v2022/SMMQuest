<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderHold extends Model
{
    use HasFactory;
    protected $table = 'hold_providers';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'domain',
        'endpoint',
        'api_key',
        'request_by_admin',
        'request_by_id'
    ];

    protected $hidden = [
        'api_key'
    ];

}
