# Modèle de données

Les tables Sylius et celles des 21 plugins existent déjà (voir chaque plugin). Seules les **nouvelles** tables sont décrites ici. Convention : chaque plugin préfixe ses propres tables, sans modifier les entités du cœur.

```mermaid
erDiagram
    sylius_channel ||--o| tmonier_b2b_access_channel : "mode d'accès"
    sylius_customer ||--o| tmonier_b2b_access_customer : "statut d'inscription"
    sylius_order ||--o| tmonier_order_reference : "référence PO"
    sylius_channel ||--o| tmonier_order_reference_channel : "exigences"
    sylius_customer ||--o{ tmonier_shopping_list : "propriétaire"
    tmonier_company ||--o{ tmonier_shopping_list : "partage"
    tmonier_shopping_list ||--o{ tmonier_shopping_list_item : contient
    sylius_product_variant ||--o{ tmonier_shopping_list_item : référence
    tmonier_company ||--o{ tmonier_company_address : "carnet"
    sylius_address ||--|| tmonier_company_address : "adresse"

    tmonier_b2b_access_channel {
        int id PK
        int channel_id FK "unique, CASCADE"
        string mode "open | prices_hidden | closed"
        bool registration_approval
    }
    tmonier_b2b_access_customer {
        int id PK
        int customer_id FK "unique, CASCADE"
        string status "pending | approved | rejected"
        datetime requested_at
        datetime decided_at
        string decided_by
    }
    tmonier_order_reference {
        int id PK
        int order_id FK "unique, CASCADE"
        string buyer_reference "35, BT-10"
        string cost_center "35"
    }
    tmonier_order_reference_channel {
        int id PK
        int channel_id FK "unique, CASCADE"
        string reference_requirement "none | optional | required"
        string cost_center_requirement "none | optional | required"
    }
    tmonier_shopping_list {
        int id PK
        int owner_id FK "customer"
        int company_id FK "nullable"
        int channel_id FK
        string name
        datetime updated_at
    }
    tmonier_shopping_list_item {
        int id PK
        int list_id FK "CASCADE"
        int variant_id FK "CASCADE"
        int quantity
    }
    tmonier_company_address {
        int id PK
        int company_id FK "CASCADE"
        int address_id FK "unique, CASCADE"
        string label
        bool default_shipping
        bool default_billing
    }
    app_demo_webhook_receipt {
        int id PK
        string event_type
        string webhook_id
        bool signature_valid
        text payload
        datetime received_at
    }
```

- `shopping-list` dépend de l'ADR de US-023 (wishlist officielle ou plugin maison).
- `app_demo_webhook_receipt` appartient à la démo (préfixe `app_`) et est vidée à chaque remise à zéro.
- Migrations : Schema API (MySQL, MariaDB, PostgreSQL) pour les plugins ; migrations Doctrine de l'application pour `app_*`.
