<?php

namespace Tests\Traits;

use App\Models\Menu;

trait DisableMenuObserver
{
    protected function setUpDisableMenuObserver(): void
    {
        Menu::unsetEventDispatcher();
    }
}
