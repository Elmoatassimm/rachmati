# Duplicate Purchase Validation API Documentation

## Overview

The duplicate purchase validation system prevents clients from purchasing the same rachma (digital embroidery pattern) multiple times. Since rachmat are digital products that can be downloaded unlimited times after purchase, customers should not be able to add the same rachma to their cart or complete checkout if they have already purchased it previously.

## How It Works

### Validation Process

1. **Pre-Order Validation**: Before creating an order, the system checks if the client has already purchased any of the rachmat in their current order
2. **Comprehensive Check**: The validation covers both legacy single-item orders and new multi-item orders
3. **Status-Based**: Only considers `completed` orders as valid purchases (ignores `pending` and `rejected` orders)
4. **Detailed Response**: Returns specific information about which rachmat are already owned

### Database Coverage

The validation system checks purchases across:
- **Legacy Orders**: Single-item orders stored in the `orders.rachma_id` field
- **Multi-Item Orders**: Orders with items stored in the `order_items` table
- **All Order Statuses**: Only `completed` orders are considered as valid purchases

## API Endpoint

### Create Order with Duplicate Validation

**POST** `/api/orders`

#### Request Body

```json
{
    "items": [
        {"rachma_id": 1},
        {"rachma_id": 2},
        {"rachma_id": 3}
    ],
    "payment_method": "ccp",
    "payment_proof": "file"
}
```

#### Success Response (201)

When no duplicates are found:

```json
{
    "success": true,
    "message": "تم إنشاء الطلب بنجاح",
    "data": {
        "id": 123,
        "client_id": 456,
        "amount": 3000,
        "status": "pending",
        "order_items": [
            {
                "id": 1,
                "rachma_id": 1,
                "price": 1000
            },
            {
                "id": 2,
                "rachma_id": 2,
                "price": 1000
            }
        ]
    }
}
```

#### Duplicate Purchase Error (400)

When duplicates are detected:

```json
{
    "success": false,
    "message": "لقد قمت بشراء بعض الرشمات من قبل",
    "error_type": "duplicate_purchase",
    "already_purchased": {
        "rachma_ids": [1, 3],
        "rachma_titles": ["رشمة الزهور العصرية", "رشمة الطيور الجميلة"],
        "message": "الرشمات التي تملكها بالفعل: رشمة الزهور العصرية، رشمة الطيور الجميلة"
    }
}
```

#### Validation Errors (422)

Standard validation errors with Arabic messages:

```json
{
    "success": false,
    "message": "أخطاء في التحقق من البيانات",
    "errors": {
        "items": ["يجب تحديد الرشمات المراد شراؤها."],
        "items.0.rachma_id": ["الرشمة المحددة غير موجودة."],
        "payment_method": ["طريقة الدفع مطلوبة."],
        "payment_proof": ["إثبات الدفع مطلوب."]
    }
}
```

## User Model Methods

### Check Single Rachma Purchase

```php
$user = auth()->user();
$hasPurchased = $user->hasPurchasedRachma(123);
```

### Check Multiple Rachmat Purchases

```php
$user = auth()->user();
$rachmaIds = [1, 2, 3, 4, 5];
$purchasedIds = $user->hasPurchasedAnyRachmat($rachmaIds);
// Returns: [1, 3] (if user purchased rachmat with IDs 1 and 3)
```

### Get All Purchased Rachmat

```php
$user = auth()->user();
$allPurchasedIds = $user->getPurchasedRachmatIds();
// Returns: [1, 3, 7, 12] (all rachmat IDs the user has purchased)
```

## Validation Rules

### Order Items Validation

- `items`: required, array, min:1, max:20
- `items.*.rachma_id`: required, must exist in rachmat table
- `payment_method`: required, must be one of: ccp, baridi_mob, dahabiya
- `payment_proof`: required, image file (jpeg, png, jpg), max 2MB

### Arabic Error Messages

All validation messages are provided in Arabic:

- **Items Required**: "يجب تحديد الرشمات المراد شراؤها."
- **Invalid Rachma**: "الرشمة المحددة غير موجودة."
- **Payment Method Required**: "طريقة الدفع مطلوبة."
- **Payment Proof Required**: "إثبات الدفع مطلوب."
- **Duplicate Purchase**: "لقد قمت بشراء بعض الرشمات من قبل"

## Business Logic

### Purchase Status Consideration

- ✅ **Completed Orders**: Prevent duplicate purchase
- ❌ **Pending Orders**: Allow duplicate purchase (order not yet processed)
- ❌ **Rejected Orders**: Allow duplicate purchase (order was declined)

### Designer Status Handling

The validation checks for duplicates **before** checking designer subscription status:

1. **Duplicate Check First**: If user already owns the rachma, return duplicate error
2. **Designer Status Second**: If no duplicates, then check if designer is active

### Multi-Item Order Support

The system handles both order types:

- **Legacy Single-Item**: Orders with `rachma_id` directly on the order
- **New Multi-Item**: Orders with items in the `order_items` table
- **Mixed Scenarios**: Users can have both types of orders in their history

## Testing

### Test Coverage

The comprehensive test suite covers:

- ✅ Single rachma duplicate detection
- ✅ Multiple rachmat with partial duplicates
- ✅ All rachmat already owned
- ✅ Legacy single-item order compatibility
- ✅ Status-based validation (only completed orders)
- ✅ Empty and invalid input handling
- ✅ Arabic error message validation

### Running Tests

```bash
php artisan test tests/Feature/Api/DuplicatePurchaseValidationTest.php
```

## Error Handling

### Client-Side Integration

```javascript
const createOrder = async (orderData) => {
    try {
        const response = await fetch('/api/orders', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(orderData),
        });

        const data = await response.json();

        if (!data.success) {
            if (data.error_type === 'duplicate_purchase') {
                // Handle duplicate purchase error
                showDuplicateError(data.already_purchased);
            } else {
                // Handle other errors
                showGeneralError(data.message);
            }
        } else {
            // Order created successfully
            showSuccess(data.message);
        }
    } catch (error) {
        showNetworkError();
    }
};

const showDuplicateError = (alreadyPurchased) => {
    const message = `${alreadyPurchased.message}`;
    alert(message);
    
    // Remove already purchased items from cart
    removeItemsFromCart(alreadyPurchased.rachma_ids);
};
```

## Performance Considerations

### Database Optimization

- **Indexed Queries**: Uses indexed columns for efficient lookups
- **Batch Processing**: Checks multiple rachmat in a single query
- **Minimal Data Transfer**: Only fetches necessary fields for validation

### Query Efficiency

The validation uses optimized queries:

```sql
-- Single query to check all rachmat at once
SELECT DISTINCT rachma_id FROM (
    SELECT rachma_id FROM orders 
    WHERE client_id = ? AND status = 'completed' AND rachma_id IN (?)
    UNION
    SELECT rachma_id FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.client_id = ? AND o.status = 'completed' AND oi.rachma_id IN (?)
) AS purchased_rachmat
```

## Security Considerations

1. **Authorization**: Only authenticated clients can create orders
2. **Data Validation**: All input is validated before processing
3. **Transaction Safety**: Uses database transactions to prevent partial orders
4. **Status Verification**: Only considers completed orders as valid purchases
5. **Designer Verification**: Ensures rachmat belong to active designers
