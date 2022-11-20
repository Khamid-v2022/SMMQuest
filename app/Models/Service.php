<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Provider;

class Service extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'provider_id',
        'service',
        'name',
        'type',
        'rate',
        'min',
        'max',
        'dripfeed',
        'refill',
        'cancel',
        'category'
    ];
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
