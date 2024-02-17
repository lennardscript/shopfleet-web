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
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *    name="API Products",
 *    description="Operations about products"
 * )
 */

class ProductController extends Controller
{

    /**
     * @OA\Get(
     *    path="products",
     *    @OA\Response(response="200", description="Display a listing of the resource.")
     * )
     */
    public function index()
    {
        //
        $products = Product::orderBy(function ($query) {
            $query->selectRaw('LOWER(name_product)');
        })->paginate(10);
        return response()->json(['products' => $products], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
    * @OA\Post(
    *    path="products",
    *    @OA\Response(response="201", description="Store a newly created resource in storage.")
    * )
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
        return response()->json(['product' => $product], Response::HTTP_OK);
    }

    /**
    * @OA\Get(
    *    path="products/search/{name_product}",
    *    @OA\Response(response="200", description="Search products for name.")
    * )
    */
    public function search_name_product($name_product)
    {

        if (empty($name_product)) {
            return response()->json(['info' => 'Please provide a name to search'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $products = Product::whereRaw('LOWER(name_product) LIKE LOWER(?)', ['%' . $name_product . '%'])->paginate(10);

        if ($products->isEmpty()) {
            Log::error('No products found for name: ' . $name_product);
            return response()->json(['info' => 'Products not found', 'name_product' => $name_product], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['products' => $products], Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
    * @OA\Patch(
    *    path="products/{slug}",
    *    @OA\Response(response="200", description="Update the specified resource in storage.")
    * )
    */
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

    /**
    * @OA\Delete(
    *    path="products/{product:slug}",
    *    @OA\Response(response="200", description="Remove the specified resource from storage.")
    * )
    */
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
