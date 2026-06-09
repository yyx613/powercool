<?php

namespace App\Exports\Listings;

/**
 * Describes where each field of a record sits inside one repeating block of an
 * AutoCount listing template, plus the header cells to stamp.
 *
 * Anchors are [column letter, row offset from the block's first row]. Offsets
 * come from inspecting the template's first sample record (its merge anchors).
 */
class ListingLayout
{
    /**
     * @param array<string, array{0:string,1:int}|null> $anchors keyed by
     *        code, name, area, agent, terms, contact, phone, fax, address
     *        (address is a list of [col,offset] slots)
     */
    public function __construct(
        public int $firstDataRow,
        public int $blockHeight,
        public array $anchors,
        public ?string $dateCell = null,
        public ?string $userCell = null,
        // The horizontal rule under the column-header band. AutoCount draws it
        // as a shape that PhpSpreadsheet's .xls reader drops, so we redraw it as
        // a cell border spanning columns A..$ruleLastColumn on row $ruleRow.
        public ?int $ruleRow = null,
        public string $ruleLastColumn = 'AD',
    ) {}

    public static function debtor(): self
    {
        return new self(
            firstDataRow: 19,
            blockHeight: 9,
            anchors: [
                'code' => ['A', 0],
                'name' => ['B', 0],
                'area' => ['K', 1],
                'agent' => ['K', 5],
                'terms' => ['K', 6],
                'contact' => ['P', 0],
                'phone' => ['X', 0],
                'fax' => ['X', 4],
                'address' => [['F', 0], ['F', 4], ['F', 6], ['F', 7]],
            ],
            dateCell: 'V1',
            userCell: 'V3',
            ruleRow: 17,
        );
    }

    public static function creditor(): self
    {
        return new self(
            firstDataRow: 19,
            blockHeight: 11,
            anchors: [
                'code' => ['A', 0],
                'name' => ['B', 0],
                'area' => ['K', 1],
                'agent' => ['K', 4],
                'terms' => ['K', 7],
                'contact' => ['P', 0],
                'phone' => ['X', 0],
                'fax' => ['X', 2],
                'address' => [['F', 0], ['F', 2], ['F', 6], ['F', 8]],
            ],
            dateCell: 'V1',
            userCell: 'V2',
            ruleRow: 17,
        );
    }
}
