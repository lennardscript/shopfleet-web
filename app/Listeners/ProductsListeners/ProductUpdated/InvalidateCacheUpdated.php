<?php

namespace App\Listeners\ProductsListeners\ProductUpdated;

use App\Events\ProductUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;

class InvalidateCacheUpdated
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
    public function handle(\App\Events\ProductsEvents\ProductUpdated $event): void
    {
        //TODO: invalidación de la caché de productos paginados
        Redis::del('products:paginated');

        //TODO: invalidación de la caché de detalles del producto específico
        Redis::del("product:{$event->product->id_product}");

        //TODO: invalidación de la caché de búsqueda si el producto afecta los resultados de búsqueda
        Redis::del("products:search:{$event->product->name_product}");
    }
}
