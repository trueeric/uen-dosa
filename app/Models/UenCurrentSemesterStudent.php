<?php
namespace App\Models;

use App\Models\UenCurrentSemesterClass;
use App\Models\UenScoreRecord;
use Illuminate\Database\Eloquent\Model;

class UenCurrentSemesterStudent extends Model {
  protected $connection = 'uen_students_db';
  protected $table      = 'v_uen_current_semester_students';
  // protected $fillable   = ['student_no',   ];

// 添加與班級的關聯
  public function class () {
    return $this->belongsTo(UenCurrentSemesterClass::class, 'class_no', 'class_no');
  }
// UenScoreRecord 多態關聯
  public function uenScoreRecords() {
    return $this->morphMany(UenScoreRecord::class, 'targetable', 'target_type', 'target_id');
  }
}
