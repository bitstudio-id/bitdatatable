<?php
/**
 * This file is part of BITDataTable.
 *
 * (c) 2023 Ibnul Mutaki <ibnuul@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * User: cacing69
 * Date: 12/07/2019
 * Time: 14:50
 */

namespace BITStudio\BITDataTable\Providers;

use Illuminate\Support\ServiceProvider;

class BITDataTableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes/web.php');
//        $this->loadViewsFrom(__DIR__.'/resources/views', 'dtb.test');
    }

    public function register()
    {
    }
}
