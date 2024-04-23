<?php

namespace Uupt\Puppet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Slowlyo\OwlAdmin\Renderers\Page;
use Slowlyo\OwlAdmin\Renderers\Form;
use Slowlyo\OwlAdmin\Controllers\AdminController;
use Uupt\Approval\Models\ApprovalBind;
use Uupt\Erp\Models\Good;
use Uupt\Erp\Models\GoodsUnit;
use Uupt\Puppet\Models\PuppetEquipment;
use Uupt\Puppet\Services\PuppetEquipmentService;

/**
 * 设备管理
 *
 * @property PuppetEquipmentService $service
 */
class PuppetEquipmentController extends AdminController
{
    protected string $serviceName = PuppetEquipmentService::class;

    public function list(): Page
    {
        $crud = $this->baseCRUD()
            ->filterTogglable(false)
			->headerToolbar([
				$this->createButton(true),
				...$this->baseHeaderToolBar()
			])
            ->columns([
                amis()->TableColumn('id', 'ID')->sortable(),
				amis()->TableColumn('name', '名称'),
				amis()->TableColumn('desc', '描述'),
                amis()->TagControl('status_name','状态')->color('${status==1?"success":(status==3?"active":"error")}')->displayMode('status')->type('tag')->static(),
                amis()->TableColumn('uuid', '设备ID')->sortable(),
				amis()->TableColumn('created_at', __('admin.created_at'))->set('type', 'datetime')->sortable(),
				amis()->TableColumn('updated_at', __('admin.updated_at'))->set('type', 'datetime')->sortable(),
                $this->rowActions(true)
            ]);

        return $this->baseList($crud);
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
//            $this->rowShowButton($dialog, $dialogSize),
            $this->rowEditButton($dialog, $dialogSize),
            $this->rowDeleteButton(),
        ]);
    }

    public function form($isEdit = false): Form
    {
        return $this->baseForm()->body([
            amis()->HiddenControl('id','ID')->required(),
            amis()->TextControl('name', '名称'),
			amis()->TextareaControl('desc', '描述'),
        ]);
    }
    public function getEquipment(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = PuppetEquipment::query()->where(['status'=>1]);
        if(strlen(strval($request->input('term')))>=1){
            $query->where(function($where) use($request){
                $where->where('name','like',"%{$request->input('term')}%")->orWhere('description','like',"%{$request->input('term')}%")->orWhere('desc','like',"%{$request->input('term')}%");
            });
        }
        return response()->json([
            'options'=>$query->select([
                DB::raw('name as label'),
                DB::raw('id as value'),
            ])->get()
        ]);
    }
}
