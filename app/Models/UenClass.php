<?php

namespace App\Models;

use App\Models\StaffView;
use Illuminate\Database\Eloquent\Model;

class UenClass extends Model {
  protected $connection = 'uen_students_db';
  protected $table      = 'uen_classes';

  public function staff() {
    return $this->belongsTo(StaffView::class, 'homeroom_teacher_id', 'staff_no');
  }
}
