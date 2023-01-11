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
    ];

    public function list() {
        return $this->belongsTo('App\Models\UserList');
    }

    public function service() {
        return $this->belongsTo('App\Models\Service');
    }
}
