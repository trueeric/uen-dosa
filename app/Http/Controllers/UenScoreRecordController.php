<?php
namespace App\Http\Controllers;

use App\Models\UenCurrentSemester;
use App\Models\UenCurrentSemesterClass;
use App\Models\UenCurrentSemesterStudent;
use App\Models\UenScoreItem;
use App\Models\UenScoreRecord;
use App\Models\UenScoreRecordView;
use App\Models\UenUserMapping;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UenScoreRecordController extends Controller {

  /**
   * 獲取當前用戶今日的記錄
   * 這個私有方法應該放在控制器類中，供其他方法調用
   */
  private function getUserTodayRecords($userId) {
    return UenScoreRecordView::where('recorded_by', $userId)
      ->whereDate('score_date', now()->toDateString())
      ->latest('id')
      ->get();
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request) {
    // 先獲取當前學期semester
    $currentSemester = UenCurrentSemester::first()->semester;

    // 查詢條件
    $query = UenScoreRecordView::query()
      ->when($request->filled('class_no'), function ($query) use ($request) {
        return $query->where('class_no', 'like', "%{$request->class_no}%");
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
    $weekOptions = UenScoreRecordView::select('week_no')
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
      'records'         => [
        'data' => $records->items(),
        'meta' => [
          'current_page' => $records->currentPage(),
          'last_page'    => $records->lastPage(),
          'per_page'     => $records->perPage(),
          'total'        => $records->total(),
          'from'         => $records->firstItem(),
          'to'           => $records->lastItem(),
        ],
      ],
      'filters'         => $request->only(['class_no', 'start_date', 'end_date', 'week_no']),
      'weekOptions'     => $weekOptions,
      'currentSemester' => $currentSemester,
      'debugSql'        => $debugSql,
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create() {

    $userId = auth()->id();

    $userInfo = UenUserMapping::getCurrentUserInfo($userId);
    // 獲取所有分數項目（不進行權限過濾）

    // 使用 scopeGetScoreItems 查詢符合權限的項目
    $scoreItems = UenScoreItem::getScoreItems($userId)->get();
    // dd($scoreItems);

    $semesterInfo = UenCurrentSemester::first();

    // 獲取當前用戶今日的記錄
    $userTodayRecords = $this->getUserTodayRecords($userId);

    // 返回更多調試信息
    return Inertia::render('UenScoreRecords/Create', [
      'semesterInfo'     => $semesterInfo,
      'scoreItems'       => $scoreItems,
      'userInfo'         => $userInfo,
      'userTodayRecords' => $userTodayRecords,

    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request) {

    $data = $request->validate([
      'target_no'   => 'required|string',
      'target_type' => 'required|string',
      'score_no'    => 'required|string',
      'semester'    => 'required|string',
      'score_date'  => 'required|date',
      'scored_by'   => 'nullable|string',
      'description' => 'nullable|string',

    ]);
    // dd($data);
    // 將 target_no 轉換為大寫
    $data['target_no'] = strtoupper($data['target_no']);

    // 檢查今日是否已有相同記錄
    $existingRecord = UenScoreRecord::where('target_no', $data['target_no'])
      ->where('score_no', $data['score_no'])
      ->whereDate('score_date', now())
      ->first();

    if ($existingRecord) {
      return redirect()->back()->with('error', '今日已有相同班級/學號和項目代碼的記錄，請勿重複輸入');
    }

// 根據 target_type 和 target_no 找到對應的 target_id
    $targetId = null;

    if ($request->target_type === 'class') {
      $class = UenCurrentSemesterClass::where('class_no', $request->target_no)->first();
      if ($class) {
        $targetId = $class->class_id;
      } else {
        return response()->json(['message' => '找不到指定的班級'], 404);
      }
    } else if ($request->target_type === 'student') {
      $student = UenCurrentSemesterStudent::where('student_no', $request->target_no)->first();
      if ($student) {
        $targetId = $student->student_id;
      } else {
        return response()->json(['message' => '找不到指定的學生'], 404);
      }
    }

// 創建記錄
    // 確保必要欄位有值
    $data['target_id']   = $targetId;
    $data['recorded_by'] = $data['recorded_by'] ?? auth()->id();
    $data['status']      = $data['status'] ?? 'pending';

    $scoreRecord = UenScoreRecord::create($data);

    // 獲取當前用戶今日的記錄
    $userTodayRecords = $this->getUserTodayRecords(auth()->id());

    return redirect()->back()->with([
      'success'          => '成績記錄已成功創建',
      'userTodayRecords' => $userTodayRecords,
    ]);
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

  public function destroy(UenScoreRecord $uen_score_record) {
    // 檢查權限（可選）
    // $this->authorize('delete', $uen_score_record);

    // 軟刪除記錄
    $uen_score_record->delete();

    // 獲取當前用戶今日的記錄（已排除軟刪除的記錄）
    $userTodayRecords = $this->getUserTodayRecords(auth()->id());

    // 返回成功訊息和更新後的記錄
    return redirect()->back()->with([
      'success'          => '記錄已成功刪除',
      'userTodayRecords' => $userTodayRecords,
    ]);
  }
}
