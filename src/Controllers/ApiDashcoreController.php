<?php

namespace Goldfinch\Dashcore\Controllers;

use SilverStripe\Security\Security;
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
    ];

    protected function init()
    {
        parent::init();

        // ..
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
