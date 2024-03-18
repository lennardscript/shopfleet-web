<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductsRequests\StoreProductRequest;
use App\Http\Requests\ProductsRequests\UpdateProductRequest;
use App\Models\Categories\Category;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index()
    {

        $products = Redis::get('products:paginated');

        if (!$products) {
            $products = Product::orderBy(function ($query) {
                $query->selectRaw('LOWER(name_product)');
            })->paginate(10);

            //TODO: almacenar los productos paginados en Redis por 350 segundos
            Redis::setex('products:paginated', 350, serialize($products));

        } else {

            //TODO: deserializar los productos almacenados en Redis
            $products = unserialize($products);
        }

        return response()->json(['products' => $products], Response::HTTP_OK);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(StoreProductRequest $request)
    {
        //
        try {

            // TODO: busca la categoría por su nombre
            $category = Category::where('name_category', $request->category)->firstOrFail();

            // TODO: crea un producto
            $product = new Product($request->except('category'));
            $product->id_category = $category->id_category;
            $product->slug = Str::slug($product->name_product);
            $product->save();

            //TODO: crea un directorio o carpeta para las imagenes subidas
            $imgDirectory = 'products/' . $product->id_product;
            Storage::makeDirectory($imgDirectory);

            //TODO: guarda la imagen subida
            if ($request->hasFile('image_product')) {
                $image = $request->file('image_product');
                $imgPath = $image->store($imgDirectory, 'public');
                $product->image_product = $imgPath;
                $product->save();
            }

            //TODO: invalidación de la caché de productos paginados
            Redis::del('products:paginated');

            Log::info('Product created successfully!');
            return response()->json(['product' => $product], Response::HTTP_CREATED);
        } catch (\Throwable $e) {

            Log::error('Error creating product: ', ['exception' => $e]);
            return response()->json(['message' => 'An error ocurred while creating the product', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {

        $productDetails = Redis::get('product:' . $product->id_product);

        if (!$productDetails) {
            $product->load('category');
            $product->makeHidden('id_category');
            $productDetails = $product->toArray();

            //TODO: almacenar los detalles del producto en Redis por 350 segundos
            Redis::setex('product:' . $product->id_product, 350, serialize($productDetails));

        } else {

            //TODO: deserializar los detalles del producto almacenados en Redis
            $productDetails = unserialize($productDetails);
        }

        return response()->json(['product' => $product], Response::HTTP_OK);

    }

    public function search_name_product($name_product)
    {

        $products = Redis::get('products:search:' . $name_product);

        if (!$products) {

            $products = Product::whereRaw('LOWER(name_product) LIKE LOWER(?)', ['%' . $name_product . '%'])->paginate(10);

            //TODO: almacenar los resultados de búsqueda en Redis por 350 segundos
            Redis::setex('products:search:' . $name_product, 350, serialize($products));
        } else {

            //TODO: deserializar los resultados de búsqueda almacenados en Redis
            $products = unserialize($products);
        }

        if ($products->isEmpty()) {
            Log::error('No products found for name: ' . $name_product);
            return response()->json(['info' => 'Products not found', 'name_product' => $name_product], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['product'=> $products], Response::HTTP_OK);

        /* if (empty ($name_product)) {
            return response()->json(['info' => 'Please provide a name to search'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $products = Product::whereRaw('LOWER(name_product) LIKE LOWER(?)', ['%' . $name_product . '%'])->paginate(10);

        if ($products->isEmpty()) {
            Log::error('No products found for name: ' . $name_product);
            return response()->json(['info' => 'Products not found', 'name_product' => $name_product], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['products' => $products], Response::HTTP_OK); */
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    public function update(UpdateProductRequest $request, string $slug)
    {
        //

        $product = Product::where('slug', $slug)->first();

        if (!$product) {
            return response()->json(['info:' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        //TODO: obtener solo los campos que deseo actualizar
        $fieldsToUpdate = $request->only([
            'name_product',
            'slug',
            'description_product',
            'image_product',
            'price',
            'quantity',
            'status_product',
        ]);

        if ($request->has('name_product') && $request->input('name_product') !== $product->name_product) {
            $product->slug = Str::slug($request->input('name_product'));
        }

        $product->fill($fieldsToUpdate);
        $product->save();


        Log::info('Product updated successfully!');
        return response()->json(['product' => $product], Response::HTTP_OK);
    }

    public function destroy(Product $product)
    {
        //

        try {

            if (!$product) {
                throw new ModelNotFoundException('Product not found');
            }

            $product->delete();

            return response()->json(['message' => 'Product deleted successfully!'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error("Error deleting product: " . $e->getMessage());
            return response()->json(['info' => 'Product not found'], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $e) {
            Log::error("Error deleting product: " . $e->getMessage());
            return response()->json(['error' => 'An error ocurred while deleting the product'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
