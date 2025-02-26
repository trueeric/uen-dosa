<?php
namespace App\Models;

use App\Models\UenCurrentSemesterClass;
use App\Models\UenCurrentSemesterStudent;
use Illuminate\Database\Eloquent\Model;

class UenScoreRecord extends Model {
  protected $table = 'uen_score_records';

  // 基本關聯
  public function targetClass() {
    return $this->belongsTo(
      UenCurrentSemesterClass::class,
      'target_id',
      'class_id'
    )->where('target_type', 'class');
  }

  public function targetStudent() {
    return $this->belongsTo(
      UenCurrentSemesterStudent::class,
      'target_id',
      'student_id'
    )->where('target_type', 'student');
  }

  // 使用視圖查詢的 scope 本學期的評分紀錄
  public function scopeWithTargetInfo($query) {
    return $query->from('v_uen_current_semester_score_record')
      ->orderBy('id', 'desc');
  }

  // 如果需要獲取原始資料的 scope
  public function scopeWithOriginalData($query) {
    $currentSemester = UenCurrentSemester::value('semester');

    return $query->where('semester', $currentSemester);
  }

}
