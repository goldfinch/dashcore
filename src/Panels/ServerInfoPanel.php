<?php

namespace Goldfinch\Dashcore\Panels;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\Admin\LeftAndMain;
use Goldfinch\Dashboard\DashboardPanel;
use Goldfinch\Dashcore\Services\DashService;

class ServerInfoPanel extends DashboardPanel
{
    protected $panel_header = 'Info desk';

    protected $panel_cols = 8;

    protected $panel_position = 3;

    protected $panel_extra_class = 'dashcard dashcard--sitetree';

    public function process(): array
    {
        $list = [
            'php' => ['label' => 'PHP'],
            'mysql' => ['label' => 'MySQL'],
            'other' => ['label' => 'Server'],
            'site' => ['label' => 'Site'],
        ];

        $phpList = ArrayList::create();
        $mysqlList = ArrayList::create();
        $siteList = ArrayList::create();
        $otherList = ArrayList::create();

        $phpList->push(ArrayData::create([
            'label' => 'Version',
            'value' => phpversion(),
        ]));

        $mysqlList->push(ArrayData::create([
            'label' => 'Version',
            'value' => DB::get_conn()->getVersion(),
        ]));

        $count = DB::query(
            'SELECT TABLE_SCHEMA AS DB_Name, count(TABLE_SCHEMA) AS Total_Tables, SUM(TABLE_ROWS) AS Total_Tables_Row, ROUND(sum(data_length + index_length)/1024/1024) AS Total_DB_size, ROUND(sum( data_free )/ 1024 / 1024) AS Total_Free_Space FROM information_schema.TABLES WHERE TABLE_SCHEMA = \'' .
                ss_env('SS_DATABASE_NAME') .
                '\' GROUP BY TABLE_SCHEMA;',
        );

        $mysqlList->push(ArrayData::create([
            'label' => 'Total tables row',
            'value' => current($count->column('Total_Tables')),
        ]));

        $mysqlList->push(ArrayData::create([
            'label' => 'Total tables',
            'value' => current($count->column('Total_Tables_Row')),
        ]));

        $mysqlList->push(ArrayData::create([
            'label' => 'DB size',
            'value' => current($count->column('Total_DB_size')) . 'M',
        ]));

        if (current($count->column('Total_Free_Space')) != 0) {
            $mysqlList->push(ArrayData::create([
                'label' => 'Free Space',
                'value' => current($count->column('Total_Free_Space')) . 'M',
            ]));
        }

        $phpList->push(ArrayData::create([
            'label' => 'Timezone',
            'value' => date_default_timezone_get(),
        ]));

        $siteList->push(ArrayData::create([
            'label' => 'SS version',
            'value' => LeftAndMain::singleton()->CMSVersionNumber(),
        ]));

        $siteList->push(ArrayData::create([
            'label' => 'Environment',
            'value' => Environment::getEnv('SS_ENVIRONMENT_TYPE'),
        ]));

        $otherList->push(ArrayData::create([
            'label' => 'Server IP',
            'value' => $_SERVER['SERVER_ADDR'],
        ]));

        $otherList->push(ArrayData::create([
            'label' => 'Main domain',
            'value' => Director::host(),
        ]));

        // ! causing errors in production
        // if (isset($_SERVER['SERVER_ADMIN'])) {
        //     $otherList->push(ArrayData::create([
        //         'label' => 'Server email',
        //         'value' => $_SERVER['SERVER_ADMIN'],
        //     ]));
        // }

        $siteList->push(ArrayData::create([
            'label' => 'Total assets size',
            'value' => DashService::getAssetsSize() . 'M',
        ]));

        $siteList->push(ArrayData::create([
            'label' => 'Vendor size',
            'value' => DashService::getVendorSize() . 'M',
        ]));

        $phpList->push(ArrayData::create([
            'label' => 'Allocated php memory',
            'value' => round(memory_get_usage() / 1000000, 2) . 'M',
        ]));

        $siteList->push(ArrayData::create([
            'label' => 'Total disk',
            'value' => round(disk_total_space('/') / 1000000 / 1000, 2) . 'G',
        ]));

        $dns = @dns_get_record(Director::host());

        if (!empty($dns)) {
            $dnsString = '';

            sort($dns);

            foreach ($dns as $d) {
                if ($d['type'] == 'NS') {
                    $dnsString .= $d['target'] . ', ';
                }
            }

            $dnsString = substr($dnsString, 0, -2);

            $otherList->push(ArrayData::create([
                'label' => 'DNS Server',
                'value' => $dnsString,
            ]));
        }

        $phpList->push(ArrayData::create([
            'label' => 'Memory limit',
            'value' => ini_get('memory_limit'),
        ]));

        $phpList->push(ArrayData::create([
            'label' => 'Upload max filesize',
            'value' => ini_get('upload_max_filesize'),
        ]));

        $phpList->push(ArrayData::create([
            'label' => 'Max execution time',
            'value' => ini_get('max_execution_time') . 's',
        ]));

        $phpList->push(ArrayData::create([
            'label' => 'Post max size',
            'value' => ini_get('post_max_size'),
        ]));

        if (ini_get('display_errors')) {
            $phpList->push(ArrayData::create([
                'label' => 'Display errors',
                'value' => ini_get('display_errors'),
            ]));
        }

        if (ini_get('expose_php')) {
            $phpList->push(ArrayData::create([
                'label' => 'Expose php',
                'value' => ini_get('expose_php'),
            ]));
        }

        $list['php']['list'] = $phpList;
        $list['mysql']['list'] = $mysqlList;
        $list['site']['list'] = $siteList;
        $list['other']['list'] = $otherList;

        return ['list' => ArrayList::create($list)];
    }
}
