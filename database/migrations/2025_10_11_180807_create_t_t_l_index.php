<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $store = \Illuminate\Support\Facades\Cache::store('mongodb');
        $store->createTTLIndex();
        $store->lock('')->createTTLIndex();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
