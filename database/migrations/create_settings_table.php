<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            
            if (config('filament-dynamic-settings.multi_tenant', false)) {
                $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');
                $table->unsignedBigInteger($tenantColumn)
                      ->nullable()
                      ->index();
                
                $tenantModel = config('filament-dynamic-settings.tenant_model');
                if ($tenantModel) {
                    $table->foreign($tenantColumn)->references('id')->on((new $tenantModel)->getTable());
                }
            }
            
            $table->string('module')->index();
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); 
            $table->json('options')->nullable();
            $table->string('label')->nullable();
            $table->string('description')->nullable();
            $table->integer('order')->default(0);
            $table->json('validation_rules')->nullable();
            $table->text('custom_validation_message')->nullable(); 
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            if (config('filament-dynamic-settings.multi_tenant', false)) {
                $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');
                $table->unique(
                    [$tenantColumn, 'module', 'key'],
                    'settings_unique_per_tenant'
                );
            } else {
                $table->unique(['module', 'key'], 'settings_unique_single_tenant');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};