<?php
namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class UenSemesterClasses extends Model {
  protected $connection = 'uen_students_db';
  protected $table      = 'v_uen_current_semester_classes';

  // 重要：新增這個方法來處理 Route Model Binding
  public function getRouteKeyName() {
    return 'class_id';
  }

  // 指定主鍵
  protected $primaryKey = 'class_id';

  // 設定為唯讀
  protected $isReadOnly = true;

  // 主鍵非自增
  // public $incrementing = false;

  // 主鍵型別為字串
  protected $keyType = 'string';

  // 關閉時間戳記
  public $timestamps = false;

  // 定義可訪問的欄位
  // protected $fillable = [
  //   'class_id',
  //   'class_no',
  //   'class_name',
  //   'semester',
  //   'homeroom_teacher_id',
  //   'homeroom_teacher',
  //   'grade_sort',
  // ];

  // 防止資料被修改（因為是唯讀視圖）
  public static function boot() {
    parent::boot();

    static::saving(function () {
      throw new Exception('This model is read-only');
    });
  }

}
