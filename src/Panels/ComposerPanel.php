<?php

namespace Goldfinch\Dashcore\Panels;

use SilverStripe\ORM\ArrayList;
use Goldfinch\Dashboard\DashboardPanel;
use Goldfinch\Dashcore\Services\DashService;

class ComposerPanel extends DashboardPanel
{
    protected $panel_header = 'Composer';

    protected $panel_cols = 12;

    protected $panel_position = 7;

    protected $panel_extra_class = '';

    protected $panel_dev = true;

    public function process(): array
    {
        // Composer installed packages
        $installedPackages = DashService::getComposerInstalledPackageList();

        return ['list' => ArrayList::create($installedPackages)];
    }
}
