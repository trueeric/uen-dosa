<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffView extends Model {
  protected $connection = 'uen_staff_db';
  protected $table      = 'view_uen_staff_now';

  // 關閉時間戳記
  public $timestamps = false;
}
