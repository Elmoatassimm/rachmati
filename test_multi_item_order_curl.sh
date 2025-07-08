#!/bin/bash

# Multi-Item Order Creation Test Script
# This script demonstrates how to create an order with multiple rachmat items using CURL

echo "=== Rachmat Multi-Item Order Creation Test ==="
echo ""

# Configuration
BASE_URL="http://127.0.0.1:8000"
CLIENT_EMAIL="aicha@client.com"
CLIENT_PASSWORD="password"
PAYMENT_PROOF_FILE="monterey.png"

# Check if payment proof file exists
if [ ! -f "$PAYMENT_PROOF_FILE" ]; then
    echo "❌ Error: Payment proof file '$PAYMENT_PROOF_FILE' not found!"
    echo "Please ensure the monterey.png file is in the current directory."
    exit 1
fi

echo "✅ Payment proof file found: $PAYMENT_PROOF_FILE"
echo ""

# Step 1: Login to get JWT token
echo "🔐 Step 1: Authenticating client user..."
echo "Email: $CLIENT_EMAIL"
echo ""

LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{
    \"email\": \"$CLIENT_EMAIL\",
    \"password\": \"$CLIENT_PASSWORD\"
  }")

echo "Login Response:"
echo "$LOGIN_RESPONSE" | jq '.' 2>/dev/null || echo "$LOGIN_RESPONSE"
echo ""

# Extract JWT token
JWT_TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.access_token' 2>/dev/null)

if [ "$JWT_TOKEN" = "null" ] || [ -z "$JWT_TOKEN" ]; then
    echo "❌ Error: Failed to get JWT token from login response"
    echo "Please check your credentials and ensure the server is running"
    exit 1
fi

echo "✅ JWT Token obtained successfully"
echo "Token: ${JWT_TOKEN:0:50}..."
echo ""

# Step 2: Create multi-item order
echo "🛒 Step 2: Creating multi-item order..."
echo "Order Details:"
echo "  - Rachma ID 1: رشمة قسنطينية كلاسيكية (8500.00 DZD)"
echo "  - Rachma ID 2: رشمة تلمسانية فاخرة (12000.00 DZD)" 
echo "  - Rachma ID 5: رشمة مجردة فنية (9200.00 DZD)"
echo "  - Total Expected: 29700.00 DZD"
echo "  - Payment Method: dahabiya"
echo "  - Payment Proof: $PAYMENT_PROOF_FILE"
echo ""

ORDER_RESPONSE=$(curl -s -X POST "$BASE_URL/api/orders" \
  -H "Authorization: Bearer $JWT_TOKEN" \
  -H "Accept: application/json" \
  -F "items[0][rachma_id]=1" \
  -F "items[1][rachma_id]=2" \
  -F "items[2][rachma_id]=5" \
  -F "payment_method=dahabiya" \
  -F "payment_proof=@$PAYMENT_PROOF_FILE")

echo "Order Creation Response:"
echo "$ORDER_RESPONSE" | jq '.' 2>/dev/null || echo "$ORDER_RESPONSE"
echo ""

# Check if order was created successfully
ORDER_SUCCESS=$(echo "$ORDER_RESPONSE" | jq -r '.success' 2>/dev/null)
ORDER_ID=$(echo "$ORDER_RESPONSE" | jq -r '.data.id' 2>/dev/null)

if [ "$ORDER_SUCCESS" = "true" ] && [ "$ORDER_ID" != "null" ] && [ -n "$ORDER_ID" ]; then
    echo "✅ Order created successfully!"
    echo "Order ID: $ORDER_ID"
    
    # Extract order details
    ORDER_AMOUNT=$(echo "$ORDER_RESPONSE" | jq -r '.data.amount' 2>/dev/null)
    ORDER_STATUS=$(echo "$ORDER_RESPONSE" | jq -r '.data.status' 2>/dev/null)
    ORDER_ITEMS_COUNT=$(echo "$ORDER_RESPONSE" | jq -r '.data.order_items | length' 2>/dev/null)
    
    echo "Order Amount: $ORDER_AMOUNT DZD"
    echo "Order Status: $ORDER_STATUS"
    echo "Number of Items: $ORDER_ITEMS_COUNT"
    echo ""
    
    # Step 3: Verify order details
    echo "🔍 Step 3: Retrieving order details..."
    
    ORDER_DETAILS=$(curl -s -X GET "$BASE_URL/api/orders/$ORDER_ID" \
      -H "Authorization: Bearer $JWT_TOKEN" \
      -H "Accept: application/json")
    
    echo "Order Details Response:"
    echo "$ORDER_DETAILS" | jq '.' 2>/dev/null || echo "$ORDER_DETAILS"
    echo ""
    
    echo "✅ Multi-item order test completed successfully!"
    echo ""
    echo "📋 Summary:"
    echo "  - Order ID: $ORDER_ID"
    echo "  - Total Amount: $ORDER_AMOUNT DZD"
    echo "  - Status: $ORDER_STATUS"
    echo "  - Items: $ORDER_ITEMS_COUNT rachmat products"
    echo "  - Payment Method: dahabiya"
    echo "  - Payment Proof: Uploaded successfully"
    echo ""
    echo "🎯 Next Steps:"
    echo "  1. Check the admin panel at: $BASE_URL/admin/orders"
    echo "  2. Verify the order appears in the orders list"
    echo "  3. Check that all 3 rachmat items are listed correctly"
    echo "  4. Verify the payment proof image is accessible"
    
else
    echo "❌ Error: Order creation failed!"
    echo "Please check the response above for error details"
    exit 1
fi
