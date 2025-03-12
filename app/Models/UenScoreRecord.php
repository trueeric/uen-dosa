<?php
namespace App\Models;

use App\Models\UenCurrentSemesterClass;
use App\Models\UenCurrentSemesterStudent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UenScoreRecord extends Model {

  use SoftDeletes;
  protected $table = 'uen_score_records';

  // 可填充欄位 - 這些欄位允許批量賦值
  protected $fillable = [
    'target_type', // 記錄對象類型: student/class
    'target_id',   // 學生ID或班級ID
    'target_no',   // 輸入的學號或班級編碼
    'recorded_by', // 輸入人員
    'scored_by',   // 評分人員
    'score_no',    // 遠端編號 o_保存碼, t 整潔碼
    'times',       // 計算次數
    'description', // 描述
    'score_date',  // 登記日期
    'semester',    // 學期
    'status',      // 狀態
  ];

  // 類型轉換
  protected $casts = [
    'times'      => 'integer',
    'score_date' => 'date',
    'status'     => 'string',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

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

  // 如果需要獲取原始資料的 scope
  public function scopeWithOriginalData($query) {
    $currentSemester = UenCurrentSemester::value('semester');

    return $query->where('semester', $currentSemester);
  }

}
