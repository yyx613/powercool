<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class CustomerExport implements FromView, WithStyles
{
    public function styles($sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }

    public function view(): View
    {
        $customers = Customer::with([
            'locations',
            'currency',
            'area',
            'debtorType',
            'platform',
            'msicCode',
            'creditTerms.creditTerm',
        ])->get();

        // FromView renders this data to HTML and PhpSpreadsheet re-parses it with
        // DOMDocument::loadHTML(), which aborts on invalid control characters
        // (e.g. a 0x1F from synced free-text). Scrub the whole loaded graph first.
        $customers->each(fn (Customer $customer) => $this->sanitizeModel($customer));

        return view('export.customer', [
            'customers' => $customers,
        ]);

    }

    /**
     * Recursively strip XML/HTML-invalid control characters from a model's
     * attributes and every loaded relation.
     */
    private function sanitizeModel(Model $model): void
    {
        foreach (array_keys($model->getAttributes()) as $key) {
            $value = $model->getAttribute($key);

            if (is_string($value) || is_array($value)) {
                $model->setAttribute($key, cleanControlChars($value));
            }
        }

        foreach ($model->getRelations() as $relation) {
            if ($relation instanceof Collection) {
                $relation->filter()->each(fn (Model $related) => $this->sanitizeModel($related));
            } elseif ($relation instanceof Model) {
                $this->sanitizeModel($relation);
            }
        }
    }
}
