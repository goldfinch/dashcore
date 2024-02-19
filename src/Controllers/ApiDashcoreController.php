<?php

namespace Goldfinch\Dashcore\Controllers;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Environment;
use SilverStripe\Security\Security;
use SilverStripe\Control\Controller;
use Goldfinch\Dashboard\DashboardPanel;
use Goldfinch\Dashboard\Configs\DashboardConfig;

class ApiDashcoreController extends Controller
{
    private static $url_handlers = [
        'POST fetch/panels' => 'fetchPanels',
    ];

    private static $allowed_actions = [
        'fetchPanels',
    ];

    public function fetchPanels()
    {
        $cfg = DashboardConfig::current_config();

        // TODO: this nesting loop-check needs to be done differently (for the sake of query optimization)
        foreach (ClassInfo::subclassesFor(DashboardPanel::class) as $panel) {
            if ($panel != DashboardPanel::class)
            {
                if (!$cfg->Panels()->Filter('ClassName', $panel)->exists()) {
                    $pl = new $panel;
                    $pl->newPanel();
                    $cfg->Panels()->add($pl);
                }
            }
        }

        $html = '';

        $user = Security::getCurrentUser();
        $defaultAdmin = $user->Email == Environment::getEnv('SS_DEFAULT_ADMIN_USERNAME');

        foreach ($cfg->Panels() as $panel) {

            if ($panel->isDev() && !$defaultAdmin) {
                continue;
            }

            $html .= $panel->run()->RAW();
        }

        $data = [
            'cards' => $html,
        ];

        return json_encode($data);
    }
}
