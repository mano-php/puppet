<?php

use Uupt\Puppet\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('puppet', [Controllers\PuppetController::class, 'index']);

/**
 * 获取设备列表
 */
Route::get('/puppet/get-goods-sku',[Controllers\PuppetEquipmentController::class,'getEquipment']);
/**
 * 搜索账号
 */
Route::get('/puppet/get-huolala-account',[Controllers\PuppetHuolalaAccountController::class,'getAccount']);

// 设备管理
Route::resource('puppet_equipment', \Uupt\Puppet\Http\Controllers\PuppetEquipmentController::class);
// 任务管理
Route::resource('puppet_task', \Uupt\Puppet\Http\Controllers\PuppetTaskController::class);
// 货拉拉账号
Route::resource('puppet_huolala_account', \Uupt\Puppet\Http\Controllers\PuppetHuolalaAccountController::class);
