<?php

declare(strict_types=1);

namespace App\Wishlist\Security;

/** Security attributes of the company lists (subject: CompanyWishlist). */
final class CompanyWishlistPermissions
{
    /** An active member of the owning company: read the list and add it to the cart. */
    public const VIEW = 'COMPANY_WISHLIST_VIEW';

    /** VIEW and (creator of the list or company admin): change the items. */
    public const EDIT = 'COMPANY_WISHLIST_EDIT';

    private function __construct()
    {
    }
}
