<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnimalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Animal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // numberBetween隨機產生範圍1到3之間的整數。
            'type_id' => $this->faker->numberBetween(1, 3),
            'name' => $this->faker->name, // 隨機名稱
            'birthday' => $this->faker->date(),  // 隨機日期
            'area' => $this->faker->city, // 隨機城市名稱
            'fix' => $this->faker->boolean, // 隨機布林值
            'description' => $this->faker->text,  // 隨機一段內容
            'personality' => $this->faker->text, // 隨機一段內容
            'user_id' => User::all()->random()->id // 隨機綁定一位會員
        ];
    }
}
