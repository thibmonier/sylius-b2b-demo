<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Wishlist\CompanyWishlist;
use App\Wishlist\CompanyWishlistCartAdder;
use App\Wishlist\CompanyWishlistRepository;
use App\Wishlist\Security\CompanyWishlistPermissions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Member\CurrentMemberResolverInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Security\CompanyPermissions;

/**
 * Shared purchase lists of the company (US-024). Under `/account`: a visitor is sent to the login
 * (Sylius access control). A list of another company answers 403 (voter); no company, 404.
 */
#[Route('/{_locale}/account/company-wishlists', requirements: ['_locale' => '[A-Za-z]{2,4}(_([A-Za-z]{4}|[0-9]{3}))?(_([A-Za-z]{2}|[0-9]{3}))?'])]
final class CompanyWishlistController extends AbstractController
{
    private const UUID = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';

    public function __construct(
        private readonly CurrentMemberResolverInterface $currentMemberResolver,
        private readonly CompanyWishlistRepository $repository,
        private readonly CompanyWishlistCartAdder $cartAdder,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('', name: 'app_shop_company_wishlist_index', methods: ['GET'])]
    public function index(): Response
    {
        $company = $this->currentMemberResolver->getMember()?->getCompany();
        if (null === $company) {
            throw $this->createNotFoundException('No company.');
        }
        $this->denyAccessUnlessGranted(CompanyPermissions::COMPANY_VIEW, $company);

        return $this->render('shop/wishlist/company/index.html.twig', [
            'company' => $company,
            'lists' => $this->repository->findByCompany($company),
        ]);
    }

    #[Route('/{id}', name: 'app_shop_company_wishlist_show', requirements: ['id' => self::UUID], methods: ['GET'])]
    public function show(string $id): Response
    {
        $list = $this->load($id, CompanyWishlistPermissions::VIEW);

        return $this->render('shop/wishlist/company/show.html.twig', [
            'list' => $list,
            'can_edit' => $this->isGranted(CompanyWishlistPermissions::EDIT, $list),
        ]);
    }

    #[Route('/{id}/add-to-cart', name: 'app_shop_company_wishlist_add_to_cart', requirements: ['id' => self::UUID], methods: ['POST'])]
    public function addToCart(Request $request, string $id): Response
    {
        $list = $this->load($id, CompanyWishlistPermissions::VIEW);
        $this->assertCsrf($request, $list);

        $all = $request->request->has('all');
        $selected = array_filter(array_map(static fn (mixed $id): int|false => filter_var($id, \FILTER_VALIDATE_INT), $request->request->all('items')));
        $items = [];
        foreach ($list->getWishlist()->getWishlistProducts() as $item) {
            if ($all || \in_array($item->getId(), $selected, true)) {
                $items[] = $item;
            }
        }

        if ([] === $items) {
            $this->addFlash('error', $this->translator->trans('app.company_wishlist.nothing_selected', [], 'wishlist'));

            return $this->redirectToShow($id);
        }

        $result = $this->cartAdder->add($items);
        if ($result['added'] > 0) {
            $this->addFlash('success', $this->translator->trans('app.company_wishlist.added_to_cart', ['%count%' => $result['added']], 'wishlist'));
        }
        if ($result['skipped'] > 0) {
            $this->addFlash('error', $this->translator->trans('app.company_wishlist.skipped', ['%count%' => $result['skipped']], 'wishlist'));
        }

        return $this->redirectToRoute('sylius_shop_cart_summary');
    }

    #[Route('/{id}/items/{itemId}/remove', name: 'app_shop_company_wishlist_remove_item', requirements: ['id' => self::UUID, 'itemId' => '\d+'], methods: ['POST'])]
    public function removeItem(Request $request, string $id, int $itemId): Response
    {
        $list = $this->load($id, CompanyWishlistPermissions::EDIT);
        $this->assertCsrf($request, $list);

        foreach ($list->getWishlist()->getWishlistProducts() as $item) {
            if ($item->getId() === $itemId) {
                $list->getWishlist()->removeProduct($item);
                $this->entityManager->flush();
                $this->addFlash('success', $this->translator->trans('app.company_wishlist.item_removed', [], 'wishlist'));
            }
        }

        return $this->redirectToShow($id);
    }

    private function load(string $id, string $permission): CompanyWishlist
    {
        $list = $this->repository->find($id);
        if (null === $list) {
            throw $this->createNotFoundException('List not found.');
        }
        $this->denyAccessUnlessGranted($permission, $list);

        return $list;
    }

    private function assertCsrf(Request $request, CompanyWishlist $list): void
    {
        if (!$this->isCsrfTokenValid('app_company_wishlist_' . $list->getId()->toRfc4122(), (string) $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }

    private function redirectToShow(string $id): RedirectResponse
    {
        return $this->redirectToRoute('app_shop_company_wishlist_show', ['id' => $id]);
    }
}
