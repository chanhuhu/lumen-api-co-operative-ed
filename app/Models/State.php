<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    // all State defined in the database
    public static $IDEL       = 1;
    public static $APPROVE      = 2;
    public static $REJECT       = 3;
}
