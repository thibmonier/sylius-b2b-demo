<?php

declare(strict_types=1);

namespace App\Demo\Admin;

/**
 * Role of the public back-office accounts of the demo (US-035). Every account of the
 * `demo.admin_users` catalogue (config/demo/accounts.yaml) carries it, see DemoAdminRoleFixture.
 */
final class DemoAdminRole
{
    public const NAME = 'ROLE_DEMO_ADMIN';

    private function __construct()
    {
    }
}
