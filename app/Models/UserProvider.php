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
        'user_balance',
        'balance_currency',
        'balance_alert_limit',
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
        LEFT JOIN `providers` `p` ON `up`.`provider_id` = `p`.`id` AND `is_activated` = 1  AND `is_frozon` = 0 AND `p`.`is_valid_key` = 1 AND `is_hold` = 0");
    }

    public static function getProviderListWithDetail($user_id){
        return DB::select("SELECT `up`.*, `p`.`service_count`, `p`.`domain`, `p`.`is_hold`, `p`.`is_valid_key` AS `main_key_valid`, `p`.`is_frozon`, `p`.`updated_at` AS `last_updated` 
            FROM (	
                SELECT *
                FROM `user_provider`
                WHERE `user_id` = {$user_id}
            ) `up`
            LEFT JOIN `providers` `p` ON `up`.`provider_id` = `p`.`id`
        ");
    }


}
