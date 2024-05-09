<?php

namespace ManoCode\Puppet\Http\Controllers;

use Slowlyo\OwlAdmin\Controllers\AdminController;

class PuppetController extends AdminController
{
    public function index()
    {
        $page = $this->basePage()->body('傀儡机模块');

        return $this->response()->success($page);
    }
}
