<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'points' => $this->points,
            'start_date' => $this->start_date,
            'is_recurring' => $this->is_recurring,
            'due_date' => $this->due_date->diffForHumans(),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'parent' => new TaskResource($this->whenLoaded('parent')),
            'subtasks' => TaskResource::collection($this->whenLoaded('subtasks')),
            'assignedUsers' => UserResource::collection($this->whenLoaded('assignedUsers')),
            'collaborators' => UserResource::collection($this->whenLoaded('collaborators')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            'labels' => LabelResource::collection($this->whenLoaded('labels', function () {
                return $this->labels->filter(fn ($label) => in_array($label->type, ['task', 'both']));
            })),
            'comments_count' => $this->whenCounted('comments'),
            'attachments_count' => $this->whenCounted('attachments'),
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans(),
        ];
    }
}
