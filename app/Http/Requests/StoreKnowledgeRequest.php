<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKnowledgeRequest extends FormRequest
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
            'summary' => 'nullable|string',
            'type' => 'nullable|string|in:note,solution,command,snippet',
            'metadata' => 'nullable|array',
            'is_public' => 'nullable|boolean',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'git_context' => 'nullable|array',
            'git_context.repository_url' => 'nullable|string|url',
            'git_context.repository_name' => 'nullable|string',
            'git_context.branch_name' => 'nullable|string',
            'git_context.commit_hash' => 'nullable|string',
        ];
    }
}
