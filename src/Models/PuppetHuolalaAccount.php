<?php

namespace ManoCode\Puppet\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Slowlyo\OwlAdmin\Models\BaseModel as Model;

/**
 * 货拉拉账号
 */
class PuppetHuolalaAccount extends Model
{
    use SoftDeletes;

    protected $table = 'puppet_huolala_account';
    
}
