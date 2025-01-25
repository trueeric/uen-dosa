<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('uen_score_items', function (Blueprint $table) {
      $table->id()->comment('主鍵');
      $table->string('score_no', 20)->unique()->comment('評分項目代碼，結合秩序及整潔');
      $table->string('ori_score_code', 20)->comment('原始評分項目代碼');
      $table->tinyInteger('type_no')->unsigned()->nullable()->comment('類別代碼');
      $table->string('name', 100)->comment('評分項目名稱');
      $table->string('memo', 100)->nullable()->comment('備註');
      $table->decimal('points', 5, 2)->default(0)->comment('分數');
      $table->boolean('is_active')->default(true)->comment('是否啟用');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('type_no')
        ->references('type_no')
        ->on('uen_score_types')
        ->onDelete('restrict');

      $table->index('type_no');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('uen_score_items');
  }
};
