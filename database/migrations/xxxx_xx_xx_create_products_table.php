Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
    // ... other columns
    $table->timestamps();
}); 