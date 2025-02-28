<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UenScoreTypeSubTotal extends Model {
  protected $table   = 'v_uen_current_semester_score_type_sub_total';
  public $timestamps = false;

  // 視圖沒有主鍵，告訴 Laravel 不要嘗試查找主鍵
  protected $primaryKey = null;
  public $incrementing  = false;

  // 獲取統計數據
  public function scopeGetStats($query) {
    return $query->select([
      'semester',
      'week_no',
      'e_class',
      '10_order_sub_total as order_total',
      '20_tidy_sub_total as tidy_total',
    ]);
  }

  // 可以根據需要添加 scope 方法
  public function scopeFilterByClass($query, $class) {
    return $query->when($class, function ($q) use ($class) {
      return $q->where('e_class', $class);
    });
  }

  public function scopeFilterByWeek($query, $weekNo) {
    return $query->when($weekNo, function ($q) use ($weekNo) {
      return $q->where('week_no', $weekNo);
    });
  }

}
