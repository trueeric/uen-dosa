<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UenScoreRecord extends Model {
  protected $table = 'uen_score_records';

  // 定義多態關聯
  public function targetable() {
    return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
  }

  // 方便取得名稱的 accessor
  public function getTargetNameAttribute() {
    return $this->targetable?->name;
  }

  // 定義查詢範圍
  public function scopeWithTargetInfo($query) {
    return $query
      ->join('uen_score_items as usi', 'uen_score_records.score_no', '=', 'usi.score_no')
      ->leftJoin('std_basic.uen_student_semester_classes as ussc', function ($join) {
        $join->on('uen_score_records.target_id', '=', 'ussc.student_id')
          ->where('uen_score_records.target_type', '=', 'student')
          ->on('uen_score_records.semester', '=', 'ussc.semester');
      })
      ->leftJoin('std_basic.uen_semester_classes as usc', function ($join) {
        $join->on('uen_score_records.target_id', '=', 'usc.class_id')
          ->where('uen_score_records.target_type', '=', 'class')
          ->on('uen_score_records.semester', '=', 'usc.semester');
      })
      ->select([
        'uen_score_records.target_type',
        'uen_score_records.target_id',
        DB::raw('COALESCE(ussc.student_id, usc.class_no) as target_name'),
        'uen_score_records.score_no',
        'usi.name',
        'usi.points',
        'uen_score_records.times',
      ]);
  }
}
