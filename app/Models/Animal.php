<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Animal",
 *     required={"id", "name", "fix", "user_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="type_id",
 *         type="integer",
 *         description="動物的分類ID(需參照types資料表)",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="type_name",
 *         type="string",
 *         description="關聯type名稱",
 *         example="狗"
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
 *         property="age",
 *         type="string",
 *         description="年齡(系統自動計算)",
 *         example="1歲1月"
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
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="date",
 *         description="建立時間",
 *         example="2020-10-01 00:00:00"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="date",
 *         description="更新時間",
 *         example="2020-10-01 00:00:00"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="所屬會員ID",
 *         example=1
 *     )
 * )
 */
class Animal extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * 可以被批量寫入的屬性。
     *
     * @var array
     */
    protected $fillable = [
        'type_id',
        'name',
        'birthday',
        'area',
        'fix',
        'description',
        'personality',
    ];

    /**
     * 取得動物的分類
     */
    public function type()
    {
        // belongsTo(類別名稱, 參照欄位, 主鍵)
        return $this->belongsTo('App\Models\Type');
    }

    public function getAgeAttribute()
    {
        $diff = Carbon::now()->diff($this->birthday);
        return "{$diff->y}歲{$diff->m}月";
    }

    /**
     * 取得動物的刊登會員，一對多的反向關聯
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * 多對多關聯 animal 與 user 我的最愛關係
     */
    public function likes()
    {
        return $this->belongsToMany('App\Models\User', 'animal_user_likes')
            ->withTimestamps();
    }
}
