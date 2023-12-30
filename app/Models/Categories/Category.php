<?php

namespace App\Models\Categories;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_category';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name_category',
    ];

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->id_category = (string) Str::uuid();
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
