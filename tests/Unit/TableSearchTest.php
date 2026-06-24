<?php

namespace Tests\Unit;

use App\Support\TableSearch;
use Illuminate\Database\Capsule\Manager as Capsule;
use PHPUnit\Framework\TestCase;

class TableSearchTest extends TestCase
{
    private Capsule $capsule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->capsule = new Capsule;
        $this->capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        $schema = $this->capsule->schema();
        $schema->create('items', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('sku')->nullable();
            $table->integer('status')->default(0);
            $table->integer('type')->nullable();
        });

        $this->insert(['name' => 'Alpha Widget', 'sku' => 'AW-1', 'status' => 1, 'type' => 1]);   // Active / Car
        $this->insert(['name' => 'Beta Gadget',  'sku' => 'BG-2', 'status' => 0, 'type' => 2]);   // Inactive / Lorry
        $this->insert(['name' => 'Gamma Sold',   'sku' => 'GS-3', 'status' => 2, 'type' => 1]);   // Sold / Car
    }

    private function insert(array $row): void
    {
        $this->capsule->table('items')->insert($row);
    }

    private function query()
    {
        return $this->capsule->table('items');
    }

    private const STATUS_LABELS = [0 => 'Inactive', 1 => 'Active', 2 => 'Sold'];
    private const TYPE_LABELS = [1 => 'Car', 2 => 'Lorry'];

    public function test_matches_plain_text_column(): void
    {
        $ids = TableSearch::apply($this->query(), 'alpha', ['name', 'sku'])
            ->pluck('id')->all();

        $this->assertSame([1], $ids);
    }

    public function test_text_match_is_case_insensitive_substring(): void
    {
        $ids = TableSearch::apply($this->query(), 'gadget', ['name', 'sku'])
            ->pluck('id')->all();

        $this->assertSame([2], $ids);
    }

    public function test_matches_coded_status_label_active(): void
    {
        $ids = TableSearch::apply($this->query(), 'active', ['name'], ['status' => self::STATUS_LABELS])
            ->pluck('id')->all();

        // "active" is a substring of both "Active" (1) and "Inactive" (0).
        $this->assertEqualsCanonicalizing([1, 2], $ids);
    }

    public function test_matches_coded_status_label_sold(): void
    {
        $ids = TableSearch::apply($this->query(), 'sold', ['name'], ['status' => self::STATUS_LABELS])
            ->pluck('id')->all();

        // Row 3 status=2 (Sold) AND row 3 name contains "Sold" — single row either way.
        $this->assertSame([3], $ids);
    }

    public function test_coded_match_combines_with_text_via_or(): void
    {
        // keyword "car" matches type label Car (rows 1 & 3); no name/sku contains "car".
        $ids = TableSearch::apply($this->query(), 'car', ['name', 'sku'], ['type' => self::TYPE_LABELS])
            ->pluck('id')->all();

        $this->assertEqualsCanonicalizing([1, 3], $ids);
    }

    public function test_multiple_coded_columns(): void
    {
        // "lorry" -> type 2 (row 2). "inactive" not searched here.
        $ids = TableSearch::apply(
            $this->query(),
            'lorry',
            ['name'],
            ['status' => self::STATUS_LABELS, 'type' => self::TYPE_LABELS]
        )->pluck('id')->all();

        $this->assertSame([2], $ids);
    }

    public function test_empty_keyword_is_a_noop(): void
    {
        $this->assertCount(3, TableSearch::apply($this->query(), '', ['name'])->get());
        $this->assertCount(3, TableSearch::apply($this->query(), null, ['name'])->get());
        $this->assertCount(3, TableSearch::apply($this->query(), '   ', ['name'])->get());
    }

    public function test_no_match_returns_nothing(): void
    {
        $ids = TableSearch::apply($this->query(), 'zzzz', ['name', 'sku'], ['status' => self::STATUS_LABELS])
            ->pluck('id')->all();

        $this->assertSame([], $ids);
    }

    public function test_matching_codes_resolves_labels_case_insensitively(): void
    {
        $this->assertSame([2], array_values(TableSearch::matchingCodes(self::STATUS_LABELS, 'SOLD')));
        $this->assertEqualsCanonicalizing([0, 1], TableSearch::matchingCodes(self::STATUS_LABELS, 'active'));
        $this->assertSame([], TableSearch::matchingCodes(self::STATUS_LABELS, 'nope'));
    }
}
