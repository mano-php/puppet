<?php

namespace Uupt\Puppet\Http\Controllers;

use Slowlyo\OwlAdmin\Renderers\Page;
use Slowlyo\OwlAdmin\Renderers\Form;
use Slowlyo\OwlAdmin\Controllers\AdminController;
use Uupt\Puppet\Services\PuppetTaskService;

/**
 * 任务管理
 *
 * @property PuppetTaskService $service
 */
class PuppetTaskController extends AdminController
{
    protected string $serviceName = PuppetTaskService::class;

    public function list(): Page
    {
        $crud = $this->baseCRUD()
            ->filterTogglable(true)
            ->filter($this->baseFilter()->body([
                amis()->GroupControl()->body([
                    amis()->SelectControl('type', '任务类型')->options(admin_dict()->getOptions('puppet.task.type')),
                    amis()->SelectControl('status', '任务类型')->options(admin_dict()->getOptions('puppet.task.status')),
                ]),
            ]))
			->headerToolbar([
				$this->createButton(true),
				...$this->baseHeaderToolBar()
			])
            ->columns([
                amis()->TableColumn('id', 'ID')->sortable(),
				amis()->TextControl('equipment_name', '设备')->static(),
				amis()->SelectControl('type', '任务类型')->options(admin_dict()->getOptions('puppet.task.type'))->static(),
                amis()->TagControl('status_name','任务状态')->color('${status==3?"success":((status==1 || status==2)?"active":"error")}')->displayMode('status')->type('tag')->static(),
				amis()->TableColumn('created_at', __('admin.created_at'))->set('type', 'datetime')->sortable(),
				amis()->TableColumn('updated_at', __('admin.updated_at'))->set('type', 'datetime')->sortable(),
                $this->rowActions(true)
            ]);

        return $this->baseList($crud);
    }

    public function form($isEdit = false): Form
    {
        return $this->baseForm()->body([
			amis()->SelectControl('equipment_id', '设备')->clearable()->source('/puppet/get-goods-sku')->remark('非必选、不选则随机设备执行。'),
			amis()->SelectControl('type', '任务类型')->options(admin_dict()->getOptions('puppet.task.type'))->required(),
            // 货拉拉任务采集
            amis()->Container()->hiddenOn('${type != 1}')->body([
                amis()->Divider()->title('货拉拉采集任务'),
                amis()->SelectControl('content.account','账号')->source('/puppet/get-huolala-account')->remark('货拉拉账号')->required(),
                amis()->InputCityControl('content.city', '城市')->extractValue(false)->allowDistrict(false)->required()->remark('选择采集的城市'),
                amis()->SelectControl('content.car_type', '车型')->options(admin_dict()->getOptions('puppet.task.huolala.car_type'))->required()->remark('选择要采集的车型'),
                amis()->TextControl('content.start_address','装货地')->required()->remark('装货地名称'),
                amis()->TextControl('content.end_address','卸货地')->required()->remark('卸货地名称'),
            ]),
        ]);
    }

    /**
     * 操作列
     *
     * @param bool   $dialog
     * @param string $dialogSize
     *
     * @return \Slowlyo\OwlAdmin\Renderers\Operation
     */
    protected function rowActions(bool|array $dialog = false, string $dialogSize = '')
    {
        if (is_array($dialog)) {
            return amis()->Operation()->label(__('admin.actions'))->buttons($dialog);
        }

        return amis()->Operation()->label(__('admin.actions'))->buttons([
            $this->rowShowButton($dialog, $dialogSize),
//            $this->rowEditButton($dialog, $dialogSize),
            $this->rowDeleteButton(),
        ]);
    }

    public function detail(): Form
    {
        return $this->baseDetail()->body([
            amis()->SelectControl('equipment_id', '设备')->source('/puppet/get-goods-sku')->static(),
            amis()->TextControl('task_id', '任务ID')->static(),
            amis()->SelectControl('type', '任务类型')->options(admin_dict()->getOptions('puppet.task.type'))->static(),
            amis()->SelectControl('status', '状态')->options(admin_dict()->getOptions('puppet.task.status'))->static(),

            // 货拉拉任务采集
            amis()->Container()->hiddenOn('${type != 1}')->body([
                amis()->Divider()->title('货拉拉采集任务'),
                amis()->SelectControl('content.account','账号')->source('/puppet/get-huolala-account')->static(),
                amis()->TextControl('content.city.city', '城市')->extractValue(false)->allowDistrict(false)->static(),
                amis()->SelectControl('content.car_type', '车型')->options(admin_dict()->getOptions('puppet.task.huolala.car_type'))->static(),
                amis()->TextControl('content.start_address','装货地')->static(),
                amis()->TextControl('content.end_address','卸货地')->static(),
            ]),
            // 货拉拉结果渲染
            amis()->Container()->hiddenOn('${type != 1 || (status != 3 && status != 4)}')->body([
                amis()->Divider()->title('货拉拉采集结果'),
                // 结果价格
                amis()->TextControl('result.price','结果价格')->permission(2)->static(),
                amis()->TextControl('content.map_distance','距离（Km）')->permission(2)->static(),
                amis()->TextControl('result.time','采集时间')->static(),
                amis()->TextControl('result.error_msg','采集反馈')->static()
            ])
        ]);
    }
}
