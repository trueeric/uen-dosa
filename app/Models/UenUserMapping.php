<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UenUserMapping extends Model {
  protected $table   = 'v_uen_current_user_mappings';
  public $timestamps = false; // 假設視圖表沒有時間戳記欄位

  /**
   * 獲取用戶的權限等級
   *
   * @return int
   */
  public function getPermissionLevelAttribute($value) {
    return $value ? (int) $value : 0;
  }

  /**
   * 根據用戶ID查詢權限等級
   *
   * @param int $userId
   * @return int|null
   */
  public static function getPermissionLevelByUserId($userId) {
    $mapping = self::where('user_id', $userId)->first();
    return $mapping ? (int) $mapping->permission_level : null;
  }
  public static function getCurrentUserInfo($userId) {
    $userId  = 1;
    $mapping = self::where('user_id', $userId)->first();

    if (! $mapping) {
      return null;
    }

    $user_info = (object) [
      'user_id'          => $mapping->user_id,
      'user_name'        => $mapping->user_name,
      'identity_no'      => $mapping->identity_no,
      'permission_level' => $mapping?->permission_level,
      'identity_type'    => $mapping->identity_type,
    ];

    return $user_info;
  }

}
