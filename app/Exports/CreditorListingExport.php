<?php

namespace App\Exports;

use App\Exports\Listings\AutoCountListingExport;
use App\Exports\Listings\ListingLayout;
use App\Exports\Listings\ListingRecord;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * "Creditor Listing" export — clones the AutoCount creditor template, one block
 * per supplier, honouring whatever filter the caller has already applied.
 */
class CreditorListingExport
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query ?? Supplier::query();
    }

    /**
     * The filtered suppliers flattened into listing records, in report order.
     *
     * @return ListingRecord[]
     */
    public function records(): array
    {
        return $this->query
            ->with(['area', 'creditTerms.creditTerm'])
            ->orderBy('sku')
            ->get()
            ->map(fn (Supplier $supplier) => $this->toRecord($supplier))
            ->all();
    }

    public function download(string $filename): BinaryFileResponse
    {
        $template = resource_path('exports/templates/creditor_listing.xls');

        $export = new AutoCountListingExport(
            templatePath: $template,
            layout: ListingLayout::creditor(),
            records: $this->records(),
            dateText: now()->format('d-m-Y H:i:s'),
            userId: strtoupper((string) (Auth::user()->name ?? '')),
        );

        return $this->stream($export, $filename);
    }

    protected function toRecord(Supplier $supplier): ListingRecord
    {
        $terms = $supplier->creditTerms
            ->map(fn ($t) => $t->creditTerm->name ?? null)
            ->filter()
            ->all();

        $mobiles = is_array($supplier->mobile_number) ? $supplier->mobile_number : [$supplier->mobile_number];

        return new ListingRecord(
            code: (string) $supplier->sku,
            name: (string) $supplier->name,
            // Supplier address is a single free-text column; split on newlines.
            addressLines: preg_split('/\r\n|\r|\n/', (string) $supplier->location) ?: [],
            area: (string) ($supplier->area->name ?? ''),
            agent: (string) $supplier->sale_agent,
            terms: implode(' / ', $terms),
            contact: '',
            phones: array_filter([$supplier->phone, ...$mobiles], fn ($v) => $v !== null && $v !== ''),
        );
    }

    protected function stream(AutoCountListingExport $export, string $filename): BinaryFileResponse
    {
        $temp = tempnam(sys_get_temp_dir(), 'listing');
        $export->save($temp);

        return response()
            ->download($temp, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }
}
