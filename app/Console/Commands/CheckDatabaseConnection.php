<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDatabaseConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check database connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            //TODO: intenta ejecutar una consulta simple para verificar la conexión
            $db = DB::connection()->getPdo();
            if ($db) {
                $this->info('Conexión exitosa con la base de datos!');
            } else {
                $this->error('No se ha podido establecer conexión con la base de datos.');
            }
        } catch (\Throwable $e) {
            $this->error('Error al conectarse a la base de datos: ' . $e->getMessage());
        }
    }
}
