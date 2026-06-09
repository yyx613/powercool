<?php

namespace App\Exports\Listings;

/**
 * A single debtor/creditor row, already flattened from its Eloquent model into
 * the plain strings the listing template expects.
 */
class ListingRecord
{
    /**
     * @param string[] $addressLines
     * @param string[] $phones
     * @param string[] $faxes
     */
    public function __construct(
        public string $code = '',
        public string $name = '',
        public array $addressLines = [],
        public string $area = '',
        public string $agent = '',
        public string $terms = '',
        public string $contact = '',
        public array $phones = [],
        public array $faxes = [],
    ) {}

    /** @return string[] non-empty, trimmed address lines */
    public function addressLines(): array
    {
        return array_values(array_filter(array_map('trim', $this->addressLines), fn ($l) => $l !== ''));
    }

    /** Phone 1 & 2 stacked into one cell, matching the template. */
    public function phone(): string
    {
        return $this->joinLines($this->phones);
    }

    /** Fax 1 & 2 stacked into one cell. */
    public function fax(): string
    {
        return $this->joinLines($this->faxes);
    }

    private function joinLines(array $values): string
    {
        $clean = array_filter(array_map('trim', $values), fn ($v) => $v !== '' && $v !== '-');

        return implode("\n", array_unique($clean));
    }
}
