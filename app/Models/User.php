<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    protected $fillable = [
        'first_name', 'last_name', 'email'
    ];

    protected $hidden = [
        'password',
    ];

    public function enterprises()
    {
        return $this->belongsTo('App\Models\Enterprise');
    }

    public function skills()
    {
        return $this->belongsToMany('App\Models\Skill');
    }
}
