<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UenStaffViewAll extends Model {
  protected $connection = 'uen_staff_db';
  protected $table      = 'v_uen_staff_all';

  // 關閉時間戳記
  // public $timestamps = false;
}
