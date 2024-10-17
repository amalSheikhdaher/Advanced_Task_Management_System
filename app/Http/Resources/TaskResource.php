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
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,  // Bug, Feature, Improvement
            'status' => $this->status,  // Open, In Progress, Completed, Blocked
            'priority' => $this->priority,  // Low, Medium, High
            'due_date' => $this->due_date ? $this->due_date->toDateString() : null,
            'assigned_to' => $this->assignedUser ? [
                'id' => $this->assignedUser->id,
                'name' => $this->assignedUser->name,
                'email' => $this->assignedUser->email,
            ] : null,
            // 'comments' => CommentResource::collection($this->whenLoaded('comments')),
            // 'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            //'dependencies' => DependencyResource::collection($this->whenLoaded('dependencies')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
