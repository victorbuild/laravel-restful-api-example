<?php

namespace App\Http\Controllers\Api\V1\Animal;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnimalRequest;
use App\Http\Requests\UpdateAnimalRequest;
use App\Http\Resources\AnimalCollection;
use App\Http\Resources\AnimalResource;
use App\Models\Animal;
use App\Services\AnimalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AnimalController extends Controller
{
    // class定義屬性預計存放 AnimalService物件。
    private $animalService;

    public function __construct(AnimalService $animalService)
    {
        // 外部物件賦予自身類別的屬性
        $this->animalService = $animalService;
        $this->middleware('client', ['only' => ['index', 'show']]);
        $this->middleware('scopes:create-animals', ['only' => ['store']]);
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/animals",
     *      operationId="animalIndex",
     *      tags={"Animal"},
     *      summary="取得動物資源列表",
     *      description="查看動物資源列表",
     *      security={
     *         {
     *              "passport": {}
     *         }
     *      },
     *      @OA\Parameter(
     *          name="filters",
     *          description="篩選條件",
     *          required=false,
     *          in="query",
     *          example="name:黑",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sorts",
     *          description="排序條件",
     *          required=false,
     *          in="query",
     *          example="name:asc,id:desc",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          description="設定回傳資料筆數(預設10筆資料)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="請求成功",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                      property="data",
     *                      @OA\Items(ref="#/components/schemas/Animal")
     *                 )
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="身分驗證未通過"
     *      ),
     *      @OA\Response(
     *          response=429,
     *          description="請求次數超過限制"
     *      )
     * )
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

        $animals = $this->animalService->getListData($request);

        // 沒有快取紀錄記住資料，並設定60秒過期。
        return Cache::remember($fullUrl, 60, function () use ($animals) {
            return new AnimalCollection($animals);
        });
    }

    /**
     * @OA\Post(
     *      path="/api/v1/animals",
     *      operationId="animalStore",
     *      tags={"Animal"},
     *      summary="新增動物資料",
     *      description="新增動物資料",
     *      security={
     *         {
     *              "passport": {}
     *         }
     *      },
     *      @OA\RequestBody(
     *           @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(ref="#/components/schemas/StoreAnimalRequest"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="新增成功",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                      property="data",
     *                      ref="#/components/schemas/Animal"
     *                 )
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="身分驗證未通過"
     *      )
     * )
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAnimalRequest $request)
    {
        $this->authorize('create', Animal::class);

        // try包住可能會出錯的程式碼
        try {
            // 開始資料庫交易
            DB::beginTransaction();
            // 登入會員新增動物，建立會員與動物的關係，返回動物物件
            $animal = auth()->user()->animals()->create($request->all());
            // 刷新動物物件(從資料庫讀取完整欄位資料)
            $animal = $animal->refresh();
            // 寫入第二張資料表
            // 製作建立動物資源同時將動物加到我的最愛
            $animal->likes()->attach(auth()->user()->id);
            // 提交資料庫，正式寫入資料庫
            DB::commit();
            // 回傳資料
            return new AnimalResource($animal);
            // 若以上範圍有擷取到例外錯誤，執行catch的程式
        } catch (\Exception $e) {
            // 擷取到Exception例外錯誤，上面做了什麼事這裡撰寫復原的程式
            // 恢復資料庫交易
            DB::rollback();

            // 或紀錄Log
            $errorMessage = 'MESSAGE: ' . $e->getMessage();
            Log::error($errorMessage);
            // 回傳錯誤訊息並且設定500狀態碼
            return response(['error' => '程式異常'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/animals/{id}",
     *      operationId="animalShow",
     *      tags={"Animal"},
     *      summary="查看單一動物資源",
     *      description="查看單一動物資源",
     *      @OA\Parameter(
     *          name="id",
     *          description="Animal id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      security={
     *         {
     *              "passport": {}
     *         }
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="請求成功",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                      property="data",
     *                      ref="#/components/schemas/Animal"
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=401,
     *          description="身分驗證未通過"
     *      ),
     * )
     *
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
     * @OA\Patch(
     *      path="/api/v1/animals/{id}",
     *      operationId="animalUpdate",
     *      tags={"Animal"},
     *      summary="更新動物資料",
     *      description="更新動物資料",
     *      @OA\Parameter(
     *          name="id",
     *          description="Animal id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *           @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(ref="#/components/schemas/UpdateAnimalRequest"),
     *          )
     *      ),
     *      security={
     *         {
     *              "passport": {}
     *         }
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="請求成功",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                      property="data",
     *                      ref="#/components/schemas/Animal"
     *                 )
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="找不到資源"
     *       )
     * )
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Animal  $animal
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAnimalRequest $request, Animal $animal)
    {
        $this->authorize('update', $animal);

        $animal->update($request->all());

        return new AnimalResource($animal);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/animals/{id}",
     *      operationId="animalDelete",
     *      tags={"Animal"},
     *      summary="刪除動物資料",
     *      description="刪除動物資料",
     *      @OA\Parameter(
     *          name="id",
     *          description="Animal id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      security={
     *         {
     *              "passport": {}
     *         }
     *      },
     *      @OA\Response(
     *          response=204,
     *          description="刪除成功回傳空值",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="找不到資源"
     *       )
     * )
     *
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Animal  $animal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Animal $animal)
    {
        $this->authorize('delete', $animal);

        $animal->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
