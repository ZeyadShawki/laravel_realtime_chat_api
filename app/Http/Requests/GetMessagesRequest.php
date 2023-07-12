<?php

namespace App\Http\Requests;

use App\Models\Chat;
use Illuminate\Foundation\Http\FormRequest;

class GetMessagesRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
          $chat_model=get_class(new Chat());
        return [
            //
            'chat_id'=>"required|exists:{$chat_model},id",
            // 'page'=>'nullable|numeric',
            'page_size'=>'nullable|numeric'
        ];
    }
}
