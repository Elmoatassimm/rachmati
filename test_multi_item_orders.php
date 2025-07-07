<?php

/**
 * Test script for multi-item order functionality
 * This script demonstrates how to create and test multi-item orders via CURL
 */

echo "=== Multi-Item Order API Test Script ===\n\n";

// Test data for single item order (backward compatibility)
$singleItemOrder = [
    'rachma_id' => 1,
    'quantity' => 2,
    'payment_method' => 'ccp',
    // payment_proof file would be uploaded separately
];

// Test data for multi-item order
$multiItemOrder = [
    'items' => [
        [
            'rachma_id' => 1,
            'quantity' => 2
        ],
        [
            'rachma_id' => 2,
            'quantity' => 1
        ],
        [
            'rachma_id' => 3,
            'quantity' => 3
        ]
    ],
    'payment_method' => 'baridi_mob',
    // payment_proof file would be uploaded separately
];

echo "1. Single Item Order (Backward Compatibility):\n";
echo "POST /api/orders\n";
echo "Data: " . json_encode($singleItemOrder, JSON_PRETTY_PRINT) . "\n\n";

echo "CURL Command:\n";
echo 'curl -X POST http://127.0.0.1:8000/api/orders \\' . "\n";
echo '  -H "Authorization: Bearer YOUR_JWT_TOKEN" \\' . "\n";
echo '  -H "Accept: application/json" \\' . "\n";
echo '  -F "rachma_id=1" \\' . "\n";
echo '  -F "quantity=2" \\' . "\n";
echo '  -F "payment_method=ccp" \\' . "\n";
echo '  -F "payment_proof=@/path/to/payment_proof.jpg"' . "\n\n";

echo "2. Multi-Item Order:\n";
echo "POST /api/orders\n";
echo "Data: " . json_encode($multiItemOrder, JSON_PRETTY_PRINT) . "\n\n";

echo "CURL Command:\n";
echo 'curl -X POST http://127.0.0.1:8000/api/orders \\' . "\n";
echo '  -H "Authorization: Bearer YOUR_JWT_TOKEN" \\' . "\n";
echo '  -H "Accept: application/json" \\' . "\n";
echo '  -F "items[0][rachma_id]=1" \\' . "\n";
echo '  -F "items[0][quantity]=2" \\' . "\n";
echo '  -F "items[1][rachma_id]=2" \\' . "\n";
echo '  -F "items[1][quantity]=1" \\' . "\n";
echo '  -F "items[2][rachma_id]=3" \\' . "\n";
echo '  -F "items[2][quantity]=3" \\' . "\n";
echo '  -F "payment_method=baridi_mob" \\' . "\n";
echo '  -F "payment_proof=@/path/to/payment_proof.jpg"' . "\n\n";

echo "3. Get Order Details (includes order items):\n";
echo 'curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \\' . "\n";
echo '  -H "Accept: application/json" \\' . "\n";
echo '  http://127.0.0.1:8000/api/orders/{order_id}' . "\n\n";

echo "4. Get My Orders (includes order items):\n";
echo 'curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \\' . "\n";
echo '  -H "Accept: application/json" \\' . "\n";
echo '  http://127.0.0.1:8000/api/my-orders' . "\n\n";

echo "=== Expected Response Structure ===\n\n";

$expectedResponse = [
    'success' => true,
    'message' => 'Order created successfully',
    'data' => [
        'id' => 123,
        'client_id' => 1,
        'rachma_id' => null, // null for multi-item orders
        'amount' => 15000.00, // Total of all items
        'payment_method' => 'baridi_mob',
        'status' => 'pending',
        'created_at' => '2025-07-07T10:00:00.000000Z',
        'order_items' => [
            [
                'id' => 1,
                'rachma_id' => 1,
                'quantity' => 2,
                'unit_price' => 5000.00,
                'total_price' => 10000.00,
                'rachma' => [
                    'id' => 1,
                    'title_ar' => 'رشمة جميلة',
                    'price' => 5000.00,
                    'designer' => [
                        'user' => [
                            'name' => 'أحمد المصمم'
                        ]
                    ]
                ]
            ],
            [
                'id' => 2,
                'rachma_id' => 2,
                'quantity' => 1,
                'unit_price' => 3000.00,
                'total_price' => 3000.00,
                'rachma' => [
                    'id' => 2,
                    'title_ar' => 'رشمة أخرى',
                    'price' => 3000.00
                ]
            ]
        ]
    ]
];

echo json_encode($expectedResponse, JSON_PRETTY_PRINT) . "\n\n";

echo "=== Database Structure ===\n\n";
echo "Orders Table:\n";
echo "- id (primary key)\n";
echo "- client_id (foreign key to users)\n";
echo "- rachma_id (nullable, for backward compatibility)\n";
echo "- amount (total order amount)\n";
echo "- payment_method\n";
echo "- payment_proof_path\n";
echo "- status\n";
echo "- timestamps\n\n";

echo "Order Items Table (NEW):\n";
echo "- id (primary key)\n";
echo "- order_id (foreign key to orders)\n";
echo "- rachma_id (foreign key to rachmat)\n";
echo "- quantity\n";
echo "- unit_price (price at time of order)\n";
echo "- total_price (quantity * unit_price)\n";
echo "- timestamps\n\n";

echo "=== Features Implemented ===\n\n";
echo "✅ Backward compatibility with single-item orders\n";
echo "✅ Support for multi-item orders\n";
echo "✅ Automatic total calculation\n";
echo "✅ Order items with quantities\n";
echo "✅ Price preservation at order time\n";
echo "✅ Updated API endpoints\n";
echo "✅ Updated Admin UI\n";
echo "✅ Updated Designer UI\n";
echo "✅ Database migrations\n";
echo "✅ TypeScript type definitions\n\n";

echo "=== Testing Instructions ===\n\n";
echo "1. Run migrations: php artisan migrate\n";
echo "2. Seed some test data\n";
echo "3. Get JWT token by logging in\n";
echo "4. Test single-item order creation\n";
echo "5. Test multi-item order creation\n";
echo "6. Verify admin and designer UIs show multiple items\n";
echo "7. Test order completion workflow\n\n";

?>
