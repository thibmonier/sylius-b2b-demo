# CSV Catalogue Schema

CSV files versioned under `fixtures/csv/` feed the `demo_products_csv` fixture
(class `App\Demo\DataFixtures\ProductCsvFixture`).

## Files

| File | Products | Taxons covered |
|------|----------|----------------|
| `epi.csv` | 80 | protection-tete, protection-mains, chaussures-securite, protection-corps |
| `quincaillerie.csv` | 80 | visserie, outillage, fixations |
| `fournitures.csv` | 50 | emballage, signaletique, papeterie |

## Column reference

| Column | Required | Type | Description |
|--------|----------|------|-------------|
| `sku` | YES | string | Unique product code — also used as Sylius product code and variant code. Must be unique within each file. |
| `mpn` | no | string | Manufacturer Part Number |
| `ean` | no | string (13 digits) | EAN-13 barcode |
| `name_fr` | YES | string | Product name in French |
| `name_en` | YES | string | Product name in English |
| `description_fr` | no | string | Full description in French |
| `description_en` | no | string | Full description in English |
| `taxon` | YES | string | **Leaf** taxon code (see Taxon tree below) |
| `attributes_json` | no | JSON string | Key→value map of attribute codes to values. Keys must match existing `product_attribute` codes. |
| `weight_kg` | no | float | Weight in kilograms |
| `width_cm` | no | float | Width in centimetres |
| `height_cm` | no | float | Height in centimetres |
| `depth_cm` | no | float | Depth in centimetres |
| `price_eur_ht` | YES | float | Price in EUR excluding tax. Stored as cents in Sylius (×100). |

## Attribute codes

| Code | FR label | EN label | Used in |
|------|----------|----------|---------|
| `epi_norm` | Norme de protection | Protection standard | EPI |
| `packaging_unit` | Unité de conditionnement (le poids, les dimensions et le prix de la ligne sont ceux de ce conditionnement : « boîte de 100 » = une boîte) | Packaging unit (the row's weight, size and price are those of this packaging: "box of 100" = one box) | All |
| `material` | Matière principale | Main material | All |
| `certifications` | Certifications | Certifications | EPI |

## Taxon tree

```
catalogue
├── epi
│   ├── protection-tete
│   ├── protection-mains
│   ├── chaussures-securite
│   └── protection-corps
├── quincaillerie
│   ├── visserie
│   ├── outillage
│   └── fixations
└── fournitures-industrielles
    ├── emballage
    ├── signaletique
    └── papeterie
```

## Adding a product

1. Pick the correct CSV file for the category.
2. Choose a unique SKU following the naming convention: `<PREFIX>-<TYPE>-NNN`
   (e.g. `EPI-CASK-021`, `VISS-031`).
3. Compute a valid EAN-13 using the project's precomputed series,
   or generate a fresh one with any online EAN-13 tool.
4. Add a row respecting the `attributes_json` format (JSON with doubled
   double-quotes when inside a CSV-quoted field).
5. Re-run the fixture suite: `bin/console sylius:fixtures:load --suite=demo`.

The fixture is **idempotent**: existing products are updated, not duplicated.
Within a single file, duplicate SKUs cause an immediate import failure with
an explicit error message and **no partial import**.

## Product images

Every product gets its image from `fixtures/images/` (WebP, CC0), linked by SKU through `fixtures/images/manifest.csv`
(`ProductImageAttacher`, called by the fixture; local files only, never a network call). A new product needs a manifest
row: see `docs/images-produits.md`.

## Validation at load

The loader reads the three files upfront and stops, before any product is created, with the file and the line
(`file.csv:12`) when: a row does not have as many cells as the header; `attributes_json` is not a JSON object of strings;
a cell is not valid UTF-8; a SKU is used twice, in one file or in two. A UTF-8 BOM and spaces around the header names are
ignored.
