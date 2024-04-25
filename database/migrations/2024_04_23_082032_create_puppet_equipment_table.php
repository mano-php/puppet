<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('puppet_equipment', function (Blueprint $table) {
            $table->comment('设备管理');
            $table->increments('id');
            $table->string('name',50)->index()->comment('名称');
            $table->string('desc')->nullable()->default('')->comment('描述');
            $table->string('uuid',50)->index()->comment('设备ID');
            $table->integer('status')->nullable()->default(2)->comment('状态'); // 默认离线
            $table->dateTime('last_time')->nullable()->index()->comment('最后交互时间');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('puppet_equipment');
    }
};
