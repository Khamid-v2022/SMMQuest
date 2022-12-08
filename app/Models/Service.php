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
                    // ->orderBy("rate")
                    ->limit(3000)
                    ->get();
        return $result;
    } 

    public static function search_services_with_query($user_id, $provider_ids, $type, $include, $exclude, $min, $max, $min_rate, $max_rate){
        
        $sql = "SELECT `domain`, `is_favorite`, `service`, `name`, `type`, `rate`, `min`, `max`, `dripfeed`, `refill`, `cancel`, `category`, `status`, `s`.`created_at`, `s`.`updated_at` ";
        $sql .= " FROM ( ";
            $sql .= " SELECT `p`.`id`, `is_favorite`, `domain` FROM ( ";
                $sql .= " SELECT `provider_id`, `is_favorite` FROM `user_provider` WHERE `is_enabled` = 1 AND `user_id` = " . $user_id;
            $sql .= " ) `up` ";
            $sql .= " LEFT JOIN `providers` `p` ON  `up`.`provider_id` = `p`.`id` AND `is_activated` = 1 ";
        $sql .= " ) `pro` ";
        $sql .= " LEFT JOIN `services` `s` ON `pro`.`id` = `s`.`provider_id` AND `status` = 1 ";
        $sql .= " WHERE ";
        if(trim($type))
            $sql .= " `type` = '{$type}' ";
        else
            $sql .= " `type` LIKE '%' ";
        //check provider 
        if($provider_ids && !(count($provider_ids) == 1 && $provider_ids[0] == '0')){
            $provider_ids_str = " (";
            foreach($provider_ids as $provider_id){
                $provider_ids_str .= $provider_id . ", ";
            }
            $provider_ids_str = rtrim($provider_ids_str, ", ") . ") ";
            $sql .= " AND `s`.`provider_id` IN {$provider_ids_str} ";
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
        // $sql .= " ORDER BY `rate`";
        $sql .= " LIMIT 3000 ";

        $result =  DB::select($sql);
       
        return $result;
    }
}
