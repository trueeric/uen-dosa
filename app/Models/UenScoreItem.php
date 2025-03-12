<?php
namespace App\Models;

use App\Models\UenScoreRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

// 添加這行導入 Schema facade

class UenScoreItem extends Model {
  protected $table = 'v_uen_current_score_items';

  /* 獲取與此評分項目相關的所有成績記錄     */
  public function scoreRecords() {
    return $this->hasMany(UenScoreRecord::class, 'score_no', 'score_no');
  }

  /**
   * 依據評分項目代碼獲取單一項目
   *
   * @param string $scoreNo
   * @return \App\Models\UenScoreItem|null
   */
  public static function getItemByScoreNo($scoreNo) {
    return self::where('score_no', $scoreNo)->first();
  }

  /**
   * 根據用戶權限級別獲取分數項目
   *
   * @param Builder $query
   * @param int $userId
   * @return Builder
   */
  public function scopeGetScoreItems(Builder $query, $userId = null) {
    if (! $userId) {
      return $query;
    }

    // 獲取用戶的權限級別
    $userMapping = UenUserMapping::where('user_id', $userId)->first();

    if (! $userMapping) {
      return $query->whereRaw('1 = 0'); // 沒有找到用戶映射，返回空結果
    }

    $permissionLevel = (int) $userMapping->permission_level;
    Log::info('UenScoreItem: User permission level: ' . $permissionLevel);

    // 檢查 type_no 欄位是否存在
    $hasTypeNo = \Schema::hasColumn($this->table, 'type_no');

    if (! $hasTypeNo) {
      Log::warning('UenScoreItem: type_no column does not exist in table ' . $this->table);
      // 如果沒有 type_no 欄位，根據權限級別決定是否返回所有項目
      if ($permissionLevel === 10) {
        return $query;
      } else {
        // 可以根據實際業務需求調整這裡的邏輯
        return $query;
      }
    }

    // 根據權限級別篩選分數項目
    if ($permissionLevel <= 10) {
      // 權限級別 10 可以看到所有項目
      Log::info('UenScoreItem: Returning all items for admin user');
      return $query;
    } elseif ($permissionLevel >= 21 && $permissionLevel <= 29) {
      // 權限級別 21-29 只能看到 type_no < 20 的項目
      Log::info('UenScoreItem: Filtering for type_no < 20');
      return $query->where('type_no', '<', 20);
    } elseif ($permissionLevel >= 31 && $permissionLevel <= 39) {
      // 權限級別 31-39 只能看到 type_no > 20 的項目
      Log::info('UenScoreItem: Filtering for type_no > 20');
      return $query->where('type_no', '>', 20);
    } else {
      // 其他權限級別不能看到任何項目
      Log::info('UenScoreItem: No items for permission level: ' . $permissionLevel);
      return $query->whereRaw('1 = 0');
    }

  }

}
