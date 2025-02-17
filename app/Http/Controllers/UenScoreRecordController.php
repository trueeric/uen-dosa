<?php
namespace App\Http\Controllers;

use App\Models\UenCurrentSemesterClass;
use App\Models\UenScoreRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UenScoreRecordController extends Controller {
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request) {
    $query = UenScoreRecord::query()
      ->withTargetInfo()
      ->when($request->target_no, function ($query, $target_no) {
        return $query->where('target_no', 'like', "%{$target_no}%");
      })
      ->when($request->semester, function ($query, $semester) {
        return $query->where('semester', $semester);
      })
      ->when($request->date, function ($query, $date) {
        return $query->whereDate('created_at', $date);
      });

    $records = $query->latest()
      ->paginate(10)
      ->withQueryString();

    return Inertia::render('UenScoreRecords/Index', [
      'records' => $records,
      'filters' => $request->only(['target_no', 'semester', 'date']),
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create() {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request) {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(UenCurrentSemesterClass $uen_score_record) {
    // $record = UenScoreRecord::withTargetInfo()->findOrFail($id);
    // return new UenScoreRecordResource($record);

    // 使用正確參數名稱接收模型實例
    return response()->json([
      'data' => $uen_score_record->load('uenScoreRecords'),
      'meta' => [
        'score_count' => $uen_score_record->uenScoreRecords->count(),
      ],
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(UenScoreRecord $uenScoreRecord) {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, UenScoreRecord $uenScoreRecord) {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(UenScoreRecord $uenScoreRecord) {
    //
  }
}
