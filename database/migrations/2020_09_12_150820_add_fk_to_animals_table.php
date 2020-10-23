<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFkToAnimalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->foreign('user_id')  // animals 資料表 user_id 參照欄位
                ->references('id')->on('users') // 參照users資料表的id
                ->onDelete('cascade');
                // 若users 刪除，動物資料一起刪除

            $table->foreign('type_id') // animals 資料表 type_id 參照欄位
                ->references('id')->on('types') // 參照types資料表的id
                ->onDelete('set null');
                // 若types刪除，相關動物資料type_id設為null
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropForeign('animals_user_id_foreign');
            $table->dropForeign('animals_type_id_foreign');
        });
    }
}
