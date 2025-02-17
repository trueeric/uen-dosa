<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UenScoreRecordResource extends JsonResource {
  public function toArray(Request $request): array {
    return [
      'id'          => $this->id,
      'target_type' => $this->target_type,
      'target_id'   => $this->target_id,
      'target_no'   => $this->target_no,
      'recorded_by' => $this->recorded_by,
      'score_no'    => $this->score_no,
      'times'       => $this->times,
      'semester'    => $this->semester,
      'created_at'  => $this->created_at->format('md  Hi') ?? null,
    ];
  }
}
