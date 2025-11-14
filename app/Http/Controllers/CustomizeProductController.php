<?php

namespace App\Http\Controllers;

use App\Models\CustomizeProduct;
use App\Models\Production;
use Barryvdh\DomPDF\Facade\Pdf;
use Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight\TypeCode128;
use Com\Tecnick\Barcode\Type\Square\QrCode\TypeQrcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\Renderers\DynamicHtmlRenderer;
use Picqer\Barcode\Types\TypeCode128 as TypesTypeCode128;

class CustomizeProductController extends Controller
{
    public function index()
    {
        $search = Session::get('customize-product-search');
        $page = Session::get('customize-product-page');

        return view('customize_inventory.list', [
            'default_search' => $search ?? null,
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        $records = CustomizeProduct::with('production')
            ->whereHas('production', function ($q) {
                $q->where('status', Production::STATUS_COMPLETED);
            });

        // Search with session persistence
        $keyword = null;
        if ($req->has('search')) {
            if ($req->search['value'] != null) {
                $keyword = $req->search['value'];
                Session::put('customize-product-search', $keyword);
            } else {
                Session::remove('customize-product-search');
            }
        } else if (Session::get('customize-product-search') != null) {
            $keyword = Session::get('customize-product-search');
        }

        if ($keyword != null) {
            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhereHas('production', function ($qq) use ($keyword) {
                        $qq->where('sku', 'like', '%' . $keyword . '%');
                    });
            });
        }

        // Persist page
        Session::put('customize-product-page', $req->page);

        // Order
        if ($req->has('order')) {
            $map = [
                1 => 'sku',
                4 => 'weight',
            ];
            foreach ($req->order as $order) {
                if (isset($map[$order['column']])) {
                    $records = $records->orderBy($map[$order['column']], $order['dir']);
                }
            }
        } else {
            $records = $records->orderBy('id', 'desc');
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];

        foreach ($records_paginator as $record) {
            $dimensions = null;
            if ($record->length || $record->width || $record->height) {
                $dimensions = sprintf(
                    '%s × %s × %s mm',
                    $record->length ?? '-',
                    $record->width ?? '-',
                    $record->height ?? '-'
                );
            }

            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'production_sku' => $record->production->sku ?? null,
                'dimensions' => $dimensions,
                'weight' => $record->weight ? number_format($record->weight, 2) . ' kg' : null,
                'capacity' => $record->capacity,
                'refrigerant' => $record->refrigerant,
                'power_input' => $record->power_input,
                'power_consumption' => $record->power_consumption,
                'voltage_frequency' => $record->voltage_frequency,
                'standard_features' => $record->standard_features,
                'can_edit' => hasPermission('inventory.customize.edit'),
            ];
        }

        return response()->json($data);
    }

    public function edit($id)
    {
        $customizeProduct = CustomizeProduct::findOrFail($id);
        $back_url = url()->previous();

        return view('customize_inventory.form', compact('customizeProduct', 'back_url'));
    }

    public function update(Request $req, $id)
    {
        $customizeProduct = CustomizeProduct::findOrFail($id);

        $validated = $req->validate([
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|string|max:255',
            'refrigerant' => 'nullable|string|max:255',
            'power_input' => 'nullable|string|max:255',
            'power_consumption' => 'nullable|string|max:255',
            'voltage_frequency' => 'nullable|string|max:255',
            'standard_features' => 'nullable|string|max:255',
        ]);

        $customizeProduct->update($validated);

        return redirect()->route('customize.index')->with('success', __('Customize product updated successfully'));
    }

    public function generateBarcode($id)
    {
        $customizeProduct = CustomizeProduct::with('production.product')->findOrFail($id);

        $barcode = (new TypesTypeCode128)->getBarcode($customizeProduct->sku);
        $renderer = new DynamicHtmlRenderer;

        // Format dimensions to remove .00
        $length = $customizeProduct->length ?? 0;
        $width = $customizeProduct->width ?? 0;
        $height = $customizeProduct->height ?? 0;

        if (str_contains($length, '.00')) {
            $length = (int) $length;
        }
        if (str_contains($width, '.00')) {
            $width = (int) $width;
        }
        if (str_contains($height, '.00')) {
            $height = (int) $height;
        }

        $data = [
            'barcode' => [$customizeProduct->sku],
            'renderer' => [$renderer->render($barcode)],
            'product_brand' => [$customizeProduct->production->product->brand ?? 1],
            'product_name' => [$customizeProduct->production->name ?? ''],
            'product_code' => [$customizeProduct->production->sku ?? ''],
            'dimension' => [$length . ' x ' . $width . ' x ' . $height . 'MM'],
            'capacity' => [$customizeProduct->capacity],
            'weight' => [$customizeProduct->weight],
            'refrigerant' => [$customizeProduct->refrigerant],
            'power_input' => [$customizeProduct->power_input],
            'power_consumption' => [$customizeProduct->power_consumption],
            'voltage_frequency' => [$customizeProduct->voltage_frequency],
            'standard_features' => [$customizeProduct->standard_features],
        ];

        $pdf = Pdf::loadView('customize_inventory.barcode', $data);
        $pdf->setPaper('A4', 'letter');

        return $pdf->stream();
    }
}
