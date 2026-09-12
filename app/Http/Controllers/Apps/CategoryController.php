<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct(private readonly ImageService $imageService) {}
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
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'name' => 'required',
            'description' => 'required',
        ]);

        // create category
        $category = Category::create([
            'image' => 'default.jpg',
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->file('image')) {
            try {
                $image = $this->imageService->uploadImage($request->file('image'), 'categories/'.$category->id);
                $category->forceFill([
                    'image' => $image->secureUrl,
                    'cloudinary_public_id' => $image->publicId,
                    'cloudinary_version' => $image->version,
                    'cloudinary_format' => $image->format,
                    'cloudinary_resource_type' => $image->resourceType,
                    'cloudinary_bytes' => $image->bytes,
                    'cloudinary_width' => $image->width,
                    'cloudinary_height' => $image->height,
                ])->save();
            } catch (\Throwable $exception) {
                $category->delete();
                throw $exception;
            }
        }

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

            $image = $request->file('image');
            $uploaded = $this->imageService->replaceImage($image, 'categories/'.$category->id, $category->cloudinary_public_id);

            if (! $category->cloudinary_public_id) {
                Storage::disk('local')->delete('public/category/'.basename($category->image));
            }

            // update category with new image
            $category->update([
                'image' => $uploaded->secureUrl,
                'cloudinary_public_id' => $uploaded->publicId,
                'cloudinary_version' => $uploaded->version,
                'cloudinary_format' => $uploaded->format,
                'cloudinary_resource_type' => $uploaded->resourceType,
                'cloudinary_bytes' => $uploaded->bytes,
                'cloudinary_width' => $uploaded->width,
                'cloudinary_height' => $uploaded->height,
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

        if ($category->cloudinary_public_id) {
            $this->imageService->deleteImage($category->cloudinary_public_id);
        } else {
            Storage::disk('local')->delete('public/category/'.basename($category->image));
        }

        // delete
        $category->delete();

        // redirect
        return to_route('categories.index');
    }
}
