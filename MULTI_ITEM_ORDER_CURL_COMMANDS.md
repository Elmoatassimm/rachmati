# Multi-Item Order CURL Commands

This document provides complete CURL commands for creating orders with multiple rachmat items using the Rachmat API.

## Prerequisites

1. **Laravel server running** on `http://127.0.0.1:8000`
2. **monterey.png file** in the current directory
3. **Database seeded** with rachmat and client users
4. **jq installed** (optional, for JSON parsing)

## Available Test Data

### Client Users
- **Email**: `aicha@client.com`
- **Password**: `password`
- **Name**: عائشة الجزائرية

### Available Rachmat Items
- **ID 1**: رشمة قسنطينية كلاسيكية (8,500.00 DZD)
- **ID 2**: رشمة تلمسانية فاخرة (12,000.00 DZD)
- **ID 3**: رشمة عنابية عصرية (7,500.00 DZD)
- **ID 4**: رشمة هندسية معاصرة (6,500.00 DZD)
- **ID 5**: رشمة مجردة فنية (9,200.00 DZD)

## Step-by-Step CURL Commands

### 1. Authentication (Get JWT Token)

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "aicha@client.com",
    "password": "password"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "user": {
      "id": 8,
      "name": "عائشة الجزائرية",
      "email": "aicha@client.com"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 999999999999
  }
}
```

### 2. Create Multi-Item Order

Replace `YOUR_JWT_TOKEN` with the token from step 1:

```bash
curl -X POST http://127.0.0.1:8000/api/orders \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Accept: application/json" \
  -F "items[0][rachma_id]=1" \
  -F "items[1][rachma_id]=2" \
  -F "items[2][rachma_id]=5" \
  -F "payment_method=dahabiya" \
  -F "payment_proof=@monterey.png"
```

**Order Details:**
- **Item 1**: Rachma ID 1 (8,500.00 DZD)
- **Item 2**: Rachma ID 2 (12,000.00 DZD)
- **Item 3**: Rachma ID 5 (9,200.00 DZD)
- **Total**: 29,700.00 DZD
- **Payment Method**: dahabiya
- **Payment Proof**: monterey.png file

### 3. Alternative Order Examples

#### Example 1: Two Items with CCP Payment
```bash
curl -X POST http://127.0.0.1:8000/api/orders \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Accept: application/json" \
  -F "items[0][rachma_id]=3" \
  -F "items[1][rachma_id]=4" \
  -F "payment_method=ccp" \
  -F "payment_proof=@monterey.png"
```

#### Example 2: Multiple Copies of Same Item
```bash
curl -X POST http://127.0.0.1:8000/api/orders \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Accept: application/json" \
  -F "items[0][rachma_id]=1" \
  -F "items[1][rachma_id]=1" \
  -F "items[2][rachma_id]=3" \
  -F "payment_method=baridi_mob" \
  -F "payment_proof=@monterey.png"
```

#### Example 3: Large Order (5 Items)
```bash
curl -X POST http://127.0.0.1:8000/api/orders \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Accept: application/json" \
  -F "items[0][rachma_id]=1" \
  -F "items[1][rachma_id]=2" \
  -F "items[2][rachma_id]=3" \
  -F "items[3][rachma_id]=4" \
  -F "items[4][rachma_id]=5" \
  -F "payment_method=dahabiya" \
  -F "payment_proof=@monterey.png"
```

## Expected Response Format

### Successful Order Creation
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 123,
    "client_id": 8,
    "rachma_id": null,
    "amount": "29700.00",
    "payment_method": "dahabiya",
    "payment_proof_path": "payment_proofs/xyz.png",
    "status": "pending",
    "created_at": "2025-01-07T...",
    "order_items": [
      {
        "id": 1,
        "order_id": 123,
        "rachma_id": 1,
        "price": "8500.00",
        "rachma": {
          "id": 1,
          "title_ar": "رشمة قسنطينية كلاسيكية",
          "price": "8500.00"
        }
      },
      {
        "id": 2,
        "order_id": 123,
        "rachma_id": 2,
        "price": "12000.00",
        "rachma": {
          "id": 2,
          "title_ar": "رشمة تلمسانية فاخرة",
          "price": "12000.00"
        }
      },
      {
        "id": 3,
        "order_id": 123,
        "rachma_id": 5,
        "price": "9200.00",
        "rachma": {
          "id": 5,
          "title_ar": "رشمة مجردة فنية",
          "price": "9200.00"
        }
      }
    ],
    "client": {
      "id": 8,
      "name": "عائشة الجزائرية",
      "email": "aicha@client.com"
    }
  }
}
```

## Validation Rules

### Required Fields
- `items`: Array of items (min: 1, max: 20)
- `items.*.rachma_id`: Must exist in rachmat table
- `payment_method`: Must be one of: `ccp`, `baridi_mob`, `dahabiya`
- `payment_proof`: Image file (jpeg, png, jpg, max: 2MB)

### Authentication
- Must include `Authorization: Bearer {token}` header
- Token must be valid and not expired
- User must be of type 'client'

## Testing the Order

### 1. Run the Automated Script
```bash
./test_multi_item_order_curl.sh
```

### 2. Manual Verification
1. **Check Admin Panel**: Visit `http://127.0.0.1:8000/admin/orders`
2. **Verify Order Details**: Ensure all items are listed correctly
3. **Check Payment Proof**: Verify the uploaded image is accessible
4. **Validate Totals**: Confirm the total amount matches sum of item prices

## Troubleshooting

### Common Errors

#### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```
**Solution**: Check JWT token is valid and included in Authorization header

#### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "items.0.rachma_id": ["The selected items.0.rachma_id is invalid."]
  }
}
```
**Solution**: Verify rachma_id exists in database

#### 413 Payload Too Large
**Solution**: Ensure payment_proof file is under 2MB

### File Upload Issues
- Ensure monterey.png exists in current directory
- Check file permissions are readable
- Verify file is a valid image format (jpeg, png, jpg)

## Complete Working Example

See `test_multi_item_order_curl.sh` for a complete automated test script that:
1. Authenticates the user
2. Creates a multi-item order
3. Verifies the order was created successfully
4. Retrieves and displays order details
5. Provides next steps for manual verification
