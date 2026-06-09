<?php

namespace App\Exports;

use App\Exports\Listings\AutoCountListingExport;
use App\Exports\Listings\ListingLayout;
use App\Exports\Listings\ListingRecord;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * "Debtor Listing" export — clones the AutoCount debtor template, one block per
 * customer, honouring whatever filter the caller has already applied.
 */
class DebtorListingExport
{
    protected $query;

    public function __construct($query = null, protected ?string $companyName = null)
    {
        $this->query = $query ?? Customer::query();
    }

    /**
     * The filtered customers flattened into listing records, in report order.
     *
     * @return ListingRecord[]
     */
    public function records(): array
    {
        return $this->query
            ->with(['locations', 'area', 'creditTerms.creditTerm', 'salesAgents.salesAgent'])
            ->orderBy('sku')
            ->get()
            ->map(fn (Customer $customer) => $this->toRecord($customer))
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
        );

        return $this->stream($export, $filename);
    }

    protected function toRecord(Customer $customer): ListingRecord
    {
        $location = $customer->locations->firstWhere('is_default', 1)
            ?? $customer->locations->first();

        $addressLines = $location ? [
            $location->address1,
            $location->address2,
            $location->address3,
            $location->address4,
        ] : [];

        $agents = $customer->salesAgents
            ->map(fn ($pivot) => $pivot->salesAgent->name ?? null)
            ->filter()
            ->all();

        $terms = $customer->creditTerms
            ->map(fn ($t) => $t->creditTerm->name ?? null)
            ->filter()
            ->all();

        $mobiles = is_array($customer->mobile_number) ? $customer->mobile_number : [$customer->mobile_number];

        return new ListingRecord(
            code: (string) $customer->sku,
            name: (string) ($customer->company_name ?: $customer->name),
            addressLines: array_map('strval', $addressLines),
            area: (string) ($customer->area->name ?? ''),
            agent: implode(' / ', $agents),
            terms: implode(' / ', $terms),
            contact: '',
            phones: array_filter([$customer->phone, ...$mobiles], fn ($v) => $v !== null && $v !== ''),
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
