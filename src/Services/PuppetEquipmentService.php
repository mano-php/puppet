<?php

namespace ManoCode\Puppet\Services;

use Illuminate\Support\Str;
use ManoCode\Puppet\Models\PuppetEquipment;
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
            $items[$key]['reboot_name'] = $item['reboot'] == 1?'等待重启':'正常';
        }

        return compact('items', 'total');
    }
}
