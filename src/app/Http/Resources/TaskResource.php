<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'building_id' => $this->building_id,
            // TODO: FIXME: Why doesn't this work?
            // 'creator' => $this->creator ? new UserResource($this->creator) : null,
            // 'assignee' => $this->assignee ? new UserResource($this->assignee) : null,
            'creator' => $this->creator,
            'assignee' => $this->assignee,
            'status' => $this->status,
            'summary' => $this->summary,
            'comments' => $this->comments,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
