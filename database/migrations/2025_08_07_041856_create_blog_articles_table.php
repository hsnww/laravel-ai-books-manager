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
        Schema::create('blog_articles', function (Blueprint $table) {
            $table->id();
            $table->integer('book_id'); // Changed from unsignedBigInteger to integer
            $table->string('original_file');
            $table->string('title');
            $table->longText('article_content');
            $table->string('target_language');
            $table->string('article_type')->default('blog'); // blog, review, etc.
            $table->integer('word_count')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('processing_date')->useCurrent();
            $table->timestamps();
            
            // Foreign key constraint - temporarily removed
            // $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            
            // Indexes
            $table->index(['book_id', 'target_language']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_articles');
    }
};
