<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public static function search_services($user_id, $provider_ids, $type, $include, $exclude, $min, $max){
        $result =  UserProvider::where('user_id', $user_id)
                    ->join('providers', 'providers.id' , '=', 'user_provider.provider_id')
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
                    ->where('status', 1)
                    ->where('is_enabled', 1)
                    // ->where('user_provider.is_valid_key', 1)
                    ->select("domain", "service", "name", "type", "rate", "min", "max", "dripfeed", "refill", "cancel", "category", "status", "services.created_at", "services.updated_at")
                    ->get();
        return $result;
    } 
}
