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

    /** The role the platform health plugin is configured with (config/packages/tmonier_platform_health.yaml): read-only page. */
    public const PLATFORM_HEALTH_VIEW = 'ROLE_PLATFORM_HEALTH_VIEW';

    /**
     * @return list<string> every role the demo_admin_role fixture grants to the public back-office accounts
     */
    public static function grantedToPublicAccounts(): array
    {
        return [self::NAME, self::PLATFORM_HEALTH_VIEW];
    }

    private function __construct()
    {
    }
}
