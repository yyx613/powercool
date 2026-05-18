<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProductChild;
use App\Models\GRN;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\ProductionMilestoneMaterial;
use App\Models\Scopes\BranchScope;
use App\Models\UOM;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockCardService
{
    const TYPE_GR = 'GR';
    const TYPE_DO = 'DO';
    const TYPE_AS = 'AS';
    const TYPE_ST = 'ST';

    const COMPANY_LABELS = [
        1 => 'Power Cool',
        2 => 'Hi-Ten',
    ];

    public static function companyLabelFor($companyGroup): string
    {
        if ($companyGroup === null || $companyGroup === '') {
            return 'Unassigned';
        }
        return self::COMPANY_LABELS[(int) $companyGroup] ?? 'Unassigned';
    }

    /** @var array<int, float> Cached map of product_id => current cost */
    private array $productCostMap = [];

    /**
     * Build the Stock Card aggregation.
     *
     * Returns an ordered array of items:
     * [
     *   [
     *     'product' => Product,
     *     'locations' => [
     *       [
     *         'location_code' => 1,
     *         'location_label' => 'Kuala Lumpur',
     *         'uom' => 'UNIT',
     *         'bf_qty' => 5,
     *         'movements' => [
     *           ['date' => '2026-01-15', 'type' => 'GR', 'doc_no' => 'GR-26/000123',
     *            'description' => 'ACME SUPPLIER', 'in_out_qty' => 10, 'bal_qty' => 15],
     *           ...
     *         ],
     *         'closing_qty' => 15,
     *       ],
     *     ],
     *   ],
     * ]
     */
    public function getMovements(?string $startDate, ?string $endDate, ?string $keyword = null, ?int $companyGroup = null): array
    {
        $this->productCostMap = [];

        $startDate = $this->normalizeDateInput($startDate);
        $endDate = $this->normalizeDateInput($endDate);
        $keyword = $this->normalizeStringInput($keyword);

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        $allMovements = collect()
            ->merge($this->collectGrn($start, $end))
            ->merge($this->collectDeliveryOrders($start, $end))
            ->merge($this->collectAssemblies($start, $end))
            ->merge($this->collectTransfers($start, $end));

        $productIds = $allMovements->pluck('product_id')->unique()->filter()->values();
        if ($productIds->isEmpty()) {
            return [];
        }

        $productsQuery = Product::withoutGlobalScope(BranchScope::class)
            ->whereIn('id', $productIds);

        if ($keyword !== null && $keyword !== '') {
            $productsQuery->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%'.$keyword.'%')
                    ->orWhere('model_desc', 'like', '%'.$keyword.'%');
            });
        }

        if ($companyGroup !== null) {
            $productsQuery->where('company_group', $companyGroup);
        }

        $products = $productsQuery->orderBy('sku')->get()->keyBy('id');
        if ($products->isEmpty()) {
            return [];
        }

        $this->primeProductCostMap($products->keys()->all());

        $productBranchMap = $this->getProductBranchMap($products->keys()->all());

        $uomIds = $products->pluck('uom')->unique()->filter()->values()->all();
        $uomMap = UOM::withoutGlobalScope(BranchScope::class)
            ->whereIn('id', $uomIds)
            ->pluck('name', 'id')
            ->all();

        $movementsByProduct = $allMovements->groupBy('product_id');

        $result = [];
        foreach ($products as $productId => $product) {
            $movements = ($movementsByProduct->get($productId) ?? collect())
                ->sortBy([['date', 'asc'], ['id', 'asc']])
                ->values();

            $locationCode = $productBranchMap[$productId] ?? Branch::LOCATION_EVERY;
            $locationLabel = (new Branch)->keyToLabel($locationCode) ?? 'Unassigned';

            $bf = $this->getBroughtForward($productId, $start);
            $bfQty = $bf['qty'];
            $bfCost = $bf['cost'];

            $running = $bfQty;
            $runningCost = $bfCost;
            $inPeriod = $movements->filter(function ($m) use ($start, $end) {
                if ($start && Carbon::parse($m['date'])->lt($start)) {
                    return false;
                }
                if ($end && Carbon::parse($m['date'])->gt($end)) {
                    return false;
                }
                return true;
            })->values();

            $rows = [];
            foreach ($inPeriod as $mv) {
                $running += $mv['in_out_qty'];
                $unitCost = (float) ($mv['unit_cost'] ?? 0);
                $totalCost = (float) ($mv['total_cost'] ?? 0);
                $runningCost += $totalCost;

                $rows[] = [
                    'date' => Carbon::parse($mv['date'])->format('d-m-Y'),
                    'type' => $mv['type'],
                    'doc_no' => $mv['doc_no'],
                    'description' => $mv['description'] ?? '',
                    'in_out_qty' => $mv['in_out_qty'],
                    'bal_qty' => $running,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'bal_cost' => $runningCost,
                ];
            }

            if (count($rows) === 0 && $bfQty == 0) {
                continue;
            }

            $uomLabel = $uomMap[$product->uom] ?? 'UNIT';

            $result[] = [
                'product' => $product,
                'company_group' => $product->company_group,
                'company_label' => self::companyLabelFor($product->company_group),
                'locations' => [
                    [
                        'location_code' => $locationCode,
                        'location_label' => $locationLabel,
                        'uom' => $uomLabel,
                        'bf_qty' => $bfQty,
                        'bf_cost' => $bfCost,
                        'movements' => $rows,
                        'closing_qty' => $running,
                        'closing_cost' => $runningCost,
                    ],
                ],
            ];
        }

        return $result;
    }

    private function primeProductCostMap(array $productIds): void
    {
        $missing = array_diff($productIds, array_keys($this->productCostMap));
        if (empty($missing)) {
            return;
        }
        $rows = Product::withoutGlobalScope(BranchScope::class)
            ->whereIn('id', $missing)
            ->get(['id', 'cost']);
        foreach ($rows as $r) {
            $this->productCostMap[(int) $r->id] = (float) ($r->cost ?? 0);
        }
        // Mark anything still missing as 0 so we don't re-query.
        foreach ($missing as $id) {
            if (! array_key_exists($id, $this->productCostMap)) {
                $this->productCostMap[(int) $id] = 0.0;
            }
        }
    }

    private function productCostFor(int $productId): float
    {
        if (! array_key_exists($productId, $this->productCostMap)) {
            $this->primeProductCostMap([$productId]);
        }
        return (float) ($this->productCostMap[$productId] ?? 0);
    }

    private function normalizeDateInput(?string $value): ?string
    {
        $value = $this->normalizeStringInput($value);
        if ($value === null) {
            return null;
        }
        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizeStringInput(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim($value);
        if ($trimmed === '' || strtolower($trimmed) === 'null') {
            return null;
        }
        return $trimmed;
    }

    private function getProductBranchMap(array $productIds): array
    {
        $rows = DB::table('branches')
            ->where('object_type', Product::class)
            ->whereIn('object_id', $productIds)
            ->whereNull('deleted_at')
            ->get(['object_id', 'location']);

        $map = [];
        foreach ($rows as $r) {
            $map[$r->object_id] = (int) $r->location;
        }
        return $map;
    }

    private function getBroughtForward(int $productId, ?Carbon $start): array
    {
        if ($start === null) {
            return ['qty' => 0, 'cost' => 0.0];
        }

        $qty = 0;
        $cost = 0.0;

        // GR inbound — qty + total_price directly from grn
        $grRow = GRN::withoutGlobalScope(BranchScope::class)
            ->where('product_id', $productId)
            ->where('created_at', '<', $start)
            ->selectRaw('COALESCE(SUM(qty), 0) AS sum_qty, COALESCE(SUM(total_price), 0) AS sum_cost')
            ->first();
        $qty += (int) ($grRow->sum_qty ?? 0);
        $cost += (float) ($grRow->sum_cost ?? 0);

        // DO outbound — one row per ProductChild (qty 1 each); cost from sale_products.cost
        $doRows = DB::table('delivery_order_product_children as dopc')
            ->join('product_children as pc', 'pc.id', '=', 'dopc.product_children_id')
            ->join('delivery_order_products as dop', 'dop.id', '=', 'dopc.delivery_order_product_id')
            ->leftJoin('sale_products as sp', 'sp.id', '=', 'dop.sale_product_id')
            ->where('pc.product_id', $productId)
            ->where('dopc.created_at', '<', $start)
            ->whereNull('dopc.deleted_at')
            ->selectRaw('COUNT(*) AS cnt, COALESCE(SUM(sp.cost), 0) AS sum_cost')
            ->first();
        $doCount = (int) ($doRows->cnt ?? 0);
        $doCost = (float) ($doRows->sum_cost ?? 0);
        // If sale_products.cost is null/zero, fall back to product.cost per unit
        if ($doCount > 0 && $doCost == 0.0) {
            $doCost = $doCount * $this->productCostFor($productId);
        }
        $qty -= $doCount;
        $cost -= $doCost;

        // AS material consumption — qty * Product.cost
        $asQty = (int) ProductionMilestoneMaterial::withoutGlobalScope(BranchScope::class)
            ->where('product_id', $productId)
            ->where('created_at', '<', $start)
            ->sum('qty');
        $qty -= $asQty;
        $cost -= $asQty * $this->productCostFor($productId);

        // ST inbound (destination receives a new ProductChild)
        $stInCount = (int) ProductChild::withoutGlobalScope(BranchScope::class)
            ->where('product_id', $productId)
            ->whereNotNull('transferred_from')
            ->where('transfer_at', '<', $start)
            ->count();
        $qty += $stInCount;
        $cost += $stInCount * $this->productCostFor($productId);

        // ST outbound (source product loses one unit; cost uses source product cost = same productId here)
        $stOutCount = (int) ProductChild::withoutGlobalScope(BranchScope::class)
            ->whereNotNull('transferred_from')
            ->whereIn('transferred_from', function ($q) use ($productId) {
                $q->select('id')
                    ->from('product_children')
                    ->where('product_id', $productId);
            })
            ->where('transfer_at', '<', $start)
            ->count();
        $qty -= $stOutCount;
        $cost -= $stOutCount * $this->productCostFor($productId);

        return ['qty' => $qty, 'cost' => $cost];
    }

    private function collectGrn(?Carbon $start, ?Carbon $end): array
    {
        $q = GRN::withoutGlobalScope(BranchScope::class);

        if ($start) $q->where('created_at', '>=', $start);
        if ($end) $q->where('created_at', '<=', $end);

        $grns = $q->get();

        $supplierIds = $grns->pluck('supplier_id')->unique()->filter()->values()->all();
        $suppliers = DB::table('suppliers')
            ->whereIn('id', $supplierIds)
            ->whereNull('deleted_at')
            ->get(['id', 'name', 'company_name'])
            ->keyBy('id');

        return $grns->map(function ($g) use ($suppliers) {
            $sup = $suppliers->get($g->supplier_id);
            $desc = '';
            if ($sup) {
                $desc = $sup->company_name ?: ($sup->name ?? '');
            }
            $qty = (int) $g->qty;
            $unitCost = (float) ($g->unit_price ?? 0);
            $totalCost = $g->total_price !== null ? (float) $g->total_price : $qty * $unitCost;
            return [
                'id' => 'GR'.$g->id,
                'product_id' => $g->product_id,
                'date' => $g->created_at,
                'type' => self::TYPE_GR,
                'doc_no' => $g->sku,
                'description' => $desc,
                'in_out_qty' => $qty,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
            ];
        })->all();
    }

    private function collectDeliveryOrders(?Carbon $start, ?Carbon $end): array
    {
        $q = DeliveryOrderProductChild::withoutGlobalScope(BranchScope::class)
            ->with([
                'productChild' => function ($q) {
                    $q->withoutGlobalScope(BranchScope::class);
                },
                'doProduct' => function ($q) {
                    $q->with(['saleProduct']);
                },
                'doProduct.do' => function ($q) {
                    $q->withoutGlobalScope(BranchScope::class);
                },
                'doProduct.do.customer',
            ]);

        if ($start) $q->where('created_at', '>=', $start);
        if ($end) $q->where('created_at', '<=', $end);

        return $q->get()->map(function ($dopc) {
            $do = optional(optional($dopc->doProduct)->do);
            if ($do === null || ! $do->exists) {
                return null;
            }
            $productId = optional($dopc->productChild)->product_id;
            if (! $productId) {
                return null;
            }

            $sp = optional($dopc->doProduct)->saleProduct;
            $unitCost = $sp && $sp->cost !== null ? (float) $sp->cost : 0.0;
            if ($unitCost == 0.0) {
                $unitCost = $this->productCostFor((int) $productId);
            }

            return [
                'id' => 'DO'.$dopc->id,
                'product_id' => $productId,
                'date' => $dopc->created_at,
                'type' => self::TYPE_DO,
                'doc_no' => $do->sku,
                'description' => optional($do->customer)->company_name
                    ?? optional($do->customer)->name
                    ?? '',
                'in_out_qty' => -1,
                'unit_cost' => $unitCost,
                'total_cost' => -1 * $unitCost,
            ];
        })->filter()->values()->all();
    }

    private function collectAssemblies(?Carbon $start, ?Carbon $end): array
    {
        $q = ProductionMilestoneMaterial::withoutGlobalScope(BranchScope::class);

        if ($start) $q->where('created_at', '>=', $start);
        if ($end) $q->where('created_at', '<=', $end);

        return $q->get()->map(function ($pmm) {
            if (! $pmm->product_id) {
                return null;
            }
            $qty = -1 * (int) $pmm->qty;
            $unitCost = $this->productCostFor((int) $pmm->product_id);
            return [
                'id' => 'AS'.$pmm->id,
                'product_id' => $pmm->product_id,
                'date' => $pmm->created_at,
                'type' => self::TYPE_AS,
                'doc_no' => 'ASM-'.($pmm->production_milestone_id ?? $pmm->id),
                'description' => 'Stock Assembly',
                'in_out_qty' => $qty,
                'unit_cost' => $unitCost,
                'total_cost' => $qty * $unitCost,
            ];
        })->filter()->values()->all();
    }

    private function collectTransfers(?Carbon $start, ?Carbon $end): array
    {
        $q = ProductChild::withoutGlobalScope(BranchScope::class)
            ->whereNotNull('transferred_from')
            ->whereNotNull('transfer_at')
            ->with(['parent' => function ($q) {
                $q->withoutGlobalScope(BranchScope::class);
            }]);

        if ($start) $q->where('transfer_at', '>=', $start);
        if ($end) $q->where('transfer_at', '<=', $end);

        $rows = [];
        foreach ($q->get() as $pc) {
            $parent = ProductChild::withoutGlobalScope(BranchScope::class)
                ->find($pc->transferred_from);

            $sourceProductId = $parent ? $parent->product_id : null;

            $destCost = $this->productCostFor((int) $pc->product_id);
            $rows[] = [
                'id' => 'STI'.$pc->id,
                'product_id' => $pc->product_id,
                'date' => $pc->transfer_at,
                'type' => self::TYPE_ST,
                'doc_no' => 'XFER-'.$pc->id,
                'description' => 'Stock Transfer (in)',
                'in_out_qty' => 1,
                'unit_cost' => $destCost,
                'total_cost' => $destCost,
            ];

            if ($sourceProductId) {
                $srcCost = $this->productCostFor((int) $sourceProductId);
                $rows[] = [
                    'id' => 'STO'.$pc->id,
                    'product_id' => $sourceProductId,
                    'date' => $pc->transfer_at,
                    'type' => self::TYPE_ST,
                    'doc_no' => 'XFER-'.$pc->id,
                    'description' => 'Stock Transfer (out)',
                    'in_out_qty' => -1,
                    'unit_cost' => $srcCost,
                    'total_cost' => -1 * $srcCost,
                ];
            }
        }

        return $rows;
    }

}
