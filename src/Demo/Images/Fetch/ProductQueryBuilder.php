<?php

declare(strict_types=1);

namespace App\Demo\Images\Fetch;

/**
 * The English search terms of a product: the type of article, without brand, dimensions or grade, which an image bank
 * does not know. First term = the article, second = its category (used when the article has no CC0 image).
 */
final class ProductQueryBuilder
{
    /** @var array<string, string> category (taxon code) => search terms */
    private const CATEGORIES = [
        'protection-tete' => 'safety helmet',
        'protection-mains' => 'work gloves',
        'chaussures-securite' => 'safety boots',
        'protection-corps' => 'workwear',
        'emballage' => 'packaging',
        'signaletique' => 'safety sign',
        'papeterie' => 'office supplies',
        'visserie' => 'screws and bolts',
        'outillage' => 'hand tools',
        'fixations' => 'wall anchors',
    ];

    /** @var list<array{string, string}> first match wins: [pattern on the lower-case name, search terms] */
    private const RULES = [
        ['/tape measure/', 'tape measure'], ['/bump cap/', 'bump cap'], ['/forestry|arborist|chainsaw helmet/', 'forestry helmet'], ['/height work helmet/', 'climbing helmet'],
        ['/hard hat/', 'hard hat'],
        ['/welding.*gloves|gloves.*welding/', 'welding gloves'], ['/nitrile|latex|vinyl/', 'nitrile gloves'], ['/leather|pigskin/', 'leather work gloves'],
        ['/chemical.*gloves|pvc.*gloves/', 'rubber gloves'], ['/winter|cryo/', 'winter work gloves'], ['/gloves/', 'work gloves'],
        ['/wellington|rubber boot|waders/', 'rubber boots'], ['/boot/', 'work boots'], ['/clog|mule/', 'work clogs'], ['/shoe/', 'safety shoes'],
        ['/high-visibility vest/', 'high visibility vest'], ['/jacket|fleece|softshell/', 'work jacket'], ['/coverall/', 'coverall'],
        ['/apron/', 'work apron'], ['/trousers|rain set/', 'work trousers'],
        ['/bubble wrap/', 'bubble wrap'], ['/bubble-lined|envelope/', 'padded envelope'], ['/cardboard|carton/', 'cardboard box'],
        ['/pallet/', 'wooden pallet'], ['/kraft cushioning/', 'kraft paper'], ['/stretch film/', 'stretch film'], ['/strapping/', 'plastic strapping'],
        ['/zip bags/', 'zip lock bag'], ['/air cushion/', 'air cushion packaging'], ['/dispenser/', 'tape dispenser'],
        ['/hazard tape|floor marking tape/', 'hazard tape'], ['/packing tape|security tape|tape/', 'packing tape'],
        ['/logistics labels|label booklet|hazard labels/', 'warning label'], ['/foam sheets/', 'foam sheet'],
        ['/high voltage/', 'high voltage warning sign'], ['/emergency exit/', 'emergency exit sign'], ['/fire extinguisher/', 'fire extinguisher sign'],
        ['/first aid/', 'first aid sign'], ['/traffic cone/', 'traffic cone'], ['/stop sign/', 'stop sign'], ['/construction barrier/', 'road barrier'],
        ['/ppe/', 'safety equipment sign'], ['/shelf label/', 'shelf label'], ['/label printer/', 'label printer'], ['/customisable sign/', 'blank sign'],
        ['/paper ream/', 'paper ream'], ['/ballpoint/', 'ballpoint pens'], ['/markers/', 'permanent markers'], ['/ring binders/', 'ring binder'],
        ['/l-folders/', 'document folder'], ['/sticky notes/', 'sticky notes'], ['/paper clips/', 'paper clips'], ['/stapler/', 'stapler'],
        ['/staples/', 'staples'], ['/scissors/', 'scissors'], ['/correction tape/', 'correction tape'], ['/punch/', 'hole punch'],
        ['/organiser/', 'desk organizer'], ['/highlighters/', 'highlighter pens'],
        ['/threaded rod|suspension rod/', 'threaded rod'], ['/self-tapping/', 'self tapping screw'], ['/wood screw/', 'wood screw'],
        ['/socket head|flat head hex socket|countersunk/', 'socket head screw'], ['/hex nut|cap nut/', 'hex nut'], ['/washer/', 'metal washer'],
        ['/carriage bolt/', 'carriage bolt'], ['/bolt/', 'hex bolt'],
        ['/adjustable wrench/', 'adjustable wrench'], ['/torque wrench/', 'torque wrench'], ['/wrench/', 'spanner wrench'], ['/screwdriver set/', 'screwdriver set'],
        ['/screwdriver/', 'screwdriver'], ['/pliers/', 'pliers'], ['/hammer/', 'hammer'], ['/spirit level/', 'spirit level'],
        ['/hacksaw/', 'hacksaw'], ['/chisel/', 'cold chisel'], ['/file /', 'metal file tool'], ['/allen key/', 'allen keys'], ['/socket set/', 'socket wrench set'],
        ['/caliper/', 'vernier caliper'], ['/carpenter square/', 'carpenter square'],
        ['/wall plug/', 'wall plug'], ['/chemical anchor/', 'chemical anchor'], ['/anchor/', 'expansion anchor'], ['/bracket/', 'metal bracket'],
        ['/hose clamp/', 'hose clamp'], ['/rail/', 'metal rail'], ['/cable tie/', 'cable ties'], ['/strut channel/', 'strut channel'],
        ['/base plate|fixing plate/', 'metal plate'], ['/cable clip/', 'cable clip'],
    ];

    /**
     * @param array<string, string> $product
     *
     * @return array{string, string} [article terms, category terms]
     */
    public function __invoke(array $product): array
    {
        $category = self::CATEGORIES[$product['taxon']] ?? 'industrial supplies';
        $name = \strtolower($product['name_en']) . ' ';
        foreach (self::RULES as [$pattern, $terms]) {
            if (\preg_match($pattern, $name) === 1) {
                return [$terms, $category];
            }
        }

        return [$category, $category];
    }
}
