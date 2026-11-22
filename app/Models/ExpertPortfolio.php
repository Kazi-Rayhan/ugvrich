<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertPortfolio extends Model
{
    protected $fillable = ['title', 'path', 'is_public'];

    protected $casts = ['is_public' => 'boolean'];
}
