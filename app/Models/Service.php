<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Provider;
use App\Models\UserProvider;


class Service extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'provider_id',
        'provider',
        'default_currency',
        'service',
        'name',
        'type',
        'rate',
        'rate_usd',
        'min',
        'max',
        'dripfeed',
        'refill',
        'cancel',
        'category',
        'status'
    ];
    
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public static function service_types($user_id){
        return UserProvider::where('user_id', $user_id)
            ->join('services', 'services.provider_id' , '=', 'user_provider.provider_id')
            ->groupBy('type')
            ->select('type')
            ->orderBy('type')
            ->get();
    }

    public static function service_types_with_query($user_id){
        return DB::select("SELECT `type`
        FROM `services`
        WHERE `status` = 1
        GROUP BY `type`
        ORDER BY `type`");
    }

    public static function search_services($user_id, $provider_ids, $type, $include, $exclude, $min, $max, $min_rate, $max_rate){
        $result =  UserProvider::where('user_id', $user_id)
                    ->join('providers', 'providers.id' , '=', 'user_provider.provider_id')
                    ->where('is_activated', 1)
                    ->join('services', 'services.provider_id' , '=', 'user_provider.provider_id')
                    ->when($provider_ids, function ($query) use ($provider_ids){   
                        if(count($provider_ids) == 1 && $provider_ids[0] == '0')
                            return $query;
                        return $query->whereIn('services.provider_id', $provider_ids);
                    })
                    ->when($include, function ($query) use ($include){   
                        if(count($include) == 0)
                            return $query;
                        foreach($include as $word)
                            $query->where('services.name', 'LIKE', "%{$word}%");
                        return $query;
                    })
                    ->when($exclude, function ($query) use ($exclude){   
                        if(count($exclude) == 0)
                            return $query;
                        foreach($exclude as $word)
                            $query->where('services.name', 'NOT LIKE', "%{$word}%");
                        return $query;
                    })
                    ->when($type, function ($query) use ($type){   
                        return $query->where('services.type', 'LIKE', "{$type}");
                    })
                    ->when($max, function ($query) use ($max){
                        return $query->where('services.max', '<=', $max);
                    })
                    ->when($min, function ($query) use ($min){
                        return $query->where('services.min', '>=', $min);
                    })
                    ->when($min_rate, function ($query) use ($min_rate){
                        return $query->where('services.rate', '>=', $min_rate);
                    })
                    ->when($max_rate, function ($query) use ($max_rate){
                        return $query->where('services.rate', '<=', $max_rate);
                    })
                    ->where('status', 1)
                    ->where('is_enabled', 1)
                    // ->where('user_provider.is_valid_key', 1)
                    ->select("domain", "is_favorite", "service", "name", "type", "rate", "min", "max", "dripfeed", "refill", "cancel", "category", "status", "services.created_at", "services.updated_at")
                    ->orderBy("rate_usd")
                    ->limit(5000)
                    ->get();
        return $result;
    } 

    public static function search_services_with_query($user_id, $provider_ids, $type, $include, $exclude, $min, $max, $min_rate, $max_rate, $added_after, $added_before){
        
        $sql = "SELECT `s`.`id`, `s`.`provider` AS `domain`, `is_favorite`, `service`, `name`, `type`, `rate`, `min`, `max`, `dripfeed`, `refill`, `cancel`, `category`, `default_currency` AS `main_currency`, `up`.`balance_currency` AS `user_currency`, `status`, `s`.`created_at`, `s`.`updated_at`, `s`.`rate_usd` ";     
        $sql .= " FROM ( ";
                $sql .= " SELECT `provider_id`, `is_favorite`, `balance_currency` FROM `user_provider` WHERE `is_enabled` = 1 AND `user_id` = " . $user_id;
            $sql .= " ) `up` ";
        // $sql .= " LEFT JOIN `services` `s` ON `up`.`provider_id` = `s`.`provider_id` AND `status` = 1 AND `rate_usd` IS NOT NULL ";
        $sql .= " LEFT JOIN `services` `s` ON `up`.`provider_id` = `s`.`provider_id` AND `status` = 1 ";
        $sql .= " WHERE 1 ";
        
        if(trim($type))
            $sql .= " AND `type` = '{$type}' ";

        
        //check provider (if do not selected all or favorite item)
        if($provider_ids){
            if(!(count($provider_ids) == 1 && ($provider_ids[0] == '0' || $provider_ids[0] == '-1'))){            
                $provider_ids_str = " (";
                foreach($provider_ids as $provider_id){
                    $provider_ids_str .= $provider_id . ", ";
                }
                $provider_ids_str = rtrim($provider_ids_str, ", ") . ") ";
                $sql .= " AND `s`.`provider_id` IN {$provider_ids_str} ";
            } else if(count($provider_ids) == 1 && $provider_ids[0] == '-1')            //favorite
                $sql .= " AND `is_favorite` = 1 ";
        }

        if($include && count($include) != 0){
            foreach($include as $word){
                $sql .= " AND `name` LIKE '%{$word}%' ";
            }               
        }

        if($exclude && count($exclude) != 0){
            foreach($exclude as $word){
                $sql .= " AND `name` NOT LIKE '%{$word}%' ";
            }               
        }

        if($min)
            $sql .= " AND `min` >= {$min} ";
        if($max)
            $sql .= " AND `max` <= {$max} ";
        if($min_rate)
            $sql .= " AND `rate` >= {$min_rate} ";
        if($max_rate)
            $sql .= " AND `rate` <= {$max_rate} ";
        if($added_after)
            $sql .= " AND `s`.`created_at` >= '{$added_after}' ";
        if($added_before)
            $sql .= " AND `s`.`created_at` <= '{$added_before}' ";

        // $sql .= " ORDER BY `rate_usd`";

        $result =  DB::select($sql);
       
        return array('result'=>$result, 'sql_query'=>$sql);
    }

    // public static function search_services_with_query_type1($user_id, $provider_ids, $type, $include, $exclude, $min, $max, $min_rate, $max_rate){
        
    //     $sql = "SELECT `domain`, `is_favorite`, `service`, `name`, `type`, `rate`, `min`, `max`, `dripfeed`, `refill`, `cancel`, `category`, `main_currency`, `user_currency`, `status`, `s`.`created_at`, `s`.`updated_at`, `s`.`rate_usd` ";     
    //     $sql .= " FROM ( ";
    //         $sql .= " SELECT `p`.`id`, `is_favorite`, `domain`, `p`.`currency` AS `main_currency`, `up`.`balance_currency` AS `user_currency` FROM ( ";
    //             $sql .= " SELECT `provider_id`, `is_favorite`, `balance_currency` FROM `user_provider` WHERE `is_enabled` = 1 AND `user_id` = " . $user_id;
    //         $sql .= " ) `up` ";
    //         $sql .= " LEFT JOIN `providers` `p` ON  `up`.`provider_id` = `p`.`id` AND `is_activated` = 1 AND `is_frozon` = 0 AND `is_hold` = 0 ";
    //     $sql .= " ) `pro` ";
    //     $sql .= " LEFT JOIN `services` `s` ON `pro`.`id` = `s`.`provider_id` AND `status` = 1 ";
       
    //     $sql .= " WHERE `rate_usd` IS NOT NULL ";
    //     if(trim($type))
    //         $sql .= " AND `type` = '{$type}' ";
    //     else
    //         $sql .= " AND `type` LIKE '%' ";

        
    //     //check provider (if do not selected all or favorite item)
    //     if($provider_ids){
    //         if(!(count($provider_ids) == 1 && ($provider_ids[0] == '0' || $provider_ids[0] == '-1'))){            
    //             $provider_ids_str = " (";
    //             foreach($provider_ids as $provider_id){
    //                 $provider_ids_str .= $provider_id . ", ";
    //             }
    //             $provider_ids_str = rtrim($provider_ids_str, ", ") . ") ";
    //             $sql .= " AND `s`.`provider_id` IN {$provider_ids_str} ";
    //         } else if(count($provider_ids) == 1 && $provider_ids[0] == '-1')            //favorite
    //             $sql .= " AND `is_favorite` = 1 ";
    //     }

    //     if($include && count($include) != 0){
    //         foreach($include as $word){
    //             $sql .= " AND `name` LIKE '%{$word}%' ";
    //         }               
    //     }

    //     if($exclude && count($exclude) != 0){
    //         foreach($exclude as $word){
    //             $sql .= " AND `name` NOT LIKE '%{$word}%' ";
    //         }               
    //     }

    //     if($min)
    //         $sql .= " AND `min` >= {$min} ";
    //     if($max)
    //         $sql .= " AND `max` <= {$max} ";
    //     if($min_rate)
    //         $sql .= " AND `rate` >= {$min_rate} ";
    //     if($max_rate)
    //         $sql .= " AND `rate` <= {$max_rate} ";


    //     $sql .= " ORDER BY `rate_usd`";

    //     $result =  DB::select($sql);
       
    //     return $result;
    // }

    public static function search_services_with_query_type2_backup($user_id, $provider_ids, $type, $include, $exclude, $min, $max, $min_rate, $max_rate){
        
        $sql = "SELECT `ss`.`provider_id`, `user_id`, `domain`, `is_favorite`, `service`, `name`, `type`, `rate`, `rate_usd`, `min`, `max`, `dripfeed`, `refill`, `cancel`, `category`, `main_currency`, `balance_currency` AS `user_currency`, `status`, `ss`.`created_at`, `ss`.`updated_at` ";     
        $sql .= " FROM ( ";
            $sql .= " SELECT `provider_id`, `provider` AS `domain`, `default_currency` AS `main_currency`, `service`, `name`, `type`, `rate`, `rate_usd`, `min`, `max`, `dripfeed`, `refill`, `cancel`, `category`, `created_at`, `updated_at`, `status` ";
            $sql .= " FROM `services` `s` ";
            $sql .= " WHERE EXISTS ( ";
                $sql .= " SELECT 1 ";
                $sql .= " FROM `user_provider` `up` ";
                $sql .= " WHERE `s`.`provider_id` = `up`.`provider_id` AND `user_id` = {$user_id} AND `is_enabled` = 1 )";
            $sql .= " AND STATUS = 1 ";
            $sql .= " ORDER BY `rate_usd` ";
        $sql .= " ) `ss` ";
        $sql .= " INNER JOIN `user_provider` `up` ON `ss`.`provider_id` = `up`.`provider_id` ";
        // $sql .= " INNER JOIN `providers` `p` ON `up`.`provider_id` = `p`.id AND `p`.`is_activated` = 1 AND `p`.`is_frozon` = 0 AND `p`.`is_hold` = 0 ";
        
        
        $sql .= " WHERE ";
        if(trim($type))
            $sql .= " `type` = '{$type}' ";
        else
            $sql .= " `type` LIKE '%' ";
        
        //check provider (if do not selected all or favorite item)
        if($provider_ids){
            if(!(count($provider_ids) == 1 && ($provider_ids[0] == '0' || $provider_ids[0] == '-1'))){            
                $provider_ids_str = " (";
                foreach($provider_ids as $provider_id){
                    $provider_ids_str .= $provider_id . ", ";
                }
                $provider_ids_str = rtrim($provider_ids_str, ", ") . ") ";
                $sql .= " AND `up`.`provider_id` IN {$provider_ids_str} ";
            } else if(count($provider_ids) == 1 && $provider_ids[0] == '-1')            //favorite
                $sql .= " AND `is_favorite` = 1 ";
        }

        if($include && count($include) != 0){
            foreach($include as $word){
                $sql .= " AND `name` LIKE '%{$word}%' ";
            }               
        }

        if($exclude && count($exclude) != 0){
            foreach($exclude as $word){
                $sql .= " AND `name` NOT LIKE '%{$word}%' ";
            }               
        }

        if($min)
            $sql .= " AND `min` >= {$min} ";
        if($max)
            $sql .= " AND `max` <= {$max} ";
        if($min_rate)
            $sql .= " AND `rate` >= {$min_rate} ";
        if($max_rate)
            $sql .= " AND `rate` <= {$max_rate} ";

        $result =  DB::select($sql);

               
        return $result;
    }
}
