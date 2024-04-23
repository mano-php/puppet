<?php

namespace Uupt\Puppet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Slowlyo\OwlAdmin\Renderers\Page;
use Slowlyo\OwlAdmin\Renderers\Form;
use Slowlyo\OwlAdmin\Controllers\AdminController;
use Uupt\Puppet\Models\PuppetEquipment;
use Uupt\Puppet\Models\PuppetHuolalaAccount;
use Uupt\Puppet\Services\PuppetHuolalaAccountService;

/**
 * 货拉拉账号
 *
 * @property PuppetHuolalaAccountService $service
 */
class PuppetHuolalaAccountController extends AdminController
{
    protected string $serviceName = PuppetHuolalaAccountService::class;

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
				amis()->TableColumn('desc', '描述备注'),
				amis()->TableColumn('mobile', '手机号'),
//				amis()->TableColumn('password', '密码')->type('password'),
                amis()->TagControl('status_name','状态')->color('${status==1?"success":(status==2?"active":"error")}')->displayMode('status')->type('tag')->static(),
				amis()->TableColumn('created_at', __('admin.created_at'))->set('type', 'datetime')->sortable(),
				amis()->TableColumn('updated_at', __('admin.updated_at'))->set('type', 'datetime')->sortable(),
                $this->rowActions(true)
            ]);

        return $this->baseList($crud);
    }
    public function getAccount(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = PuppetHuolalaAccount::query()->where(['status'=>1]);
        if(strlen(strval($request->input('term')))>=1){
            $query->where(function($where) use($request){
                $where->where('mobile','like',"%{$request->input('term')}%")->orWhere('description','like',"%{$request->input('term')}%")->orWhere('desc','like',"%{$request->input('term')}%");
            });
        }
        return response()->json([
            'options'=>$query->select([
                DB::raw('mobile as label'),
                DB::raw('id as value'),
            ])->get()
        ]);
    }

    public function form($isEdit = false): Form
    {
        return $this->baseForm()->body([
			amis()->TextControl('mobile', '手机号')->required(),
			amis()->TextControl('password', '密码')->required(),
            amis()->TextareaControl('desc', '描述备注'),
			amis()->SelectControl('status', '状态')->value(1)->options(admin_dict()->getOptions('puppet.huolala.account_status'))->required(),
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
//            $this->rowShowButton($dialog, $dialogSize),
            $this->rowEditButton($dialog, $dialogSize),
            $this->rowDeleteButton(),
        ]);
    }
}
