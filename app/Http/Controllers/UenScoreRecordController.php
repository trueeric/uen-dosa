<?php
namespace App\Http\Controllers;

use App\Models\UenScoreRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UenScoreRecordController extends Controller {
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request) {
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
      });

    // 分頁資料
    $records = $query->latest()
      ->paginate(10)
      ->withQueryString();

    // dd($records);
    // dd([
    //   'records'         => $records,
    //   'filters'         => $request->only(['target_no', 'semester', 'start_date', 'end_date']),
    //   'semesterOptions' => [
    //     ['label' => '113-1', 'value' => '113-1'],
    //     ['label' => '112-2', 'value' => '112-2'],
    //     ['label' => '112-1', 'value' => '112-1'],
    //   ],
    // ]
    // );
    // 返回 Inertia 頁面
    return Inertia::render('UenScoreRecords/Index', [
      'records'         => $records,
      'filters'         => $request->only(['target_no', 'semester', 'start_date', 'end_date']),
      'semesterOptions' => [
        ['label' => '113-1', 'value' => '113-1'],
        ['label' => '112-2', 'value' => '112-2'],
        ['label' => '112-1', 'value' => '112-1'],
      ],
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create() {
    // 返回新增頁面所需資料
    return Inertia::render('UenScoreRecords/Create', [
      'semesterOptions' => [
        ['label' => '113-1', 'value' => '113-1'],
        ['label' => '112-2', 'value' => '112-2'],
        ['label' => '112-1', 'value' => '112-1'],
      ],
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
