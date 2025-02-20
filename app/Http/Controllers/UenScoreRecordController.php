<?php
namespace App\Http\Controllers;

use App\Models\UenCurrentSemester;
use App\Models\UenScoreRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UenScoreRecordController extends Controller {
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request) {

    // 先獲取當前學期的 first_monday
    $currentSemester = UenCurrentSemester::first();

    // 查詢條件
    $query = UenScoreRecord::query()
      ->withTargetInfo()
      ->when($request->target_no, function ($query, $target_no) {
        return $query->where('target_no', 'like', "%{$target_no}%");
      })
      ->when($request->semester, function ($query, $semester) {
        return $query->where('semester', $semester);
      })
      ->when($request->start_date && $request->end_date, function ($query) use ($request) {
        return $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
      })
      ->when($request->week_no, function ($query, $week_no) {
        return $query->having('week_no', '=', $week_no);
      });

    // 獲取周別選項
    $weekOptions = UenScoreRecord::query()
      ->select([
        DB::raw('DISTINCT FLOOR(DATEDIFF(score_date, (SELECT first_monday FROM std_basic.v_uen_current_semester WHERE semester = uen_score_records.semester LIMIT 1)) / 7) + 1 as week_no'),
      ])
      ->where('semester', $currentSemester->semester)
      ->whereNotNull('score_date')
      ->orderBy('week_no')
      ->pluck('week_no')
      ->filter() // 過濾掉 null 值
      ->map(function ($weekNo) {
        return [
          'label' => "第{$weekNo}周",
          'value' => $weekNo,
        ];
      })
      ->values()
      ->all();

    // 分頁資料
    $records = $query->latest()
      ->paginate(10)
      ->withQueryString();

    // 返回 Inertia 頁面
    return Inertia::render('UenScoreRecords/Index', [
      'records'         => $records,
      'filters'         => $request->only(['target_no', 'semester', 'start_date', 'end_date']),
      'weekOptions'     => $weekOptions,
      'currentSemester' => $currentSemester->semester,
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create() {

    // 先獲取當前學期的 first_monday
    $currentSemester = UenCurrentSemester::first();

    // 獲取周別選項
    $weekOptions = UenScoreRecord::query()
      ->select([
        DB::raw('DISTINCT FLOOR(DATEDIFF(score_date, (SELECT first_monday FROM std_basic.v_uen_current_semester WHERE semester = uen_score_records.semester LIMIT 1)) / 7) + 1 as week_no'),
      ])
      ->where('semester', $currentSemester->semester)
      ->whereNotNull('score_date')
      ->orderBy('week_no')
      ->pluck('week_no')
      ->filter() // 過濾掉 null 值
      ->map(function ($weekNo) {
        return [
          'label' => "第{$weekNo}周",
          'value' => $weekNo,
        ];
      })
      ->values()
      ->all();

    // 返回新增頁面所需資料
    return Inertia::render('UenScoreRecords/Create', [
      'weekOptions'     => $weekOptions,
      'currentSemester' => $currentSemester->semester,
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request) {
    // 表單驗證
    $validated = $request->validate([
      'target_no' => 'required|string|max:255',
      'semester'  => 'required|string|max:255',
      'name'      => 'required|string|max:255',
      'points'    => 'required|integer',
    ]);

    // 保存資料
    UenScoreRecord::create($validated);

    // 重定向到列表頁
    return redirect()->route('uen-score-records.index')->with('success', '記錄新增成功');
  }

  /**
   * Display the specified resource.
   */
  public function show(UenScoreRecord $uen_score_record) {
    // 返回詳細資料頁面
    return Inertia::render('UenScoreRecords/Show', [
      'record' => $uen_score_record->load('targetInfo'),
    ]);
  }
}
