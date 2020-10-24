<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Type;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AnimalTest extends TestCase
{
    // 加入重置資料庫的Trait
    use RefreshDatabase;

    /**
     * 測試查看animal 列表的Json結構
     *
     * @return void
     */
    public function testViewAllAnimal()
    {
        // 模擬客戶端憑證授權，使用模型工廠建立1個客戶端
        Passport::actingAsClient(
            Client::factory()->create()
        );

        // 使用模型工廠製作5個分類
        Type::factory(5)->create();
        // 5個使用者
        User::factory(5)->create();
        // 10筆動物資料並隨機配對分類、使用者
        Animal::factory(10)->create();

        // 使用GET請求 api/animals 結果賦予 $response
        $response = $this->json('GET', 'api/animals');
        // 設定當測試發生例外錯誤時顯示訊息
        $this->withoutExceptionHandling();

        $resultStructure = [
            "data" => [
                // 因為有多筆資料，使用*檢查每一筆是否擁有以下欄位
                '*' => [
                    "id", "type_id", "type_name", "name", "birthday",
                    "age", "area", "fix", "description", "created_at",
                    "updated_at"
                ]
            ],
            "links" => [
                "first", "last", "prev", "next"
            ],
            "meta" => [
                "current_page", "from", "last_page", "path",
                "per_page", "to", "total"
            ]
        ];

        // assertJsonStructure 判斷 Json 結構是否與我們預計的結構相同
        $response->assertStatus(200)
            ->assertJsonStructure($resultStructure);
    }

    /**
     * 測試建立animal
     *
     * @return void
     */
    public function testCanCreateAnimal()
    {
        // 創建一個會員
        $user = User::factory()->create();

        // 模擬會員權限
        Passport::actingAs(
            $user,
            ['create-animals'] // 設定必須有create-animals的SCOPE權限
        );

        // 如果有例外顯示於測試OUTPUT介面上
        $this->withoutExceptionHandling();

        // 建立一個分類資料
        $type = Type::factory()->create();

        // 請求資料
        $formData = [
            'type_id' => $type->id,
            'name' => '大黑',
            'birthday' => '2017-01-01',
            'area' => '台北市',
            'fix' => '1'
        ];

        // 請求並傳入資料
        $response = $this->json(
            'POST',
            'api/animals',
            $formData
        );

        //檢查返回資料
        $response->assertStatus(201)  //狀態碼應屬於201
            ->assertJson(['data' => $formData]);
    }

    public function testCanNotCreateAnimal()
    {
        // 誰?沒有模擬會員權限的程式

        // 建立一個分類資料
        $type = Type::factory()->create();

        // 做什麼事? 請求時並傳入資料
        $response = $this->json(
            'POST',
            'api/animals',
            [
                'type_id' => $type->id,
                'name' => '大黑',
                'birthday' => '2017-01-01',
                'area' => '台北市',
                'fix' => '1'
            ]
        );
        
        // 結果? 檢查返回資料，沒有token，狀態碼回應401
        $response->assertStatus(401)
            ->assertJson(
                [
                    "message" => "Unauthenticated."
                ]
            );
    }
}
