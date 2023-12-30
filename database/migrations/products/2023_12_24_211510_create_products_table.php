<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up(): void
        {
            Schema::create('products', function (Blueprint $table) {
                $table->uuid('id_product')->primary()->default(Str::uuid());
                $table->uuid('id_category')->nullable();
                $table->string('name_product')->nullable(false);
                $table->string('slug')->nullable()->unique();
                $table->text('description_product')->nullable();
                $table->binary('image_product')->nullable();
                $table->integer('price')->nullable(false);
                $table->integer('quantity')->nullable(false);
                $table->string('status_product')->nullable()->default('active')->comment('active or inactive');
                $table->timestamps();
            });

            // TODO: agregar clave foránea de categoría

            Schema::table('products', function (Blueprint $table) {
                $table->foreign('id_category')->references('id_category')->on('categories');
            });

            //! Modificación del tipo de dato de la columna 'image_product' a 'bytea' en la base de datos
            DB::statement('ALTER TABLE products ALTER COLUMN image_product TYPE bytea USING image_product::bytea');

            //! Para simular un enum, cambia el tipo de dato de la columna 'status_product' a 'enum' y se agrega una restricción de verificación
            DB::statement("ALTER TABLE products ADD CONSTRAINT status_check CHECK (status_product IN ('active', 'inactive'))");
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'id_category')) {
                    $table->dropForeign(['id_category']);
                    $table->dropColumn('id_category');
                }
            });

            Schema::dropIfExists('products');
        }
};
