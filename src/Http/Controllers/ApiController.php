<?php

namespace Uupt\Puppet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Uupt\Puppet\Models\PuppetEquipment;
use Uupt\Puppet\Models\PuppetHuolalaAccount;
use Uupt\Puppet\Models\PuppetTask;

/**
 *
 */
class ApiController extends Controller
{
    /**
     * 腾讯地图KEY
     * @var string
     */
    protected string $map_key = 'PABBZ-6JVYB-AXPUP-NSY7I-SGITE-WQFAY';

    public function getTask(Request $request): array
    {
        $uuid = strval($request->input('uuid'));
        if (!($puppetEquipment = PuppetEquipment::query()->where(['uuid' => $uuid])->where('status', '<>', 3)->first())) {
            $puppetEquipment = new PuppetEquipment();
            $puppetEquipment->setAttribute('name', $uuid);
            $puppetEquipment->setAttribute('desc', $uuid);
            $puppetEquipment->setAttribute('uuid', $uuid);
            $puppetEquipment->setAttribute('reboot', 0);
            $puppetEquipment->setAttribute('status', 1);
            $puppetEquipment->setAttribute('created_at', date('Y-m-d H:i:s'));
            $puppetEquipment->setAttribute('updated_at', date('Y-m-d H:i:s'));
            $puppetEquipment->save();
//            return [
//                'status' => 'error',
//                'msg' => '设备不存在',
//                'data' => []
//            ];
        }
        // 循环设备 （模拟下线） 超过两分钟
        foreach (PuppetEquipment::query()->where(['status' => 1])->get() as $equipment) {
            // 两分钟不消费 则视为掉线
            if (strtotime($equipment->last_time) < (time() - 120)) {
                PuppetEquipment::query()->where(['id' => $equipment->id])->update(['status' => 2]);
            }
        }
        // 更新状态 最后心跳时间
        PuppetEquipment::query()->where(['uuid' => $uuid])->update(['status' => 1, 'last_time' => date('Y-m-d H:i:s')]);
        // 获取采集任务
        if ($task = PuppetTask::query()->where(function ($query) use ($puppetEquipment) {
            $query->where(['equipment_id' => $puppetEquipment->getAttribute('id')])
                ->orWhereNull('equipment_id');
        })->where('status', 1)->first()) {
            // 更新状态为 处理中
            PuppetTask::query()->where(['id' => $task->getAttribute('id')])->update(['status' => 2, 'equipment_id' => $puppetEquipment->getAttribute('id')]);
            $task = $task->toArray();
            $task['content'] = json_decode($task['content'], true);
            $task['content']['account'] = PuppetHuolalaAccount::query()->where(['id' => $task['content']['account']])->select([
                'mobile',
                'password'
            ])->first();

            $task['content']['start_address'] = explode('-', $task['content']['start_address'])[0];
            $task['content']['end_address'] = explode('-', $task['content']['end_address'])[0];

//            $task['content']['start_address'] = $task['content']['city']['city'] . ' - ' . $task['content']['s_address'];
//            $task['content']['end_address'] = $task['content']['city']['city'] . ' - ' .$task['content']['e_address'];

//            $task['content']['start_address'] = $task['content']['city']['city'] . ' - ' . $task['content']['s_address'];
//            $task['content']['end_address'] = $task['content']['city']['city'] . ' - ' .$task['content']['e_address'];

            return [
                'status' => 'success',
                'msg' => '获取成功',
                'task' => $task
            ];
        }
        return ['status' => 'success', 'msg' => '暂无任务'];
    }

    public function pushResult(Request $request): array
    {
        $uuid = strval($request->input('uuid'));
        if (!($puppetEquipment = PuppetEquipment::query()->where(['uuid' => $uuid])->where('status', '<>', 3)->first())) {
            return [
                'status' => 'error',
                'msg' => '设备不存在',
                'data' => []
            ];
        }
        $task_id = strval($request->input('task_id'));
        if (strlen($task_id) <= 0) {
            return [
                'status' => 'error',
                'msg' => '任务ID不能为空'
            ];
        }
        // 只有执行中的任务可以 反馈结果
        if (!($taskInfo = PuppetTask::query()->where(['equipment_id' => $puppetEquipment->getAttribute('id'), 'task_id' => $task_id, 'status' => 2])->first())) {
            return [
                'status' => 'error',
                'msg' => '任务不存在',
                'data' => []
            ];
        }
        $result = strval($request->input('result'));
        if (strlen($result) <= 0) {
            return [
                'status' => 'error',
                'msg' => '结果不能为空',
                'data' => []
            ];
        }
        $result = json_decode($result, true);
        $result['time'] = date('Y-m-d H:i:s');
        $status = strval($request->input('status'));
        if (!in_array($status, ['success', 'error'])) {
            return [
                'status' => 'error',
                'msg' => '状态错误',
                'data' => []
            ];
        }
        $content = json_decode($taskInfo->getAttribute('content'), true);
        if ($status === 'success') {
            try {
                $map_response = json_decode(file_get_contents("https://apis.map.qq.com/ws/direction/v1/driving/?from={$this->keywordToLocation($result['start_address'],$content['city']['city'])}&to={$this->keywordToLocation($result['end_address'],$content['city']['city'])}&output=json&key={$this->map_key}"), true);
                if (!(isset($map_response['result']['routes']) && is_array($map_response['result']['routes']) && count($map_response['result']['routes']) >= 1)) {
                    throw new \Exception("导航距离获取失败form={$this->keywordToLocation($result['start_address'],$content['city']['city'])}&to={$this->keywordToLocation($result['end_address'],$content['city']['city'])}");
                }
                $result['map_distance'] = sprintf('%.2f', $map_response['result']['routes'][0]['distance'] / 1000);
            } catch (\Throwable $throwable) {
                $result['map_error_msg'] = $throwable->getMessage();
            }
        }
        $taskInfo->setAttribute('result', json_encode($result));
        $taskInfo->setAttribute('status', $status == 'success' ? 3 : 4);
        $taskInfo->save();
        return [
            'status' => 'success',
            'msg' => '反馈成功'
        ];
    }

    protected function keywordToLocation(string $keyWord, string $city): string
    {
        $response = json_decode(file_get_contents("https://apis.map.qq.com/ws/place/v1/suggestion?key={$this->map_key}&region_fix=1&keyword={$keyWord}&region={$city}"), true);
        if (isset($response['data']) && is_array($response['data']) && count($response['data']) >= 1) {
            return "{$response['data'][0]['location']['lat']},{$response['data'][0]['location']['lng']}";
        }
        throw new \Exception("{$city}-{$keyWord} 找不到需要的地点");
    }

    public function addTask(Request $request)
    {

        $task_lists = $request->post('task_lists', []);

        $task_id_lists = [];
//        $equipment_id = PuppetEquipment::query()->where(['status' => 1])->skip(rand(0, PuppetEquipment::query()->where(['status' => 1])->count() - 1))->value('id');

        foreach ($task_lists as $item) {
            $puppetTask = new PuppetTask();
            $puppetTask->setAttribute('status', 1);
            $puppetTask->setAttribute('task_id', Str::random(64));
//            $puppetTask->setAttribute('equipment_id', $equipment_id);
            $puppetTask->setAttribute('type', 1);
            $puppetTask->setAttribute('content', json_encode([
                'account' => 1,
                'city' => [
                    'city' => $item['city'],
                ],
                'map_distance' => $item['map_distance'], // 地图导航距离
                'car_type' => $item['car_type'],
                'start_address' => $item['city'] . $item['start_address'],
                'end_address' => $item['city'] . $item['end_address'],
            ]));
            $puppetTask->save();
            $task_id_lists[$item['uuid']] = $puppetTask->getAttribute('task_id');
        }
        return ['status' => 'success', 'msg' => '新增成功', 'data' => [
            'task_id_lists' => $task_id_lists
        ]];
    }

    /**
     * 探针保活 TODO 定时清除 超过20秒没有心跳的 设备
     * @param Request $request
     * @return array|string[]
     */
    public function probe(Request $request)
    {
        $uuid = strval($request->input('uuid'));
        if (!($puppetEquipment = PuppetEquipment::query()->where(['uuid' => $uuid])->where('status', '<>', 3)->first())) {
            return [
                'status' => 'error',
                'msg' => '设备不存在',
                'data' => []
            ];
        }
        $puppetEquipment->setAttribute('status', 1);
        $puppetEquipment->save();
        return [
            'status' => 'success',
            'msg' => '操作成功'
        ];
    }

    /**
     * 查询任务
     * @param Request $request
     * @return array
     */
    public function queryTask(Request $request)
    {
        $task_lists = $request->query('task_lists');
        foreach ($task_lists as $key => $item) {
            $taskInfo = PuppetTask::query()->where(['task_id' => $item['task_id']])->first();
            if ($taskInfo->getAttribute('status') === 4) {
                $result = json_decode($taskInfo->getAttribute('result'), true);
                // 错误消息获取
                $task_lists[$key]['error_msg'] = isset($result['error_msg']) ? strval($result['error_msg']) : '';
            }
            if ($taskInfo->getAttribute('status') === 3) {
                $result = json_decode($taskInfo->getAttribute('result'), true);
                $task_lists[$key]['price'] = isset($result['price']) ? $result['price'] : '';
                $task_lists[$key]['discount_price'] = isset($result['discount_price']) ? $result['discount_price'] : '';
            }
            $task_lists[$key]['status'] = array_column(admin_dict()->getOptions('puppet.task.status'), 'label', 'value')[$taskInfo->getAttribute('status')];
        }
        return [
            'status' => 'success',
            'msg' => '查询成功',
            'data' => [
                'task_lists' => $task_lists
            ]
        ];
    }

    /**
     * 获取重启状态
     * @param Request $request
     * @return array|string[]
     */
    public function getReboot(Request $request): array
    {
        $uuid = strval($request->input('uuid'));
        if (strlen($uuid) <= 0) {
            return [
                'status' => 'error',
                'msg' => '设备标识不能为空'
            ];
        }
        if (!($puppetEquipment = PuppetEquipment::query()->where(['uuid' => $uuid])->where('status', '<>', 3)->first())) {
            $puppetEquipment = new PuppetEquipment();
            $puppetEquipment->setAttribute('name', $uuid);
            $puppetEquipment->setAttribute('desc', $uuid);
            $puppetEquipment->setAttribute('uuid', $uuid);
            $puppetEquipment->setAttribute('reboot', 0);
            $puppetEquipment->setAttribute('status', 1);
            $puppetEquipment->setAttribute('created_at', date('Y-m-d H:i:s'));
            $puppetEquipment->setAttribute('updated_at', date('Y-m-d H:i:s'));
            $puppetEquipment->save();
        }
        if ($puppetEquipment->getAttribute('reboot') == 1) {
            $puppetEquipment->setAttribute('reboot', 0);
            $puppetEquipment->save();
            return [
                'status' => 'success',
                'msg' => '查询成功',
                'data' => [
                    'reboot' => 1
                ]
            ];
        }else{
            return [
                'status' => 'success',
                'msg' => '查询成功',
                'data' => [
                    'reboot' => 0
                ]
            ];
        }

    }
}
