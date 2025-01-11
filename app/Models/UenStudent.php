<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UenStudent extends Model {
  protected $connection = 'uen_students_db';
  protected $table      = 'uen_students';
  protected $fillable   = ['student_no', 'class_seat',

  ];

}
