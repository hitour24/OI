<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model{
    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = ['source_id','campaign_id','template_id','name'];

    static public function getNameNetwork(int $sourceId):string{
        $nameNetwork = self::query()->where('source_id','=',$sourceId)->get()[0]->name;
        return  strpos($nameNetwork,"(") ? mb_strtolower(trim(substr($nameNetwork,0,strpos($nameNetwork,'(')))) : mb_strtolower($nameNetwork);
    }
}
