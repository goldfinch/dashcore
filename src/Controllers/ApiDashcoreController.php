<?php

namespace Goldfinch\Dashcore\Controllers;

use Carbon\Carbon;
use ReflectionMethod;
use SilverStripe\ORM\DB;
use SilverStripe\Assets\File;
use SilverStripe\Dev\Deprecation;
use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Security\Security;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\HTTPRequest;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\ORM\Connect\MySQLDatabase;
use Goldfinch\Dashcore\Services\DashService;
use SilverStripe\AssetAdmin\Forms\FileFormFactory;
use SilverStripe\AssetAdmin\Forms\ImageFormFactory;

class ApiDashcoreController extends Controller
{
    private static $url_handlers = [
        'POST dev/tasks' => 'devTasks',
        'POST dev/build' => 'devBuild',
        'POST info/table' => 'infoTable',
        'POST search/page' => 'searchPage',
        'POST info/robot' => 'infoRobot',
        'POST info/sitemap' => 'infoSitemap',
        'POST info/git' => 'infoGit',
        'POST performance/info' => 'performanceInfo',
        'POST performance/run' => 'performanceRun',
        'POST bugtracker/info' => 'bugtrackerInfo',

        'POST page/publish' => 'pagePublish',
        'POST page/unpublish' => 'pageUnpublish',
        'POST page/archive' => 'pageArchive',

        'POST block/publish' => 'blockPublish',
        'POST block/unpublish' => 'blockUnpublish',
        'POST block/archive' => 'blockArchive',

        'POST info/user' => 'infoUser',
        'POST info/composer' => 'infoComposer',

        'POST info/sitetree' => 'infoSitetree',
        'POST info/elementalarea' => 'infoElementalarea',
        'POST info/siteassets' => 'infoSitassets',
        'POST info/server' => 'infoServer',
    ];

    private static $allowed_actions = [
        'devTasks',
        'infoTable',
        'searchPage',
        'infoRobot',
        'infoSitemap',
        'infoGit',
        'performanceInfo',
        'performanceRun',
        'bugtrackerInfo',
        'pagePublish',
        'pageUnpublish',
        'pageArchive',
        'blockPublish',
        'blockUnpublish',
        'blockArchive',

        'infoComposer',
        'infoUser',

        'infoSitetree',
        'infoElementalarea',
        'infoSitassets',
        'infoServer',
    ];

    protected function init()
    {
        parent::init();

        // ..
    }

    public function infoServer()
    {
        $list = [];

        $list[] = [
          'label' => 'PHP',
          'value' => phpversion(),
        ];

        $list[] = [
          'label' => 'MySQL',
          'value' => DB::get_conn()->getVersion(),
        ];

        $list[] = [
          'label' => 'Timezone',
          'value' => date_default_timezone_get(),
        ];

        $list[] = [
          'label' => 'SS',
          'value' => LeftAndMain::singleton()->CMSVersionNumber(),
        ];

        $list[] = [
          'label' => 'SS environment',
          'value' => Environment::getEnv('SS_ENVIRONMENT_TYPE'),
        ];

        $list[] = [
          'label' => 'Server IP',
          'value' => $_SERVER['SERVER_ADDR'],
        ];

        $list[] = [
          'label' => 'Main domain',
          'value' => Director::host(),
        ];

        $list[] = [
          'label' => 'Server email',
          'value' => $_SERVER['SERVER_ADMIN'],
        ];

        $list[] = [
          'label' => 'Total assets size',
          'value' => DashService::getAssetsSize() . 'M',
        ];

        $list[] = [
          'label' => 'Allocated php memory',
          'value' => round(memory_get_usage() / 1000000, 2) . 'M',
        ];

        $list[] = [
          'label' => 'Total disk',
          'value' => round((disk_total_space('/') / 1000000) / 1000, 2) . 'G',
        ];

        $dns = dns_get_record(Director::host());

        if (!empty($dns))
        {
            $dnsString = '';

            sort($dns);

            foreach ($dns as $d)
            {
                if ($d['type'] == 'NS')
                {
                    $dnsString .= $d['target'] . ', ';
                }
            }

            $dnsString = substr($dnsString, 0, -2);

            $list[] = [
              'label' => 'DNS Server',
              'value' => $dnsString,
            ];
        }

        $list[] = [
          'label' => 'Memory limit',
          'value' => ini_get('memory_limit'),
        ];

        $list[] = [
          'label' => 'Upload max filesize',
          'value' => ini_get('upload_max_filesize'),
        ];

        $list[] = [
          'label' => 'Max execution time',
          'value' => ini_get('max_execution_time') . 's',
        ];

        $list[] = [
          'label' => 'Post max size',
          'value' => ini_get('post_max_size'),
        ];

        if (ini_get('display_errors'))
        {
            $list[] = [
              'label' => 'Display errors',
              'value' => ini_get('display_errors'),
            ];
        }

        if (ini_get('expose_php'))
        {
            $list[] = [
              'label' => 'Expose php',
              'value' => ini_get('expose_php'),
            ];
        }

        $data = [
          'list' => $list,
          'add_link' => '',
        ];

        return json_encode($data);
    }

    public function infoSitassets()
    {
        $list = [];

        foreach(File::get()->sort('LastEdited', 'DESC')->limit(5) as $item)
        {
            if (!$item || !$item->exists()) {
                return null;
            }

            $lastversion = $item->get_latest_version($item->ClassName, $item->ID);
            // vendor/silverstripe/asset-admin/code/Controller/AssetAdmin.php

            if (method_exists($item, 'FitMax'))
            {
                $icon = $item->FitMax(352, 264)->getURL();

                $r = new ReflectionMethod(ImageFormFactory::class, 'getSpecsMarkup');
                $r->setAccessible(true);
                $fileSpecs = $r->invoke(new ImageFormFactory(), $item);
            }
            else
            {
                $icon = $item->getIcon();

                $r = new ReflectionMethod(ImageFormFactory::class, 'getSpecsMarkup');
                $r->setAccessible(true);
                $fileSpecs = $r->invoke(new FileFormFactory(), $item);
            }

            $list[] = [
              'icon' => $icon,
              'specs' => $fileSpecs,
              'title' => $item->Title,
              'link' => $item->CMSEditLink(),
              'author' => $lastversion->Author() ? $lastversion->Author()->getName() : null,
              'updated_at' => Carbon::parse($item->LastEdited)->format('l, F jS Y, H:i'),
              'updated_at_human' => Carbon::parse($item->LastEdited)->diffForHumans(),
            ];
        }

        $data = [
          'list' => $list,
          'add_link' => '',
        ];

        return json_encode($data);
    }

    public function infoElementalarea()
    {
        $list = [];

        foreach(BaseElement::get()->sort('LastEdited', 'DESC')->limit(5) as $item)
        {
            $lastversion = $item->get_latest_version($item->ClassName, $item->ID);

            $list[] = [
              'icon' => trim($item->getIcon()->RAW()),
              'title' => $item->Title,
              'link' => $item->CMSEditLink(),
              'author' => $lastversion->Author() ? $lastversion->Author()->getName() : null,
              'updated_at' => Carbon::parse($item->LastEdited)->format('l, F jS Y, H:i'),
              'updated_at_human' => Carbon::parse($item->LastEdited)->diffForHumans(),
            ];
        }

        $data = [
          'list' => $list,
          'add_link' => '',
        ];

        return json_encode($data);
    }

    public function infoSitetree()
    {
        $list = [];

        foreach(SiteTree::get()->sort('LastEdited', 'DESC')->limit(5) as $item)
        {
            $lastversion = $item->get_latest_version($item->ClassName, $item->ID);

            $list[] = [
              'icon' => $item->getIconClass(),
              'title' => $item->Title,
              'link' => $item->CMSEditLink(),
              'author' => $lastversion->Author() ? $lastversion->Author()->getName() : null,
              'updated_at' => Carbon::parse($item->LastEdited)->format('l, F jS Y, H:i'),
              'updated_at_human' => Carbon::parse($item->LastEdited)->diffForHumans(),
            ];
        }

        $data = [
          'list' => $list,
          'add_link' => '',
        ];

        return json_encode($data);
    }

    public function infoUser()
    {
        $user = Security::getCurrentUser();

        $data = [
            'email' => $user->Email,
            'name' => $user->getName(),
        ];

        return json_encode($data);
    }

    public function devTasks()
    {
        //
    }

    public function infoComposer()
    {
        // 2) Composer installed packages
        $installedPackages = DashService::getComposerInstalledPackageList();

        return json_encode($installedPackages);
    }

    public function infoTable()
    {
        // 1) Assets folder size
        $assetsSize = DashService::getAssetsSize();

        // 3) Server data
        $serverData = DashService::getServerData();

        // 4) SilverStripe version
        $ss = DashService::getSilverStripeVersion();

        // 5) Mysql Size / amount of tables
        // - TODO

        // ---

        $data = [
          'assetsSize' => $assetsSize,

          'server' => $serverData,

          'ss' => $ss,
        ];

        return json_encode($data);
    }

    public function searchPage()
    {
        /**
         * Search in sitemap
         */
    }

    public function infoRobot()
    {
        /**
         * - exists?
         * - current page affected (blocked)?
         * - source of file
         */
    }

    public function infoSitemap()
    {
        /**
         * - exists?
         * - link to sitemap
         * - current page included?
         * - sitemap tree with all details, search, links
         */
    }

    public function infoGit()
    {
        /**
         * - last commit, ref, date, author
         * - bitbucket pipelines status (last commit, date, author)
         *
         * https://developer.atlassian.com/cloud/bitbucket/rest/intro/#authentication_old
         */

        // curl -X GET -u bitbucket_username:app_password "https://api.bitbucket.org/2.0/repositories/<workspace>/<repository>/pipelines/?status=PENDING&status=BUILDING&status=IN_PROGRESS"
        // PENDING,BUILDING,IN_PROGRESS

        return json_encode([
          'commits' => DashService::getGitCommits(10),
          'branches' => DashService::getGitBranches(),
          'mainbranch' => DashService::getGitCurrentBranch(),
        ]);
    }

    public function performanceInfo()
    {
        /**
         * -
         */
    }

    public function performanceRun()
    {
        /**
         * -
         */
    }

    public function bugtrackerInfo()
    {
        /**
         * -
         */
    }

    public function pagePublish()
    {
        /**
         * -
         */
    }

    public function pageUnpublish()
    {
        /**
         * -
         */
    }

    public function pageArchive()
    {
        /**
         * -
         */
    }

    public function blockPublish()
    {
        /**
         * -
         */
    }

    public function blockUnpublish()
    {
        /**
         * -
         */
    }

    public function blockArchive()
    {
        /**
         * -
         */
    }
}
