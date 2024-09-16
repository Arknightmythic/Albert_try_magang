<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('holiday_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->string("slug");
            $table->string('destinations_name');
            $table->string('destinations_location');
            $table->text('destinations_itenary')->nullable();
            $table->text('about')->nullable();
            $table->string('contact');
            $table->longText('images')->nullable();
            $table->unsignedSmallInteger('price_per_trip')->default(0);
            $table->unsignedSmallInteger('hotel')->default(0);
            $table->unsignedSmallInteger('travel')->default(0);
            $table->unsignedSmallInteger('plane')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_packages');
    }
};
