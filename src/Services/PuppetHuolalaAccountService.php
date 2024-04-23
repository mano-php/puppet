<?php

namespace Uupt\Puppet\Services;

use Uupt\Puppet\Models\PuppetHuolalaAccount;
use Slowlyo\OwlAdmin\Services\AdminService;

/**
 * 货拉拉账号
 *
 * @method PuppetHuolalaAccount getModel()
 * @method PuppetHuolalaAccount|\Illuminate\Database\Query\Builder query()
 */
class PuppetHuolalaAccountService extends AdminService
{
    protected string $modelName = PuppetHuolalaAccount::class;


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
            $items[$key]['status_name'] = array_column(admin_dict()->getOptions('puppet.huolala.account_status'),'label','value')[$item['status']];
        }

        return compact('items', 'total');
    }
}
