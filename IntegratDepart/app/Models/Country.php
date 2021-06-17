<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model{
    protected $table = 'countries';
    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = ['name','code_alpha2','code_alpha3'];
}
