<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnimalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type_id' => 'nullable|exists:types,id',
            'name' => 'string|max:255',             // 文字類型最多255字元
            // 允許null並且為日期格式
            'birthday' => 'nullable|date',
            'area' => 'nullable|string|max:255',    // 允許null或文字最多255字元
            'fix' => 'boolean',                     // 若填寫必須是布林值
            'description' => 'nullable|string',     // 允許null或文字
            'personality' => 'nullable|string',     // 允許null或文字
        ];
    }
}
