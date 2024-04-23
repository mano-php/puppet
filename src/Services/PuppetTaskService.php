<?php

namespace Uupt\Puppet\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Uupt\Puppet\Models\PuppetEquipment;
use Uupt\Puppet\Models\PuppetTask;
use Slowlyo\OwlAdmin\Services\AdminService;

/**
 * 任务管理
 *
 * @method PuppetTask getModel()
 * @method PuppetTask|\Illuminate\Database\Query\Builder query()
 */
class PuppetTaskService extends AdminService
{
    protected string $modelName = PuppetTask::class;

    /**
     * 列表 获取数据
     *
     * @return array
     */
    public function list()
    {
        $query = $this->listQuery();

        $list = $query->paginate(request()->input('perPage', 20));
        $items = $list->items();
        $total = $list->total();
        foreach ($items as $key => $item) {
            $items[$key]['status_name'] = array_column(admin_dict()->getOptions('puppet.task.status'), 'label', 'value')[$item['status']];
            $items[$key]['content'] = json_decode($item['content'], true);
            if(isset($item['equipment_id']) && $item['equipment_id']>=1){
                $items[$key]['equipment_name'] = PuppetEquipment::query()->where(['id'=>$item['equipment_id']])->value('name');
            }else{
                $items[$key]['equipment_name'] = '-';
            }
        }

        return compact('items', 'total');
    }

    /**
     * saving 钩子 (执行于新增/修改前)
     *
     * 可以通过判断 $primaryKey 是否存在来判断是新增还是修改
     *
     * @param $data
     * @param $primaryKey
     *
     * @return void
     */
    public function saving(&$data, $primaryKey = '')
    {
        $data['content'] = json_encode($data['content']);
        if(!(isset($data['task_id']) && strlen($data['task_id'])>=1)){
            $data['task_id'] = Str::random(32);
        }
    }

    /**
     * 详情 获取数据
     *
     * @param $id
     *
     * @return Builder|Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function getDetail($id)
    {
        $query = $this->query();

        $this->addRelations($query, 'detail');
        $detail = $query->find($id);

        $detail['content'] = json_decode($detail['content'], true);
        $detail['result'] = json_decode($detail['result'], true);

        return $detail;
    }
}
