<?php

namespace App\Controllers;

use MADEV\Core\Controllers\BaseController;

class Welcome extends BaseController
{
    public function index()
    {
        return $this->renderView('layouts/main_layout', ['content' => 'welcome_message']);
    }
}
