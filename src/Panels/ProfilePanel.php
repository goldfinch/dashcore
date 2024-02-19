<?php

namespace Goldfinch\Dashcore\Panels;

use SilverStripe\Security\Security;
use Goldfinch\Dashboard\DashboardPanel;

class ProfilePanel extends DashboardPanel
{
    protected $panel_header = 'Profile';

    protected $panel_cols = 12;

    protected $panel_position = 5;

    protected $panel_extra_class = '';

    public function process(): array
    {
        $user = Security::getCurrentUser();

        return [
            'email' => $user->Email,
            'name' => $user->getName(),
        ];
    }
}
