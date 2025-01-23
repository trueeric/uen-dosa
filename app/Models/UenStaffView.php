<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UenStaffView extends Model {
  protected $connection = 'uen_staff_db';
  protected $table      = 'v_uen_staff_now';

  // 關閉時間戳記
  // public $timestamps = false;
}
