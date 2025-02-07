<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UenSemester extends Model {
  protected $connection = 'uen_students_db';
  protected $table      = 'uen_semesters';
  // 宣告模型為唯讀
  protected $isReadOnly = true;

}
