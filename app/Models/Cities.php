<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    protected $table = 'cities';

    protected $fillable = [
        'state_id',
        'district_id',
        'location_name',
        'alias',
        'active_status'
    ];

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function synonyms()
    {
        return $this->hasMany(CitiesSynonym::class, 'city_id')
                    ->where('active_status', 1);
    }
}
