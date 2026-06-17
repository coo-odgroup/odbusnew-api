<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'districts';

    protected $fillable = [
        'state_id',
        'district_name',
        'active_status'
    ];

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
}
