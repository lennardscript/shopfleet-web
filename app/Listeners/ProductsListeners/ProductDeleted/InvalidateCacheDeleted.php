<?php

namespace App\Listeners\ProductsListeners\ProductDeleted;

use App\Events\ProductDeleted;
use App\Models\Products\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;

class InvalidateCacheDeleted
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(\App\Events\ProductsEvents\ProductDeleted $event): void
    {

        //TODO: obtener la página actual de productos asignados
        $page = request()->get('page', 1);

        //TODO: obtener la lista actualizada de los productos paginados
        $updatedProductList = Product::orderBy('name_product')->paginate(10, ['*'],'page', $page);

        //TODO: almacenar la lista actualizada de productos paginados en caché
        Redis::set('products:paginated:page:{$page}', serialize($updatedProductList));
    }
}
