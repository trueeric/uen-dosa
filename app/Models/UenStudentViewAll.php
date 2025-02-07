<?php
namespace App\Models;

use App\Models\UenScoreRecord;
use Illuminate\Database\Eloquent\Model;

class UenStudentViewAll extends Model {
  protected $connection = 'uen_students_db';
  protected $table      = 'uen_student_semester_classes';
  // protected $fillable   = ['student_no',   ];

  // 宣告模型為唯讀
  protected $isReadOnly = true;

// 添加與班級的關聯
  public function class () {
    return $this->belongsTo(UenClassViewAll::class, 'class_no', 'class_no');
  }

  // UenScoreRecord 多態關聯
  public function uenScoreRecords() {
    return $this->morphMany(UenScoreRecord::class, 'targetable', 'target_type', 'target_id');
  }
}
