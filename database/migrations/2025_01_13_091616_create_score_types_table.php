<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('uen_score_types', function (Blueprint $table) {
      // 主鍵設定
      $table->smallInteger('id')->unsigned()->primary();

      // 分類欄位
      $table->string('cate_no', 10)->comment('分類代號 t01秩序類,t02整潔類');
      $table->tinyInteger('type_no')->unsigned()->comment('類別編號 1~5,21~22');
      $table->string('name', 30)->comment('類別名稱');
      $table->string('memo', 190)->nullable()->comment('備註');

      // 排序與狀態
      $table->tinyInteger('sort')->unsigned()->default(0)->comment('排序');
      $table->boolean('is_active')->default(true)->comment('是否啟用');

      // 時間戳記
      $table->timestamps();

      // 索引
      $table->index('cate_no');
      $table->index('type_no');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('uen_score_types');
  }
};
