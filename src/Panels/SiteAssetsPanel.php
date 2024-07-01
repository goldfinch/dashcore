<?php

namespace Goldfinch\Dashcore\Panels;

use Carbon\Carbon;
use Goldfinch\Dashboard\DashboardPanel;
use ReflectionMethod;
use SilverStripe\AssetAdmin\Forms\FileFormFactory;
use SilverStripe\AssetAdmin\Forms\ImageFormFactory;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBText;

class SiteAssetsPanel extends DashboardPanel
{
    protected $panel_header = 'Assets';

    protected $panel_cols = 12;

    protected $panel_position = 5;

    protected $panel_extra_class = 'dashcard dashcard--assets';

    protected $panel_actions = [
        [
            'title' => 'New upload',
            'link' => '/admin/assets',
            'icon' => 'bi bi-plus-square-fill',
        ],
    ];

    public function process(): array
    {
        $list = [];

        $list = [];

        foreach (
            File::get()
                ->sort('LastEdited', 'DESC')
                ->limit(10) as $item
        ) {
            if (get_class($item) === Folder::class) {
                continue;
            }

            if (! $item || ! $item->exists()) {
                continue;
            }

            $lastversion = $item->get_latest_version(
                $item->ClassName,
                $item->ID,
            );
            // vendor/silverstripe/asset-admin/code/Controller/AssetAdmin.php

            if (method_exists($item, 'FitMax')) {
                $icon = $item->FitMax(352, 264);

                if ($icon) {
                    $icon = $icon->getURL();
                }

                $r = new ReflectionMethod(
                    ImageFormFactory::class,
                    'getSpecsMarkup',
                );
                $r->setAccessible(true);
                $fileSpecs = $r->invoke(new ImageFormFactory(), $item);
            } else {
                $icon = $item->getIcon();

                $r = new ReflectionMethod(
                    ImageFormFactory::class,
                    'getSpecsMarkup',
                );
                $r->setAccessible(true);
                $fileSpecs = $r->invoke(new FileFormFactory(), $item);
            }

            if (! $icon) {
                if ($item->getExtension() == 'svg') {
                    $icon = $item->getUrl();
                } else {
                    $icon =
                        'https://placehold.co/352x264/3b4960/FFF?font=open-sans&text=.'.
                        $item->getExtension();
                }
            }

            $title = DBText::create();
            $title->setValue($item->Title);

            $list[] = [
                'icon' => $icon,
                'specs' => DBHTMLText::create()->setValue($fileSpecs),
                'title' => $title->LimitCharacters(18),
                'full_title' => $item->Title,
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
