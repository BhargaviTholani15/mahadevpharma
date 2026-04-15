<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackSize extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];
}
