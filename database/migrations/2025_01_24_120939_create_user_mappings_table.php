<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('uen_user_mappings', function (Blueprint $table) {
      $table->id();
      $table->unsignedMediumInteger('user_id');
      $table->tinyInteger('identity_type')->unsigned()->comment('身份類型 1:學生 2:教職員');
      $table->string('identity_no', 20)->comment('學號/員工編號');
      $table->boolean('is_active')->default(true)->comment('是否啟用');
      $table->timestamps();

      // 索引和約束
      $table->foreign('user_id')->references('id')->on('users');
      $table->unique(['user_id', 'identity_type']);
      $table->index('identity_no');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('uen_user_mappings');
  }
};
