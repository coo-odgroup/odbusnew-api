<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitiesSynonym extends Model
{
    protected $table = 'cities_synonyms';

    protected $fillable = [
        'city_id',
        'synonym',
        'active_status'
    ];

    public function city()
    {
        return $this->belongsTo(Cities::class, 'city_id');
    }
}
