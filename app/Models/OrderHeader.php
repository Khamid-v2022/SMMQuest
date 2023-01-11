<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderHeader extends Model
{
    use HasFactory;
    protected $table = 'order_headers';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'order_serial_id',
        'service_count',
        'total_cost'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function details() {
        return $this->hasMany('App\Models\OrderDetail');
    }
}