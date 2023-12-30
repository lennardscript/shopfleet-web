<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductsRequests\StoreProductRequest;
use App\Http\Requests\ProductsRequests\UpdateProductRequest;
use App\Models\Categories\Category;
use App\Models\Products\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class ProductController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $products = Product::orderBy(function ($query) {
            $query->selectRaw('LOWER(name_product)');
        })->paginate(10);
        return response()->json(['products' => $products], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
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

            Log::info('Product created successfully!');
            return response()->json(['product' => $product], 201);
        } catch (\Throwable $e) {

            Log::error('Error creating product: ', ['exception' => $e]);
            return response()->json(['message' => 'An error ocurred while creating the product', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('category');
        $product->makeHidden('id_category');
        $product = $product->toArray();
        $product = [
            'id_product' => $product['id_product'],
            'name_product' => $product['name_product'],
            'slug' => $product['slug'],
            'description_product' => $product['description_product'],
            'image_product' => $product['image_product'],
            'price' => $product['price'],
            'quantity' => $product['quantity'],
            'status_product' => $product['status_product'],
            'created_at' => $product['created_at'],
            'updated_at' => $product['updated_at'],
            'category' => [
                'id_category' => $product['category']['id_category'],
                'name_category' => $product['category']['name_category']
            ]
        ];
        return response()->json(['product' => $product], 200);
    }

    // TODO: search products for name
    public function searchByName($name_product)
    {

        if (empty($name_product)) {
            return response()->json(['message' => 'Please provide a name to search'], 422);
        }

        $products = Product::whereRaw('LOWER(name_product) LIKE LOWER(?)', ['%' . $name_product . '%'])->paginate(10);

        if ($products->isEmpty()) {
            Log::error('No products found for name: ' . $name_product);
            return response()->json(['message' => 'Products not found', 'name_product' => $name_product], 404);
        }

        return response()->json(['products' => $products], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $slug)
    {
        //

        $product = Product::where('slug', $slug)->first();

        if (!$product) {
            return response()->json(['message:' => 'Product not found'], 404);
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
        return response()->json(['product' => $product], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //

        try {

            if (!$product) {
                throw new ModelNotFoundException('Product not found');
            }

            $product->delete();

            return response()->json(['message' => 'Product deleted successfully!'], 200);
        } catch (ModelNotFoundException $e) {
            Log::error("Error deleting product: " . $e->getMessage());
            return response()->json(['message' => 'Product not found'], 404);
        } catch (\Throwable $e) {
            Log::error("Error deleting product: " . $e->getMessage());
            return response()->json(['message' => 'An error ocurred while deleting the product'], 500);
        }
    }
}
