<?php

namespace Uupt\Puppet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Uupt\Puppet\Models\PuppetEquipment;
use Uupt\Puppet\Models\PuppetHuolalaAccount;
use Uupt\Puppet\Models\PuppetTask;

/**
 *
 */
class ApiController extends Controller
{
    public function getTask(Request $request): array
    {
        $uuid = strval($request->input('uuid'));
        if(!($puppetEquipment = PuppetEquipment::query()->where(['uuid'=>$uuid])->where('status','<>',3)->first())){
            return [
                'status'=>'error',
                'msg'=>'设备不存在',
                'data'=>[]
            ];
        }
        // 更新状态
        PuppetEquipment::query()->where(['uuid'=>$uuid])->update(['status'=>1]);
        // 获取采集任务
        if($task = PuppetTask::query()->where(function($query) use($puppetEquipment){
            $query->where(['equipment_id'=>$puppetEquipment->getAttribute('id')])->orWhereNull('equipment_id');
        })->where('status',1)->first()){
            // 更新状态为 处理中
            PuppetTask::query()->where(['id'=>$task->getAttribute('id')])->update(['status'=>2,'equipment_id'=>$puppetEquipment->getAttribute('id')]);
            $task = $task->toArray();
            $task['content'] = json_decode($task['content'],true);
            $task['content']['account'] = PuppetHuolalaAccount::query()->where(['id'=>$task['content']['account']])->select([
                'mobile',
                'password'
            ])->first();
            return [
                'status'=>'success',
                'msg'=>'获取成功',
                'task'=>$task
            ];
        }
        return ['status'=>'success','msg'=>'暂无任务'];
    }
    public function pushResult(Request $request): array
    {
        $uuid = strval($request->input('uuid'));
        if(!($puppetEquipment = PuppetEquipment::query()->where(['uuid'=>$uuid])->where('status','<>',3)->first())){
            return [
                'status'=>'error',
                'msg'=>'设备不存在',
                'data'=>[]
            ];
        }
        $task_id = strval($request->input('task_id'));
        if(strlen($task_id)<=0){
            return [
                'status'=>'error',
                'msg'=>'任务ID不能为空'
            ];
        }
        // 只有执行中的任务可以 反馈结果
        if(!($taskInfo = PuppetTask::query()->where(['equipment_id'=>$puppetEquipment->getAttribute('id'),'task_id'=>$task_id,'status'=>2])->first())){
            return [
                'status'=>'error',
                'msg'=>'任务不存在',
                'data'=>[]
            ];
        }
        $result = strval($request->input('result'));
        if(strlen($result)<=0){
            return [
                'status'=>'error',
                'msg'=>'结果不能为空',
                'data'=>[]
            ];
        }
        $status = strval($request->input('status'));
        if(!in_array($status,['success','error'])){
            return [
                'status'=>'error',
                'msg'=>'状态错误',
                'data'=>[]
            ];
        }
        $taskInfo->setAttribute('result',$result);
        $taskInfo->setAttribute('status',$status=='success'?3:4);
        $taskInfo->save();
        return [
            'status'=>'success',
            'msg'=>'反馈成功'
        ];
    }

    /**
     * 探针保活 TODO 定时清除 超过20秒没有心跳的 设备
     * @param Request $request
     * @return array|string[]
     */
    public function probe(Request $request)
    {
        $uuid = strval($request->input('uuid'));
        if(!($puppetEquipment = PuppetEquipment::query()->where(['uuid'=>$uuid])->where('status','<>',3)->first())){
            return [
                'status'=>'error',
                'msg'=>'设备不存在',
                'data'=>[]
            ];
        }
        $puppetEquipment->setAttribute('status',1);
        $puppetEquipment->save();
        return [
            'status'=>'success',
            'msg'=>'操作成功'
        ];
    }
}
