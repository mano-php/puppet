<?php

namespace Uupt\Puppet\Services;

use Illuminate\Support\Str;
use Uupt\Puppet\Models\PuppetEquipment;
use Slowlyo\OwlAdmin\Services\AdminService;

/**
 * 设备管理
 *
 * @method PuppetEquipment getModel()
 * @method PuppetEquipment|\Illuminate\Database\Query\Builder query()
 */
class PuppetEquipmentService extends AdminService
{
    protected string $modelName = PuppetEquipment::class;
    /**
     * 列表 获取数据
     *
     * @return array
     */
    public function list()
    {
        $query = $this->listQuery();

        $list  = $query->paginate(request()->input('perPage', 20));
        $items = $list->items();
        $total = $list->total();
        foreach ($items as $key=>$item){
            $items[$key]['status_name'] = array_column(admin_dict()->getOptions('puppet.equipment.status'),'label','value')[$item['status']];
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
        if (!(isset($data['uuid']) && strlen($data['uuid']) >= 1)) {
            $data['uuid'] = Str::uuid();
        }
    }
}
