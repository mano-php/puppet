<?php

namespace Uupt\Puppet;

use Illuminate\Support\Facades\Cache;
use Slowlyo\OwlAdmin\Renderers\TextControl;
use Slowlyo\OwlAdmin\Extend\ServiceProvider;
use Slowlyo\OwlDict\Models\AdminDict as AdminDictModel;

class PuppetServiceProvider extends ServiceProvider
{
    protected $menu = [
        [
            'parent' => 0,
            'title' => '傀儡机管理',
            'url' => '/puppet-nav',
            'url_type' => '1',
            'icon' => 'game-icons:puppet',
        ],
        [
            'parent' => '傀儡机管理',
            'title' => '任务管理',
            'url' => '/puppet_equipment',
            'url_type' => '1',
            'icon' => 'streamline:petri-dish-lab-equipment-solid',
        ],
        [
            'parent' => '傀儡机管理',
            'title' => '任务管理',
            'url' => '/puppet_task',
            'url_type' => '1',
            'icon' => 'bi:list-task',
        ],
        [
            'parent' => '傀儡机管理',
            'title' => '货拉拉账号',
            'url' => '/puppet_huolala_account',
            'url_type' => '1',
            'icon' => 'bx:bxs-user-account',
        ],
    ];
    public function install()
    {
        parent::install();
        // 安装字典数据
        $this->installDict();
        // 清空字典缓存
        Cache::forget('admin_dict_cache_key');
        Cache::forget('admin_dict_valid_cache_key');
    }
    public function boot()
    {
        parent::boot();
        require_once(__DIR__.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'api_routes.php');
    }
    protected function installDict()
    {
        $dicts = [
            [
                'key' => 'puppet.equipment.status',
                'value' => '傀儡机状态',
                'keys' => [
                    ['key' => 1, 'value' => '正常'],
                    ['key' => 2, 'value' => '离线'],
                    ['key' => 3, 'value' => '禁用']
                ]
            ],
            [
                'key' => 'puppet.task.status',
                'value' => '傀儡机任务状态',
                'keys' => [
                    ['key' => 1, 'value' => '等待'],
                    ['key' => 2, 'value' => '执行中'],
                    ['key' => 3, 'value' => '成功'],
                    ['key' => 4, 'value' => '失败']
                ]
            ],
            [
                'key' => 'puppet.task.type',
                'value' => '傀儡机任务类型',
                'keys' => [
                    ['key' => 1, 'value' => '货拉拉计价'],
                ]
            ],
            [
                'key' => 'puppet.task.huolala.car_type',
                'value' => '傀儡机任务类型货拉拉车型',
                'keys' => [
                    ['key' => '微面', 'value' => '微面'],
                    ['key' => '小面', 'value' => '小面'],
                    ['key' => '中面', 'value' => '中面'],
                    ['key' => '依维柯', 'value' => '依维柯'],
                    ['key' => '微货', 'value' => '微货'],
                    ['key' => '小货', 'value' => '小货'],
                    ['key' => '中货', 'value' => '中货'],
                ]
            ],
            [
                'key' => 'puppet.huolala.account_status',
                'value' => '货拉拉账号状态',
                'keys' => [
                    ['key' => 1, 'value' => '正常'],
                    ['key' => 2, 'value' => '封禁'],
                    ['key' => 3, 'value' => '禁用'],
                ]
            ],
        ];
        foreach ($dicts as $dict) {
            $dictModel = AdminDictModel::query()->where('key', $dict['key'])->first();
            if (!$dictModel) {
                $dictModel = new AdminDictModel();
                $dictModel->value = $dict['value'];
                $dictModel->enabled = 1;
                $dictModel->key = $dict['key'];
                $dictModel->save();
            }
            foreach ($dict['keys'] as $value) {
                $dictValueModel = AdminDictModel::query()->where('parent_id', $dictModel->id)->where('key', $value['key'])->first();
                if (!$dictValueModel) {
                    $dictValueModel = new AdminDictModel();
                    $dictValueModel->parent_id = $dictModel->id;
                    $dictValueModel->key = $value['key'];
                    $dictValueModel->value = $value['value'];
                    $dictValueModel->enabled = 1;
                    $dictValueModel->save();
                }
            }
        }
    }

    public function settingForm()
    {
        return $this->baseSettingForm()->body([
            TextControl::make()->name('value')->label('Value')->required(true),
        ]);
    }
}
