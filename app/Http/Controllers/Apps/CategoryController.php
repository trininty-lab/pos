<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct(
        private readonly \App\Services\CloudinaryImageService $cloudinaryImageService
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        // get categories
        $categories = Category::when(request()->search, function ($categories) {
            $categories = $categories->where('name', 'like', '%'.request()->search.'%');
        })->latest()->paginate($this->perPage())->withQueryString();

        // return inertia
        return Inertia::render('Dashboard/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return Inertia::render('Dashboard/Categories/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        /**
         * validate
         */
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'name' => 'required',
            'description' => 'required',
        ]);

        // upload image
        $image = $request->file('image');
        $imagePath = $this->cloudinaryImageService->upload($image, 'category')['url'];

        // create category
        Category::create([
            'image' => $imagePath,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // redirect
        return to_route('categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit(Category $category)
    {
        return Inertia::render('Dashboard/Categories/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, Category $category)
    {
        /**
         * validate
         */
        $request->validate([
            'name' => 'required',
            'description' => 'required',
        ]);

        // check image update
        if ($request->file('image')) {

            // remove old image
            $oldImage = $category->getRawOriginal('image');
            $this->cloudinaryImageService->deleteUrl($oldImage);
            Storage::disk('local')->delete('public/category/'.basename((string) $oldImage));

            // upload new image
            $image = $request->file('image');
            $imagePath = $this->cloudinaryImageService->upload($image, 'category')['url'];

            // update category with new image
            $category->update([
                'image' => $imagePath,
                'name' => $request->name,
                'description' => $request->description,
            ]);
        }

        // update category without image
        $category->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // redirect
        return to_route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        // find by ID
        $category = Category::findOrFail($id);

        // remove image
        $oldImage = $category->getRawOriginal('image');
        $this->cloudinaryImageService->deleteUrl($oldImage);
        Storage::disk('local')->delete('public/category/'.basename((string) $oldImage));

        // delete
        $category->delete();

        // redirect
        return to_route('categories.index');
    }
}
