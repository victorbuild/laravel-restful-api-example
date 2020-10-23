<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnimalCollection;
use App\Http\Resources\AnimalResource;
use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AnimalController extends Controller
{

    public function __construct()
    {
        $this->middleware('client', ['only' => ['index', 'show']]);
        $this->middleware('scopes:create-animals', ['only' => ['store']]);
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 使用網址設定為快取檔案名稱
        // 取得網址
        $url = $request->url();
        // 取得query的參數 例如：?limit=5&page=2 網址問號後面的參數
        $queryParams = $request->query();
        // 每個人請求的query參數順序可能不同，使用參數第一個英文字排序
        ksort($queryParams);
        // 利用http_build_query方法將查詢參數轉為字串
        $queryString = http_build_query($queryParams);
        // 組合成完整網址
        $fullUrl = "{$url}?{$queryString}";
        // 使用 Laravel 的快取方法檢查是否有快取紀錄
        if (Cache::has($fullUrl)) {
            // 使用 return 直接回傳快取資料，不做其他程式邏輯
            return Cache::get($fullUrl);
        }

        // 設定預設值
        $limit = $request->limit ?? 10; // 未設定預設值為10

        // 建立查詢建構器，分段的方式撰寫SQL語句。
        $query = Animal::query()->with('type');

        // 篩選程式邏輯，如果有設定filters參數
        if (isset($request->filters)) {
            $filters = explode(',', $request->filters);
            foreach ($filters as $key => $filter) {
                list($key, $value) = explode(':', $filter);
                $query->where($key, 'like', "%$value%");
            }
        }

        // 排列順序
        if (isset($request->sorts)) {
            $sorts = explode(',', $request->sorts);
            foreach ($sorts as $key => $sort) {
                list($key, $value) = explode(':', $sort);
                if ($value == 'asc' || $value == 'desc') {
                    $query->orderBy($key, $value);
                }
            }
        } else {
            // 將原本的排序方法移至這裡，如果沒有設定條件，預設id大到小
            $query->orderBy('id', 'desc');
        }

        $animals = $query->paginate($limit)->appends($request->query());

        // 沒有快取紀錄記住資料，並設定60秒過期，快取名稱使用網址命名。
        return Cache::remember($fullUrl, 60, function () use ($animals) {
            return new AnimalCollection($animals);
        });
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
            'type_id' => 'nullable|exists:types,id',
            'name' => 'required|string|max:255',    // 必填文字最多255字元
            // 允許null或日期格式，使用PHP strtotime檢查傳入的日期字串
            'birthday' => 'nullable|date',
            'area' => 'nullable|string|max:255',    // 允許null或文字最多255字元
            'fix' => 'required|boolean',            // 必填並且為布林值
            'description' => 'nullable',            // 允許null
            'personality' => 'nullable'             // 允許null
        ]);

        $animal = auth()->user()->animals()->create($request->all());
        
        $animal = $animal->refresh();

        return new AnimalResource($animal);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Animal  $animal
     * @return \Illuminate\Http\Response
     */
    public function show(Animal $animal)
    {
        return new AnimalResource($animal);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Animal  $animal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Animal $animal)
    {
        $this->validate($request, [
            'type_id' => 'nullable|exists:types,id',
            'name' => 'string|max:255',             // 文字類型最多255字元
            // 允許null並且為日期格式
            'birthday' => 'nullable|date',
            'area' => 'nullable|string|max:255',    // 允許null或文字最多255字元
            'fix' => 'boolean',                     // 若填寫必須是布林值
            'description' => 'nullable|string',     // 允許null或文字
            'personality' => 'nullable|string',     // 允許null或文字
        ]);

        $animal->update($request->all());

        return new AnimalResource($animal);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Animal  $animal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Animal $animal)
    {
        $animal->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
