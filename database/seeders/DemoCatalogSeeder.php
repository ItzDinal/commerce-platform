<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoCatalogSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ---------------------------------------------------------
        // Categories
        // ---------------------------------------------------------

        $categories = [
            'Sarees',
            'Dresses',
            'Tops',
            'Bottoms',
            'Accessories',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                [
                    'slug' => str()->slug($name),
                ],
                [
                    'name' => $name,
                ]
            );
        }

        // ---------------------------------------------------------
        // Product Attributes
        // ---------------------------------------------------------

        $attributes = [
            'color' => [
                'name' => 'Color',
                'values' => [
                    'Black',
                    'White',
                    'Red',
                    'Blue',
                    'Green',
                ],
            ],

            'size' => [
                'name' => 'Size',
                'values' => [
                    'XS',
                    'S',
                    'M',
                    'L',
                    'XL',
                ],
            ],

            'fabric' => [
                'name' => 'Fabric',
                'values' => [
                    'Cotton',
                    'Silk',
                    'Linen',
                    'Chiffon',
                ],
            ],
        ];

        foreach ($attributes as $slug => $data) {
            $attribute = ProductAttribute::firstOrCreate(
                [
                    'slug' => $slug,
                ],
                [
                    'name' => $data['name'],
                    'type' => 'select',
                ]
            );

            foreach ($data['values'] as $value) {
                $attribute->values()->firstOrCreate(
                    [
                        'slug' => str()->slug($value),
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }

        // ---------------------------------------------------------
        // Products
        // ---------------------------------------------------------

        $products = [
            [
                'name' => 'Classic Silk Saree',
                'category' => 'sarees',
                'description' => 'A timeless silk saree suitable for festive and formal occasions.',
                'short_description' => 'Elegant classic silk saree.',
                'status' => 'active',
            ],
            [
                'name' => 'Everyday Cotton Saree',
                'category' => 'sarees',
                'description' => 'A comfortable cotton saree designed for everyday wear.',
                'short_description' => 'Comfortable everyday cotton saree.',
                'status' => 'active',
            ],
            [
                'name' => 'Floral Summer Dress',
                'category' => 'dresses',
                'description' => 'A lightweight floral dress perfect for warm days.',
                'short_description' => 'Lightweight floral summer dress.',
                'status' => 'active',
            ],
            [
                'name' => 'Classic Linen Top',
                'category' => 'tops',
                'description' => 'A breathable linen top with a clean, versatile design.',
                'short_description' => 'Breathable everyday linen top.',
                'status' => 'active',
            ],
            [
                'name' => 'Relaxed Cotton Trousers',
                'category' => 'bottoms',
                'description' => 'Relaxed-fit cotton trousers designed for everyday comfort.',
                'short_description' => 'Comfortable relaxed-fit trousers.',
                'status' => 'active',
            ],
            [
                'name' => 'Silk Evening Scarf',
                'category' => 'accessories',
                'description' => 'A lightweight silk scarf designed to complement formal outfits.',
                'short_description' => 'Elegant silk evening scarf.',
                'status' => 'active',
            ],
        ];

        foreach ($products as $data) {
            $category = Category::where(
                'slug',
                $data['category']
            )->firstOrFail();

            $product = Product::firstOrCreate(
                [
                    'slug' => str()->slug($data['name']),
                ],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'short_description' => $data['short_description'],
                    'status' => $data['status'],
                ]
            );

            $product->categories()->syncWithoutDetaching([
                $category->id,
            ]);
        }

        // ---------------------------------------------------------
        // Product Variants
        // ---------------------------------------------------------

        $variantDefinitions = [
            [
                'product' => 'classic-silk-saree',
                'variants' => [
                    [
                        'sku' => 'CSS-BLK-M',
                        'price' => 129.00,
                        'compare_at_price' => 149.00,
                        'attributes' => [
                            'color' => 'black',
                            'size' => 'm',
                            'fabric' => 'silk',
                        ],
                        'quantity' => 20,
                    ],
                    [
                        'sku' => 'CSS-BLK-L',
                        'price' => 129.00,
                        'compare_at_price' => 149.00,
                        'attributes' => [
                            'color' => 'black',
                            'size' => 'l',
                            'fabric' => 'silk',
                        ],
                        'quantity' => 15,
                    ],
                    [
                        'sku' => 'CSS-RED-M',
                        'price' => 139.00,
                        'compare_at_price' => 159.00,
                        'attributes' => [
                            'color' => 'red',
                            'size' => 'm',
                            'fabric' => 'silk',
                        ],
                        'quantity' => 12,
                    ],
                    [
                        'sku' => 'CSS-RED-L',
                        'price' => 139.00,
                        'compare_at_price' => 159.00,
                        'attributes' => [
                            'color' => 'red',
                            'size' => 'l',
                            'fabric' => 'silk',
                        ],
                        'quantity' => 10,
                    ],
                ],
            ],

            [
                'product' => 'everyday-cotton-saree',
                'variants' => [
                    [
                        'sku' => 'ECS-WHT-M',
                        'price' => 79.00,
                        'compare_at_price' => 89.00,
                        'attributes' => [
                            'color' => 'white',
                            'size' => 'm',
                            'fabric' => 'cotton',
                        ],
                        'quantity' => 25,
                    ],
                    [
                        'sku' => 'ECS-BLU-L',
                        'price' => 79.00,
                        'compare_at_price' => 89.00,
                        'attributes' => [
                            'color' => 'blue',
                            'size' => 'l',
                            'fabric' => 'cotton',
                        ],
                        'quantity' => 18,
                    ],
                ],
            ],
        ];

        foreach ($variantDefinitions as $productDefinition) {
            $product = Product::where(
                'slug',
                $productDefinition['product']
            )->firstOrFail();

            foreach ($productDefinition['variants'] as $variantDefinition) {
                $variant = ProductVariant::firstOrCreate(
                    [
                        'sku' => $variantDefinition['sku'],
                    ],
                    [
                        'product_id' => $product->id,
                        'price' => $variantDefinition['price'],
                        'compare_at_price' => $variantDefinition['compare_at_price'],
                        'status' => 'active',
                    ]
                );

                // -------------------------------------------------
                // Variant Attribute Values
                // -------------------------------------------------

                foreach ($variantDefinition['attributes'] as $attributeSlug => $valueSlug) {
                    $attributeValue = ProductAttributeValue::whereHas(
                        'attribute',
                        fn ($query) => $query->where('slug', $attributeSlug)
                    )
                        ->where('slug', $valueSlug)
                        ->firstOrFail();

                    $variant->attributeValues()->syncWithoutDetaching([
                        $attributeValue->id,
                    ]);
                }

                // -------------------------------------------------
                // Inventory
                // -------------------------------------------------

                Inventory::firstOrCreate(
                    [
                        'product_variant_id' => $variant->id,
                    ],
                    [
                        'quantity' => $variantDefinition['quantity'],
                        'reserved' => 0,
                    ]
                );
            }
        }
    }
}
