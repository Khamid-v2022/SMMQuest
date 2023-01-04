<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserList extends Model
{
    use HasFactory;
    protected $table = 'user_lists';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'list_name',
        'is_started',
        'started_at',
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

}
