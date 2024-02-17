<?php

namespace App\Http\Controllers\Api\Categories;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriesRequests\StoreCategoryRequest;
use App\Http\Requests\CategoriesRequests\UpdateCategoryRequest;
use App\Models\Categories\Category;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *    name="API Categories",
 *    description="Operations about categories"
 * )
 */

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *    path="categories",
     *    @OA\Response(response="200", description="Display a listing of the resource.")
     * )
     */
    public function index()
    {
        //
        $categories = Category::orderBy(function ($query) {
            $query->selectRaw('LOWER(name_category)');
        })->paginate(10);
        return response()->json(['categories' => $categories], Response::HTTP_OK);
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
    *    path="categories",
    *    @OA\Response(response="201", description="Store a newly created resource in storage.")
    * )
    */
    public function store(StoreCategoryRequest $request)
    {
        //

        try {
            $category = Category::create($request->all());

            Log::info('Category created successfully!');
            return response()->json(['category' => $category], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('Error creating category: ', ['exception' => $e]);
            return response()->json(['message' => 'An error occurred while creating the category', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
    * @OA\Get(
    *    path="categories/search/{name_category}",
    *    @OA\Response(response="200", description="Search categories for name.")
    * )
    */
    public function search_category($name_category)
    {

        try {
            $categories = Category::whereRaw('LOWER(name_category) LIKE LOWER(?)', '%' . $name_category . '%')->paginate(10);

            if ($categories->isEmpty()) {
                throw new Exception('No categories found with that name.');
            }

            return response()->json(['categories' => $categories], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['info' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
    * @OA\Patch(
    *    path="categories/{name_category}",
    *    @OA\Response(response="200", description="Update the specified resource in storage.")
    * )
    */
    public function update(UpdateCategoryRequest $request, string $name_category)
    {
        // TODO: buscar categoría por su nombre
        $category = Category::where('name_category', $name_category)->firstOrFail();

        // TODO: actualizar categoría solo con el nombre
        $category->name_category = $request->input('name_category');

        // TODO: guarda los cambios en la base de datos y actualiza los registros
        $category->save();

        Log::info('Category update suscessfully!');
        return response()->json(['category' => $category], Response::HTTP_OK);
    }

    /**
    * @OA\Delete(
    *    path="categories/{name_category}",
    *    @OA\Response(response="200", description="Remove the specified resource from storage.")
    * )
    */
    public function destroy(string $name_category)
    {
        //TODO: busca categorías por su nombre
        $category = Category::where('name_category', $name_category)->firstOrFail();

        // TODO: elimina categoría
        $category->delete();

        Log::info('Category deleted successfully!');
        return response()->json(['message' => 'Category deleted successfully!'], Response::HTTP_OK);
    }
}
