<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UenScoreRecordView extends Model {
  protected $table = 'v_uen_current_semester_score_record';

  // 類型轉換
  protected $casts = [
    'times'      => 'integer',
    'score_date' => 'date',
    'status'     => 'string',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

}
