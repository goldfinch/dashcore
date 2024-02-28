<?php

namespace Goldfinch\Dashcore\Panels;

use Carbon\Carbon;
use SilverStripe\ORM\ArrayList;
use Goldfinch\Dashboard\DashboardPanel;
use SilverStripe\ORM\FieldType\DBHTMLText;
use DNADesign\Elemental\Models\BaseElement;

class ElementAreaPanel extends DashboardPanel
{
    protected $panel_header = 'Blocks (elementals)';

    protected $panel_cols = 6;

    protected $panel_position = 2;

    protected $panel_extra_class = 'dashcard dashcard--sitetree';

    public function process(): array
    {
        $list = [];

        if (class_exists(BaseElement::class)) {
            foreach (
                BaseElement::get()
                    ->sort('LastEdited', 'DESC')
                    ->limit(5)
                as $item
            ) {
                $lastversion = $item->get_latest_version(
                    $item->ClassName,
                    $item->ID,
                );

                $icon = DBHTMLText::create();
                $icon->setValue(trim($item->getIcon()->RAW()));

                $list[] = [
                    'icon' => $icon,
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
        }

        return ['list' => ArrayList::create($list)];
    }
}
