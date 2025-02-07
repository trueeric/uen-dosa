<?php
namespace App\Models;

use App\Models\UenScoreRecord;
use Illuminate\Database\Eloquent\Model;

class UenClassViewAll extends Model {
  protected $connection = 'uen_students_db';

  protected $table = 'v_uen_semester_classes_all';

  // 宣告模型為唯讀
  protected $isReadOnly = true;

  public function staff() {
    return $this->belongsTo(UenStaffViewAll::class, 'homeroom_teacher_id', 'staff_no');
  }

  // UenScoreRecord 多態關聯
  public function uenScoreRecords() {
    return $this->morphMany(UenScoreRecord::class, 'targetable', 'target_type', 'target_id');
  }
}
