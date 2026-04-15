<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrugSchedule extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'sort_order'];
}
