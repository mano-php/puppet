<?php

namespace Uupt\Puppet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Slowlyo\OwlAdmin\Models\BaseModel as Model;

/**
 * 任务管理
 */
class PuppetTask extends Model
{
    use SoftDeletes;

    protected $table = 'puppet_task';
    
}
