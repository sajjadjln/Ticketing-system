<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketWithAttachmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:technical,billing,general,other',
            'priority' => 'required|in:low,medium,high',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.*.max' => 'Each attachment must not exceed 10MB',
            'attachments.*.file' => 'Each attachment must be a valid file',
        ];
    }
}