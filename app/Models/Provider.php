<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Service;

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
        'api_template',
        'balance',
        'currency',
        'request_by',
        'activated_at',
        'is_hold'
    ];

    protected $hidden = [
        'api_key'
    ];

    public function services(){
        return $this->hasMany(Service::class);
    }

    public function providers(){
        return $this->hasMany('App\Models\UserProvider');
    }

    public static function getProviders() {
        return DB::select("SELECT `p`.*, `s`.`ctn`
        FROM `providers` `p`
        LEFT JOIN 
        (
            SELECT `provider_id`, COUNT(`id`) AS `ctn`
            FROM `services`
            WHERE `status` = 1
            GROUP BY `provider_id`
        ) `s` ON `p`.`id` = `s`.`provider_id`");
    }
}
