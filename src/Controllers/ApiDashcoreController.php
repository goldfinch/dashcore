<?php

namespace Goldfinch\Dashcore\Controllers;

use Carbon\Carbon;
use SilverStripe\Control\Director;
use SilverStripe\Security\Security;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use Goldfinch\Dashcore\Services\DashService;

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
    ];

    protected function init()
    {
        parent::init();

        // ..
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
