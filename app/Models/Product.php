<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_product';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name_product', 'slug', 'description_product', 'image_product', 'price', 'quantity', 'status_product'];

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->id_product = (string) Str::uuid();
        });
    }
}
