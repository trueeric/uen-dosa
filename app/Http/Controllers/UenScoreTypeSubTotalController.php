<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UenScoreTypeSubTotalController extends Controller {
  public function index(Request $request) {

                                                // 獲取分頁參數
    $perPage = $request->input('per_page', 10); // 默認每頁10條
    $page    = $request->input('page', 1);      // 默認第1頁

    // 獲取班級列表
    $classes = DB::table('v_uen_current_semester_score_type_sub_total')
      ->select(DB::raw('class_no'))
      ->groupBy('class_no') // 使用 groupBy 而不是 distinct
      ->orderBy('class_no')
      ->get()
      ->map(function ($item) {
        return [
          'label' => $item->class_no,
          'value' => $item->class_no,
        ];
      })
      ->values()
      ->all();

    // 獲取周別列表
    $weeks = DB::table('v_uen_current_semester_score_type_sub_total')
      ->select(DB::raw('week_no'))
      ->groupBy('week_no') // 使用 groupBy 而不是 distinct
      ->orderBy('week_no')
      ->get()
      ->map(function ($item) {
        return [
          'label' => "第{$item->week_no}周",
          'value' => $item->week_no,
        ];
      })
      ->values()
      ->all();

// 篩選條件
    $selectedClass = $request->input('class_no');
    $selectedWeek  = $request->input('week_no');

    // 構建查詢
    $query = DB::table('v_uen_current_semester_score_type_sub_total');

    // 應用篩選條件
    if ($selectedClass) {
      $query->where('class_no', $selectedClass);
    }

    if ($selectedWeek) {
      $query->where('week_no', $selectedWeek);
    }

    // 獲取總記錄數
    $total = $query->count();

    // 獲取分頁數據
    $records = $query->orderBy('class_no')
      ->orderBy('week_no')
      ->skip(($page - 1) * $perPage)
      ->take($perPage)
      ->get();
    return Inertia::render('UenScoreTypeSubTotal/Index', [
      'records' => [
        'data' => $records,
        'meta' => [
          'current_page' => $page,
          'per_page'     => $perPage,
          'total'        => $total,
        ],
      ],
      'classes' => $classes,
      'weeks'   => $weeks,
      'filters' => $request->only(['class_no', 'week_no']),
    ]);
  }
}
