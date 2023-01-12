<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderDetail extends Model
{
    use HasFactory;
    protected $table = 'order_details';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'header_id',
        'list_id',
        'service_id',
        'paid_price',
        'paid_currency',
        'conversion_rate',
        'order_id',
        'cost',
        'quantity',
        'link',
        'comments',
        'usernames',
        'username',
        'hashtags',
        'hashtag',
        'media',
        'answer_number',
        'groups',
        'min',
        'max',
        'delay',
        'posts',
        'old_posts',
        'expiry',
        'start_count',
        'remains',
        'in_progress_minute',
        'completed_minute',
        'status',                           //0: default, 1: pending(make order), 2: inprogress(started order via API), 3: complete, 4: rejected, 5: canceled
        'error_message',
    ];

    public function list() {
        return $this->belongsTo('App\Models\UserList');
    }

    public function service() {
        return $this->belongsTo('App\Models\Service');
    }

    
}