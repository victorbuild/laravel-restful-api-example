<?php

namespace App\Http\Controllers;

use App\Http\Resources\TypeCollection;
use App\Http\Resources\TypeResource;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class TypeController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $types = Type::select('id', 'name', 'sort')->get();
        return new TypeCollection($types);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            // 另外一種驗證的寫法，使用陣列傳入驗證關鍵字
            'name' => [
                'required',
                'max:50',
                // types資料表中name欄位資料是唯一值
                Rule::unique('types', 'name')
            ],
            'sort' => 'nullable|integer',
        ]);
        // 如果沒有傳入sort欄位內容
        if (!isset($request->sort)) {
            // 找到目前資料表的排序欄位最大值
            $max = Type::max('sort');
            $request['sort'] = $max + 1; // 最大值加1寫入請求的資料中
        }
        $type = Type::create($request->all()); // 寫入資料庫

        return new TypeResource($type);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function show(Type $type)
    {
        return new TypeResource($type);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Type $type)
    {
        $this->validate($request, [
            'name' => [
                'max:50',
                // 更新時排除自己的名稱後，檢查是否為唯一值
                Rule::unique('types', 'name')->ignore($type->name, 'name')
            ],
            'sort' => 'nullable|integer',
        ]);

        $type->update($request->all());

        return new TypeResource($type);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function destroy(Type $type)
    {
        $type->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
