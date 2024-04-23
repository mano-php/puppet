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
        Schema::create('puppet_task', function (Blueprint $table) {
            $table->comment('任务管理');
            $table->increments('id');
            $table->string('task_id',80)->index()->comment('任务ID');
            // 默认状态 等待中
            $table->integer('status')->default(1)->index()->comment('任务状态');
            $table->integer('equipment_id')->nullable()->index()->comment('设备');
            $table->integer('type')->index()->comment('任务类型');
            $table->text('content')->nullable()->comment('内容');
            $table->text('result')->nullable()->comment('结果');
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
        Schema::dropIfExists('puppet_task');
    }
};
