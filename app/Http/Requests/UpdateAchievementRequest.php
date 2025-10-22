<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAchievementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'date_of_achievement' => 'required|date',
            'location_id' => 'nullable|exists:locations,id',
            'document_attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'status' => 'required|in:draft,published,archived',
            'category_id' => 'required|exists:categories,id',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
        ];
    }
}