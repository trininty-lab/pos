<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use App\Services\ImageService;
use App\Services\StockMutationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private readonly StockMutationService $stockMutationService,
        private readonly AuditLogService $auditLogService,
        private readonly ImageService $imageService
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $products = Product::when($request->search, function ($products, $search) {
            $products = $products->where('title', 'like', '%'.$search.'%');
        })->with('category')->latest()->paginate($this->perPage())->withQueryString();

        $warehouses = Warehouse::active()->orderBy('code')->get(['id', 'code', 'name']);

        return Inertia::render('Dashboard/Products/Index', [
            'products' => $products,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        // get categories
        $categories = Category::all();

        // return inertia
        return Inertia::render('Dashboard/Products/Create', [
            'categories' => $categories,
        ]);
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
            'barcode' => 'nullable|unique:products,barcode',
            'sku' => 'nullable|unique:products,sku',
            'title' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'buy_price' => 'required',
            'sell_price' => 'required',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ]);
        // create product
        $product = Product::create([
            'image' => 'default.jpg',
            'barcode' => $request->barcode,
            'sku' => $request->sku,
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'buy_price' => $request->buy_price,
            'sell_price' => $request->sell_price,
            'stock' => $request->stock,
            'min_stock' => $request->min_stock ?? 0,
            'max_stock' => $request->max_stock ?? 0,
        ]);

        if ($request->file('image')) {
            try {
                $image = $this->imageService->uploadImage($request->file('image'), 'products/'.$product->id);
                $product->forceFill([
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
                $product->delete();
                throw $exception;
            }
        }

        $this->stockMutationService->recordInitialStock($product, $request->user()?->id);
        $this->auditLogService->log(
            event: 'product.created',
            module: 'products',
            auditable: $product,
            description: 'Produk baru dibuat.',
            after: $this->productAuditPayload($product->fresh())
        );

        // redirect
        return to_route('products.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit(Product $product)
    {
        // get categories
        $categories = Category::all();

        return Inertia::render('Dashboard/Products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, Product $product)
    {
        $before = $this->productAuditPayload($product);

        /**
         * validate
         */
        $request->validate([
            'barcode' => 'nullable|unique:products,barcode,'.$product->id,
            'sku' => 'nullable|unique:products,sku,'.$product->id,
            'title' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'buy_price' => 'required',
            'sell_price' => 'required',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ]);

        // check image update
        if ($request->file('image')) {

            $image = $request->file('image');
            $uploaded = $this->imageService->replaceImage($image, 'products/'.$product->id, $product->cloudinary_public_id);

            if (! $product->cloudinary_public_id) {
                Storage::disk('local')->delete('public/products/'.basename($product->image));
            }

            // update product with new image
            $product->update([
                'image' => $uploaded->secureUrl,
                'cloudinary_public_id' => $uploaded->publicId,
                'cloudinary_version' => $uploaded->version,
                'cloudinary_format' => $uploaded->format,
                'cloudinary_resource_type' => $uploaded->resourceType,
                'cloudinary_bytes' => $uploaded->bytes,
                'cloudinary_width' => $uploaded->width,
                'cloudinary_height' => $uploaded->height,
                'barcode' => $request->barcode,
                'sku' => $request->sku,
                'title' => $request->title,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'buy_price' => $request->buy_price,
                'sell_price' => $request->sell_price,
            ]);

            $this->logProductUpdate($product, $before);

            return to_route('products.index');
        }

        // update product without image
        $product->update([
            'barcode' => $request->barcode,
            'sku' => $request->sku,
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'buy_price' => $request->buy_price,
            'sell_price' => $request->sell_price,
        ]);

        $this->logProductUpdate($product, $before);

        // redirect
        return to_route('products.index');
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
        $product = Product::findOrFail($id);
        $before = $this->productAuditPayload($product);

        if ($product->cloudinary_public_id) {
            $this->imageService->deleteImage($product->cloudinary_public_id);
        } else {
            Storage::disk('local')->delete('public/products/'.basename($product->image));
        }

        // delete
        $product->delete();

        $this->auditLogService->log(
            event: 'product.deleted',
            module: 'products',
            auditable: $product,
            description: 'Produk dihapus.',
            before: $before
        );

        // redirect
        return back();
    }

    private function logProductUpdate(Product $product, array $before): void
    {
        $after = $this->productAuditPayload($product->fresh());

        $this->auditLogService->log(
            event: 'product.updated',
            module: 'products',
            auditable: $product,
            description: 'Data produk diperbarui.',
            before: $before,
            after: $after
        );

        if (
            (int) $before['buy_price'] !== (int) $after['buy_price']
            || (int) $before['sell_price'] !== (int) $after['sell_price']
        ) {
            $this->auditLogService->log(
                event: 'product.price_updated',
                module: 'products',
                auditable: $product,
                description: 'Harga produk diperbarui.',
                before: [
                    'buy_price' => $before['buy_price'],
                    'sell_price' => $before['sell_price'],
                ],
                after: [
                    'buy_price' => $after['buy_price'],
                    'sell_price' => $after['sell_price'],
                ]
            );
        }
    }

    private function productAuditPayload(Product $product): array
    {
        return $this->auditLogService->only($product->toArray(), [
            'title',
            'barcode',
            'sku',
            'buy_price',
            'sell_price',
            'stock',
            'category_id',
        ]);
    }
}
