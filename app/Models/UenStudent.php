<?php

namespace App\Models;

use App\Models\UenClass;
use Illuminate\Database\Eloquent\Model;

class UenStudent extends Model {
  protected $connection = 'uen_students_db';
  protected $table      = 'uen_students';
  protected $fillable   = ['student_no', 'class_seat',

  ];
// 添加與班級的關聯
  public function class () {
    return $this->belongsTo(UenClass::class, 'class_no', 'class_no');
  }
}
