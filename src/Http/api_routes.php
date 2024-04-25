<?php


use Uupt\Puppet\Http\Controllers;
use Illuminate\Support\Facades\Route;


// 获取任务
Route::get('/puppet/get-task',[Controllers\ApiController::class,'getTask']);

// 推送结果
Route::get('/puppet/push-task',[Controllers\ApiController::class,'pushResult']);

// 探针心跳
Route::get('/puppet/probe',[Controllers\ApiController::class,'probe']);

// 添加任务
Route::post('/puppet/add-task',[Controllers\ApiController::class,'addTask']);

// 查询任务
Route::get('/puppet/query-task',[Controllers\ApiController::class,'queryTask']);

// 获取重启
Route::get('/puppet/get-reboot',[Controllers\ApiController::class,'getReboot']);
