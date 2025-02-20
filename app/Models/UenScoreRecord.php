<?php
namespace App\Models;

use App\Models\UenCurrentSemester;
use App\Models\UenCurrentSemesterClass;
use App\Models\UenCurrentSemesterStudent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UenScoreRecord extends Model {
  protected $table = 'uen_score_records';

  // 保持原有的多態關聯
  public function targetable() {
    return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
  }

  // 保持原有的 accessor
  public function getTargetNameAttribute() {
    return $this->targetable?->name;
  }

  // 計算周別的 scope
  public function scopeWithWeekNo($query) {
    return $query->addSelect([
      '*',
      DB::raw('FLOOR(DATEDIFF(score_date, (SELECT first_monday FROM std_basic.v_uen_current_semester WHERE semester = uen_score_records.semester LIMIT 1)) / 7) + 1 as week_no'),
    ]);
  }

  // 優化 scope
  public function scopeWithTargetInfo($query) {
    // 從視圖取得當前學期值
    $currentSemester = UenCurrentSemester::value('semester');

    return $query
      ->where('uen_score_records.semester', '=', $currentSemester)
      ->join('uen_score_items as usi', 'uen_score_records.score_no', '=', 'usi.score_no')
      // 學生資料連接
      ->leftJoin('std_basic.uen_student_semester_classes as ussc', function ($join) use ($currentSemester) {
        $join->on('uen_score_records.target_id', '=', 'ussc.student_id')
          ->where('uen_score_records.target_type', '=', 'student')
          ->where('ussc.semester', '=', DB::raw('uen_score_records.semester'));
      })
      // 使用視圖替代原表
      ->leftJoin('std_basic.v_uen_current_semester_classes as ucsc', function ($join) use ($currentSemester) {
        $join->on('uen_score_records.target_id', '=', 'ucsc.class_id')
          ->where('uen_score_records.target_type', '=', 'class')
          ->where('ucsc.semester', '=', DB::raw('uen_score_records.semester'));
      })
      ->select([
        'uen_score_records.id',
        'uen_score_records.semester',
        'uen_score_records.target_type',
        'uen_score_records.target_id',
        // 使用新的視圖別名 ucsc
        DB::raw('COALESCE(ussc.student_id, ucsc.class_no) as target_name'),
        'uen_score_records.score_no',
        'usi.name',
        'usi.points',
        'uen_score_records.times',
        DB::raw('round((CAST(usi.points AS DECIMAL(10, 2)) * CAST(uen_score_records.times AS DECIMAL(10, 2))),1) as subtotal'),
        'uen_score_records.score_date',
        'uen_score_records.created_at',
        // 添加周別計算
        DB::raw('FLOOR(DATEDIFF(uen_score_records.score_date, (SELECT first_monday FROM std_basic.v_uen_current_semester WHERE semester = uen_score_records.semester LIMIT 1)) / 7) + 1 as week_no'),

      ])
      ->orderBy('uen_score_records.id', 'desc');
  }

  // 建議新增以下關聯方法，方便後續使用
  public function scoreItem() {
    return $this->belongsTo(UenScoreItem::class, 'score_no', 'score_no');
  }

  // 如果需要直接訪問班級資料，可以加入
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
}
