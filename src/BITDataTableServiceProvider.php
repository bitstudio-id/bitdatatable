<?php
/**
 * Created by PhpStorm.
 * User: Cacing
 * Date: 12/07/2019
 * Time: 14:50
 */

namespace BITStudio\Repository;

use Illuminate\Support\ServiceProvider;

class BITDataTableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
//        $this->loadViewsFrom(__DIR__.'/resources/views', 'dtb.test');
    }

    public function register()
    {
    }
}
