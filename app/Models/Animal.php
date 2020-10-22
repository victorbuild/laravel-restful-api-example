<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'user_id', //不建議允許批量寫入，將在後續身分驗證章節修改這邊的設定。
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
}
