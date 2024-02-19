<?php

namespace Goldfinch\Dashcore\Panels;

use Carbon\Carbon;
use SilverStripe\ORM\ArrayList;
use SilverStripe\CMS\Model\SiteTree;
use Goldfinch\Dashboard\DashboardPanel;

class SiteTreePanel extends DashboardPanel
{
    protected $panel_header = 'Pages';

    protected $panel_cols = 6;

    protected $panel_position = 1;

    protected $panel_extra_class = 'dashcard dashcard--sitetree';

    protected $panel_actions = [
        [
            'title' => 'Add new page',
            'link' => '/admin/pages/add',
            'icon' => 'bi bi-plus-square-fill',
        ],
    ];

    public function process(): array
    {
        $list = [];

        foreach (
            SiteTree::get()
                ->sort('LastEdited', 'DESC')
                ->limit(5)
            as $item
        ) {
            $lastversion = $item->get_latest_version(
                $item->ClassName,
                $item->ID,
            );

            $list[] = [
                'icon' => $item->getIconClass(),
                'title' => $item->Title,
                'link' => $item->CMSEditLink(),
                'author' => $lastversion->Author()
                    ? $lastversion->Author()->getName()
                    : null,
                'updated_at' => Carbon::parse($item->LastEdited)->format(
                    'l, F jS Y, H:i',
                ),
                'updated_at_human' => Carbon::parse(
                    $item->LastEdited,
                )->diffForHumans(),
            ];
        }

        return ['list' => ArrayList::create($list)];
    }
}
