<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcLib extends Model
{
    protected $connection = 'oracle';
    protected $table = 'pclib';
}
