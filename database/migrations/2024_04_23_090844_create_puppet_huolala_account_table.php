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
        Schema::create('puppet_huolala_account', function (Blueprint $table) {
            $table->comment('货拉拉账号');
            $table->increments('id');
            $table->string('desc')->default('')->comment('描述备注');
            $table->string('mobile')->default('')->comment('手机号');
            $table->string('password')->default('')->comment('密码');
            $table->string('status')->index()->default('')->comment('状态');
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
        Schema::dropIfExists('puppet_huolala_account');
    }
};
