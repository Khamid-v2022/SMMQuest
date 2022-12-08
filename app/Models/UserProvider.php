<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public static function getProviderList($user_id){
        return DB::select("SELECT `p`.`id`, `p`.`domain`
        FROM 
        (SELECT * 
        FROM `user_provider`
        WHERE `user_id` = {$user_id}) `up`
        LEFT JOIN `providers` `p` ON `up`.`provider_id` = `p`.`id`");
    }

    public static function getProviderListWithDetail($user_id){
        return DB::select("SELECT `pro`.*, `s`.`ctn` FROM
        (
        SELECT `up`.*, `p`.`domain`, `p`.`is_hold`, `p`.`updated_at` AS `last_updated` 
        FROM (	
            SELECT *
            FROM `user_provider`
            WHERE `user_id` = {$user_id}
        ) `up`
        LEFT JOIN `providers` `p` ON `up`.`provider_id` = `p`.`id`
        ) `pro`
        LEFT JOIN 
        (
        SELECT `provider_id`, COUNT(`id`) AS `ctn`
        FROM `services`
        WHERE `status` = 1
        GROUP BY `provider_id`
        ) `s` ON `pro`.`provider_id` = `s`.`provider_id`");
    }
}
