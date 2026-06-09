<?php

namespace App\Exports;

use App\Exports\Listings\AutoCountListingExport;
use App\Exports\Listings\ListingLayout;
use App\Exports\Listings\ListingRecord;
use App\Models\Dealer;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * "Dealer Listing" export — reuses the AutoCount debtor template, one block per
 * dealer, retitled "Dealer Listing", honouring whatever filter the caller has
 * already applied.
 */
class DealerListingExport
{
    protected $query;

    public function __construct($query = null, protected ?string $companyName = null)
    {
        $this->query = $query ?? Dealer::query();
    }

    /**
     * The filtered dealers flattened into listing records, in report order.
     *
     * @return ListingRecord[]
     */
    public function records(): array
    {
        return $this->query
            ->orderBy('sku')
            ->get()
            ->map(fn (Dealer $dealer) => $this->toRecord($dealer))
            ->all();
    }

    public function download(string $filename): BinaryFileResponse
    {
        $template = resource_path('exports/templates/debtor_listing.xls');

        $export = new AutoCountListingExport(
            templatePath: $template,
            layout: ListingLayout::debtor(),
            records: $this->records(),
            dateText: now()->format('d-m-Y H:i:s'),
            userId: strtoupper((string) (Auth::user()->name ?? '')),
            companyName: $this->companyName,
            title: 'Dealer Listing',
        );

        return $this->stream($export, $filename);
    }

    protected function toRecord(Dealer $dealer): ListingRecord
    {
        // Dealers carry only a code, name and company name; the remaining columns
        // of the debtor template have no dealer equivalent and stay blank.
        return new ListingRecord(
            code: (string) $dealer->sku,
            name: (string) ($dealer->company_name ?: $dealer->name),
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
