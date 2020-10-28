<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateAnimalRequest",
 *     required={},
 *     @OA\Property(
 *         property="type_id",
 *         type="integer",
 *         description="動物的分類ID(需參照types資料表)"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="動物名稱",
 *         example="黑藤"
 *     ),
 *     @OA\Property(
 *         property="birthday",
 *         type="date",
 *         description="生日",
 *         example="2019-01-01"
 *     ),
 *     @OA\Property(
 *         property="area",
 *         type="string",
 *         description="所在區域",
 *         example="台北"
 *     ),
 *     @OA\Property(
 *         property="fix",
 *         type="integer",
 *         description="是否結紮(輸入1或0)",
 *         example=0
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="text",
 *         description="簡易描述",
 *         example="黑狗，胸前有白毛！宛如台灣黑熊"
 *     ),
 *     @OA\Property(
 *         property="personality",
 *         type="text",
 *         description="動物個性",
 *         example="非常親人！很可愛～"
 *     )
 * )
 */
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
            'name' => 'string|max:255',
            'birthday' => 'nullable|date',
            'area' => 'nullable|string|max:255',
            'fix' => 'boolean',
            'description' => 'nullable|string',
            'personality' => 'nullable|string',
        ];
    }
}
