<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'public.news';

    protected $fillable = [
        'guid',
        'title',
        'link',
        'description',
        'publication_date',
        'author',
        'images',
    ];
}
