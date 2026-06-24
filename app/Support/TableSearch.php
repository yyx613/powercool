<?php

namespace App\Support;

/**
 * Shared global-search helper for server-side DataTables list endpoints.
 *
 * Applies a single keyword across every visible column of a table:
 *  - $textColumns  : plain string/number/date columns matched with LIKE %keyword%.
 *  - $codedColumns : integer columns that are rendered as text labels in the UI
 *                    (e.g. status 1 => "Active"). The keyword is matched against the
 *                    LABELS (case-insensitive substring) and resolved, in PHP, to the
 *                    set of integer values whose label matches, then filtered with
 *                    orWhereIn. This keeps the matching DB-agnostic (MySQL + SQLite)
 *                    and free of raw SQL.
 *
 * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
 * @param  string|null  $keyword
 * @param  array<int,string>  $textColumns           e.g. ['sales.sku', 'customers.company_name']
 * @param  array<string,array<int|string,string>>  $codedColumns  e.g. ['status' => [0 => 'Inactive', 1 => 'Active']]
 * @return mixed  the same builder, for chaining
 */
class TableSearch
{
    public static function apply($query, ?string $keyword, array $textColumns, array $codedColumns = [])
    {
        if ($keyword === null || trim($keyword) === '') {
            return $query;
        }

        return $query->where(function ($q) use ($keyword, $textColumns, $codedColumns) {
            foreach ($textColumns as $col) {
                $q->orWhere($col, 'like', '%'.$keyword.'%');
            }

            foreach ($codedColumns as $col => $labels) {
                $matches = self::matchingCodes($labels, $keyword);
                if (! empty($matches)) {
                    $q->orWhereIn($col, $matches);
                }
            }
        });
    }

    /**
     * Resolve which coded values have a label containing the keyword (case-insensitive).
     *
     * @param  array<int|string,string>  $labels
     * @return array<int,int|string>
     */
    public static function matchingCodes(array $labels, string $keyword): array
    {
        $keyword = trim($keyword);

        return array_keys(array_filter(
            $labels,
            fn ($label) => $label !== null && $label !== ''
                && stripos((string) $label, $keyword) !== false
        ));
    }
}
