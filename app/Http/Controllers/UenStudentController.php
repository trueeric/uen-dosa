<?php
namespace App\Http\Controllers;

use App\Models\UenStudentView;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UenStudentController extends Controller {
  /**
   * Display a listing of the resource.
   */
  public function index() {

    // 測試 uen_classes 資料表結構
    // dd(DB::connection('uen_students_db')->getSchemaBuilder()->getColumnListing('uen_classes'));

    // 先測試單一筆資料的關聯
    // $student = UenStudentView::first();
    // $student = UenStudentView::with(['class.staff'])->first();
    // dd($student->toArray());

    // $students = UenStudent::take(50)->get();
    $students = UenStudentView::with(['class.staff'])
      ->take(50)
      ->get()
      ->map(function ($student) {
        return [
          'id'                    => $student->student_id,
          'student_no'            => $student->student_no,
          'name'                  => $student->student_name,
          'seat'                  => $student->seat_no,
          'class_name'            => $student->class->class_name ?? null,
          'homeroom_teacher_name' => $student->class->staff->name ?? null,
        ];
      });

    // dd($students);
    return Inertia::render('Students/Index', [
      'students' => $students,
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
  public function show(UenStudent $uenStudent) {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(UenStudent $uenStudent) {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, UenStudent $uenStudent) {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(UenStudent $uenStudent) {
    //
  }
}
