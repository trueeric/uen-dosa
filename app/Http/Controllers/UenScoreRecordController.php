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
    // 先獲取當前學期semester
    $currentSemester = UenCurrentSemester::first()->semester;

    // 查詢條件
    $query = UenScoreRecord::query()
      ->withTargetInfo()
      ->when($request->filled('target_no'), function ($query) use ($request) {
        return $query->where('target_no', 'like', "%{$request->target_no}%");
      })
      ->when($request->filled(['start_date', 'end_date']), function ($query) use ($request) {
        return $query->whereBetween('score_date', [
          $request->start_date,
          $request->end_date,
        ]);
      })
      ->when($request->filled('week_no'), function ($query) use ($request) {
        return $query->where('week_no', $request->week_no);
      });

    // 獲取周別選項 - 使用視圖
    $weekOptions = DB::table('v_uen_current_semester_score_record')
      ->select('week_no')
      ->distinct()
      ->whereNotNull('week_no')
      ->orderBy('week_no')
      ->pluck('week_no')
      ->filter()
      ->map(function ($weekNo) {
        return [
          'label' => "第{$weekNo}周",
          'value' => $weekNo,
        ];
      })
      ->values()
      ->all();

    // 分頁資料
    $records = $query->latest('id')
      ->paginate($request->input('per_page', 10))
      ->withQueryString();

    // 偵錯資訊
    $debugSql = [
      'query'          => $query->toSql(),
      'bindings'       => $query->getBindings(),
      'sample_record'  => $records->first(),
      'request_params' => $request->all(),
    ];

    return Inertia::render('UenScoreRecords/Index', [
      'records'         => $records,
      'filters'         => $request->only(['target_no', 'start_date', 'end_date', 'week_no']),
      'weekOptions'     => $weekOptions,
      'currentSemester' => $currentSemester,
      'debugSql'        => $debugSql,
    ]);
  }
  /**
   * Show the form for creating a new resource.
   */
  public function create() {

    // 先獲取當前學期的 first_monday
    $currentSemester = UenCurrentSemester::first()->semester;

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
      'currentSemester' => $currentSemester,
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
