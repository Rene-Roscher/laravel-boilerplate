<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelPasskeys\Support\Config;

return new class extends Migration
{
    public function up()
    {
        $authenticatableClass = Config::getAuthenticatableModel();

        $authenticatableTableName = (new $authenticatableClass)->getTable();

        Schema::create('passkeys', function (Blueprint $table) use ($authenticatableTableName,$authenticatableClass) {
            $table->unsignedBigInteger('id')->primary();

            $table
                ->foreignIdFor($authenticatableClass, 'authenticatable_id')
                ->constrained(table: $authenticatableTableName, indexName: 'passkeys_authenticatable_fk')
                ->cascadeOnDelete();

            $table->text('name');
            $table->text('credential_id');
            $table->json('data');

            // Additional metadata fields
            $table->string('user_agent', 500)->nullable();
            $table->string('device_name')->nullable();
            $table->string('device_type', 50)->nullable(); // desktop, mobile, tablet
            $table->string('browser_name', 100)->nullable();
            $table->string('operating_system', 100)->nullable();
            $table->integer('counter')->default(0);
            $table->boolean('backup_eligible')->default(false);
            $table->boolean('backup_state')->default(false);
            $table->json('transports')->nullable(); // USB, NFC, Bluetooth, etc.

            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }
};
