<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class User extends Model
{

    protected $fillable = [
        'firstname', 'email',
        'lastname', 'faculty_id',
        'department_id', 'mentor_id',
        'advisor_id', 'password'
    ];

    protected $hidden = [
        'password',
    ];
}
