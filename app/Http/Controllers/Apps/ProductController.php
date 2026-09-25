<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use App\Services\ImageService;
use App\Services\StockMutationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use JsonException;

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
    public function store(StoreProductRequest $request)
    {
        /**
         * validate
         */
        $validated = $request->validated();
        // create product
        $product = Product::create([
            'image' => 'default.jpg',
            'barcode' => $validated['barcode'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'buy_price' => $validated['buy_price'],
            'sell_price' => $validated['sell_price'],
            'minimum_sell_price' => $validated['minimum_sell_price'] ?? $validated['sell_price'],
            'maximum_sell_price' => $validated['maximum_sell_price'] ?? $validated['sell_price'],
            'stock' => $validated['stock'],
            'min_stock' => $validated['min_stock'] ?? 0,
            'max_stock' => $validated['max_stock'] ?? 0,
        ]);

        if ($request->file('image')) {
            try {
                $this->imageService->attachProductImage($product, $request->file('image'), $this->cropFromRequest($request));
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

    public function show(Product $product)
    {
        return Inertia::render('Dashboard/Products/Show', [
            'product' => $product->load('category'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $before = $this->productAuditPayload($product);

        /**
         * validate
         */
        $validated = $request->validated();

        // check image update
        if ($request->file('image')) {

            $this->imageService->replaceProductImage($product, $request->file('image'), $this->cropFromRequest($request));

            // update product with new image
            $product->update([
                'barcode' => $validated['barcode'] ?? null,
                'sku' => $validated['sku'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'buy_price' => $validated['buy_price'],
                'sell_price' => $validated['sell_price'],
                'minimum_sell_price' => $validated['minimum_sell_price'] ?? $validated['sell_price'],
                'maximum_sell_price' => $validated['maximum_sell_price'] ?? $validated['sell_price'],
            ]);

            $this->logProductUpdate($product, $before);

            return to_route('products.index');
        }

        if ($request->boolean('remove_image')) {
            $this->imageService->removeProductImage($product);
        }

        // update product without image
        $product->update([
            'barcode' => $validated['barcode'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'buy_price' => $validated['buy_price'],
            'sell_price' => $validated['sell_price'],
            'minimum_sell_price' => $validated['minimum_sell_price'] ?? $validated['sell_price'],
            'maximum_sell_price' => $validated['maximum_sell_price'] ?? $validated['sell_price'],
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

        try {
            $product->delete();
        } catch (QueryException $exception) {
            report($exception);

            return back()->with('error', 'Product cannot be deleted because it is referenced by existing records.');
        }

        $this->imageService->deleteProductImage($product);

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

    private function cropFromRequest(Request $request): ?array
    {
        $crop = $request->input('image_crop');

        if (! is_string($crop) || trim($crop) === '') {
            return null;
        }

        try {
            $decoded = json_decode($crop, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
}
