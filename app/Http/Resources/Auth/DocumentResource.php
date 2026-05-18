<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'url' => (Str::startsWith(Storage::url($this->file_path), ['http://', 'https://'])
                ? Storage::url($this->file_path)
                : url(Storage::url($this->file_path))),
            'is_primary' => $this->is_primary,
        ];
    }
}
