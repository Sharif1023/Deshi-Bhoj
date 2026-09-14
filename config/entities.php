<?php
return [
    'faqs' => ['label'=>'FAQs','fields'=>['title'=>'rich-inline','answer'=>'rich','sort_order'=>'number']],
    'products' => [
        'label' => 'Products',
        'fields' => [
            'name' => 'rich-inline',
            'slug' => 'slug',
            'category_id' => 'category',
            'description' => 'rich',
            'price' => 'money',
            'images' => 'images',
            'ingredients' => 'rich',
            'allergens' => 'rich',
            'dietary' => 'text?',
            'preparation_minutes' => 'number',
            'available' => 'checkbox',
            'featured' => 'checkbox',
            'badge' => 'text?',
            'sort_order' => 'number',
        ],
    ],
    'categories' => [
        'label' => 'Categories',
        'fields' => [
            'name' => 'rich-inline',
            'slug' => 'slug',
            'description' => 'rich',
            'image' => 'image',
            'sort_order' => 'number',
        ],
    ],
    'features' => [
        'label' => 'Features',
        'fields' => ['title' => 'rich-inline', 'description' => 'rich', 'sort_order' => 'number'],
    ],
    'gallery' => [
        'label' => 'Gallery',
        'fields' => ['title' => 'rich-inline', 'image' => 'image', 'sort_order' => 'number'],
    ],
    'reviews' => [
        'label' => 'Reviews',
        'fields' => [
            'name' => 'rich-inline',
            'comment' => 'rich',
            'rating' => 'rating',
            'published' => 'checkbox',
            'sort_order' => 'number',
        ],
    ],
];
