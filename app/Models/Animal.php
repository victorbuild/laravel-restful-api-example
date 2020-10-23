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
