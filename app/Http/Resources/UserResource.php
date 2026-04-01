<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $names = explode(' ', trim($this->name));

        // $firstInitial = isset($names[0]) ? strtoupper($names[0][0]) : '';
        // $secondInitial = isset($names[1]) ? strtoupper($names[1][0]) : '';
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'projects_count' => $this->whenCounted('projects'),
            'created_tasks_count' => $this->whenCounted('createdTasks'),
            'assigned_tasks_count' => $this->whenCounted('assignedTasks'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
