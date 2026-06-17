<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'states';

    protected $fillable = [
        'state_name',
        'active_status'
    ];

    public function districts()
    {
        return $this->hasMany(District::class, 'state_id');
    }
}
