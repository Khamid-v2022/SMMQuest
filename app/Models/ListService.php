<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ListService extends Model
{
    use HasFactory;
    protected $table = 'list_services';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'list_id',
        'provider_id',
        'service_id',
        'api_template',
        'order_id',
        'start_count',
        'quantity',
        'link',
        'service_type',
        'started_at',
        'comment',
        'status',
        'inprogress_minute',
        'completed_minute'
    ];

    public function list() {
        return $this->belongsTo('App\Models\UserList');
    }

    public function service() {
        return $this->belongsTo('App\Models\Service');
    }
}
