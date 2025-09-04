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
        Schema::create('shlokas', function (Blueprint $table) {
            $table->id();
            $table->string('shloka_id')->unique(); // e.g., CS_SUT_25.40
            $table->text('sanskrit_shloka');
            $table->text('unicode')->nullable();
            $table->text('transliteration')->nullable();
            $table->json('translations'); // {hindi: "", english: ""}
            $table->string('source_text_name');
            $table->string('source_section');
            $table->integer('source_chapter');
            $table->integer('source_verse');
            $table->json('keywords')->nullable();
            $table->string('category')->nullable();
            $table->json('commentaries')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shlokas');
    }
};