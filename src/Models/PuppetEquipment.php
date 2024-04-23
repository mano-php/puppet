<?php

namespace Uupt\Puppet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Slowlyo\OwlAdmin\Models\BaseModel as Model;

/**
 * 设备管理
 */
class PuppetEquipment extends Model
{
    use SoftDeletes;

    protected $table = 'puppet_equipment';
    
}
