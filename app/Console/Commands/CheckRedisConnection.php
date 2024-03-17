<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class CheckRedisConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:redis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Redis connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        try {
            $pong = Redis::ping();

            if ($pong) {
                $this->info('Conexión exitosa con Redis!');
            } else {
                $this->error('No se ha podido establecer la conexión.');
            }
        } catch (\Throwable $e) {
            $this->error('Error al conectarse con Redis: ' . $e->getMessage());
        }
    }
}
