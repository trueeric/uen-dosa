<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UenClassView extends Model {
  protected $connection = 'uen_students_db';
  protected $table      = 'v_uen_current_semester_classes';

  public function staff() {
    return $this->belongsTo(UenStaffView::class, 'homeroom_teacher_id', 'staff_no');
  }
}
