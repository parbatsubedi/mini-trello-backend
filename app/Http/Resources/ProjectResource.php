<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'client_id' => $this->client_id,
            'project_type' => $this->project_type,
            'price' => $this->price,
            'client' => new ClientResource($this->whenLoaded('client')),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'members' => UserResource::collection($this->whenLoaded('members')),
            'tasks_count' => $this->whenCounted('tasks'),
            'progress' => $this->when($this->tasks_count > 0, function () {
                return round(($this->tasks()->where('status', 'done')->count() / $this->tasks_count) * 100);
            }, 0),
            'tasks_completed' => $this->when($this->tasks_count > 0, function () {
                return $this->tasks()->where('status', 'done')->count();
            }, 0),
            'total_tasks' => $this->tasks_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
