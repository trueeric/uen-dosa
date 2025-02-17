<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UenSemesterAllResource extends JsonResource {
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array {
    return [
      'semester'     => $this->semester,
      'school_year'  => $this->school_year,
      'semester_no'  => $this->semester_no,
      'first_monday' => $this->first_monday,
      'status'       => $this->status,

    ];
  }

  // 自訂 Resource Collection 的包裝格式
  public static function collection($resource) {
    return parent::collection($resource)->additional([
      'meta' => [
        'total_count'  => $resource->count(),
        'current_time' => now()->toDateTimeString(),
      ],
    ]);
  }
}
