<?php

namespace Goldfinch\Dashcore\Panels;

use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use Goldfinch\Dashboard\DashboardPanel;
use Goldfinch\Dashcore\Services\DashService;

class GitPanel extends DashboardPanel
{
    protected $panel_header = 'Git';

    protected $panel_cols = 12;

    protected $panel_position = 6;

    protected $panel_extra_class = '';

    protected $panel_dev = true;

    public function process(): array
    {
        /**
         * - last commit, ref, date, author
         * - bitbucket pipelines status (last commit, date, author)
         *
         * https://developer.atlassian.com/cloud/bitbucket/rest/intro/#authentication_old
         */

        // curl -X GET -u bitbucket_username:app_password "https://api.bitbucket.org/2.0/repositories/<workspace>/<repository>/pipelines/?status=PENDING&status=BUILDING&status=IN_PROGRESS"
        // PENDING,BUILDING,IN_PROGRESS

        // TODO: disable panel if .git does not exists

        if (DashService::getGitCurrentBranch()) {
            $commits = ArrayList::create(DashService::getGitCommits(10));
            $branches = DashService::getGitBranches();
            $mainbranch = DashService::getGitCurrentBranch();
        } else {
            $commits = [];
            $branches = [];
            $mainbranch = '-';
        }

        return [
            'commits' => $commits,
            'branches' => $branches,
            'mainbranch' => $mainbranch,
        ];
    }
}
