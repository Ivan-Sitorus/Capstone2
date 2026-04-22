<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnexpectedTransaction extends Model
{
    protected $fillable = ['jenis', 'nominal', 'deskripsi'];

    protected $casts = ['nominal' => 'float'];
}
