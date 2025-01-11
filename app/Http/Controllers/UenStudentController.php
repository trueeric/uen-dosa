<?php

namespace App\Http\Controllers;

use App\Models\UenStudent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UenStudentController extends Controller {
  /**
   * Display a listing of the resource.
   */
  public function index() {
    $students = UenStudent::all();
    return Inertia::render('UenStudent/Index', [
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
