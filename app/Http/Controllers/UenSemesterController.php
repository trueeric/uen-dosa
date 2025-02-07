<?php
namespace App\Http\Controllers;

use App\Http\Resources\UenSemesterResource;
use App\Models\UenSemester;

class UenSemesterController extends Controller {
  public function index() {

    $semesters = UenSemester::orderBy('school_year', 'desc')
      ->orderBy('semester_no', 'desc')
      ->get();

    return UenSemesterResource::collection($semesters);
  }
}
