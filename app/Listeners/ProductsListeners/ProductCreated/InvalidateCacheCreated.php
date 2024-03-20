<?php

namespace App\Listeners\ProductsListeners\ProductCreated;

use App\Events\ProductCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;

class InvalidateCacheCreated
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
    public function handle(\App\Events\ProductsEvents\ProductCreated $event): void
    {
        //TODO: invalidación de la caché de búsqueda si el producto afecta los resultados de búsqueda
        Redis::del("products:search:{$event->product->name_product}");
    }
}
