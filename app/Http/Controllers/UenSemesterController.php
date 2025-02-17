<?php
namespace App\Http\Controllers;

use App\Http\Resources\UenSemesterResource;
use App\Models\UenSemesterAll;

class UenSemesterController extends Controller {
  public function index() {

    $semesters = UenSemesterAll::orderBy('school_year', 'desc')
      ->orderBy('semester_no', 'desc')
      ->get();

    return UenSemesterResource::collection($semesters);
  }
}
