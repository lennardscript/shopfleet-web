<?php

namespace App\Models\Products;

use App\Models\Categories\Category;
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

    protected $hidden = ['id_category'];

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->id_product = (string) Str::uuid();
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_category')->select('id_category', 'name_category');
    }

    public function append_category_name()
    {
        return ['category' => ['id_category' => $this->id_category, 'name_category' => $this->category->name_category]];
    }

    public function toArray()
    {
        $array = parent::toArray();

        //TODO: obtener la categoría asociada al producto
        $category = $this->category;

        //TODO: agrega la información de la categoría al array del producto
        $array['category'] = [
            'id_category' => $category->id_category,
            'name_category' => $category->name_category
        ];

        return $array;
    }
}
