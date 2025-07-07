#!/bin/bash

# Multi-Item Order API Testing Script
# This script demonstrates how to test the multi-item order functionality

echo "=== Multi-Item Order API Testing ==="
echo ""

# Configuration
BASE_URL="http://127.0.0.1:8000"
API_URL="$BASE_URL/api"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}Base URL: $BASE_URL${NC}"
echo -e "${BLUE}API URL: $API_URL${NC}"
echo ""

# Function to print section headers
print_section() {
    echo -e "${YELLOW}=== $1 ===${NC}"
    echo ""
}

# Function to print test commands
print_test() {
    echo -e "${GREEN}$1${NC}"
    echo ""
}

print_section "1. Authentication (Get JWT Token)"

print_test "Login to get JWT token:"
echo 'curl -X POST '$API_URL'/auth/login \'
echo '  -H "Content-Type: application/json" \'
echo '  -H "Accept: application/json" \'
echo '  -d "{"'
echo '    "email": "client@example.com",'
echo '    "password": "password"'
echo '  }"'
echo ""

echo -e "${RED}Note: Save the JWT token from the response and use it in the following requests${NC}"
echo 'export JWT_TOKEN="your_jwt_token_here"'
echo ""

print_section "2. Single Item Order (Backward Compatibility)"

print_test "Create single item order:"
echo 'curl -X POST '$API_URL'/orders \'
echo '  -H "Authorization: Bearer $JWT_TOKEN" \'
echo '  -H "Accept: application/json" \'
echo '  -F "rachma_id=1" \'
echo '  -F "payment_method=ccp" \'
echo '  -F "payment_proof=@/path/to/payment_proof.jpg"'
echo ""

print_section "3. Multi-Item Order"

print_test "Create multi-item order with 3 different rachmat:"
echo 'curl -X POST '$API_URL'/orders \'
echo '  -H "Authorization: Bearer $JWT_TOKEN" \'
echo '  -H "Accept: application/json" \'
echo '  -F "items[0][rachma_id]=1" \'
echo '  -F "items[1][rachma_id]=2" \'
echo '  -F "items[2][rachma_id]=3" \'
echo '  -F "payment_method=baridi_mob" \'
echo '  -F "payment_proof=@/path/to/payment_proof.jpg"'
echo ""

print_test "Create multi-item order with multiple instances of same rachma:"
echo 'curl -X POST '$API_URL'/orders \'
echo '  -H "Authorization: Bearer $JWT_TOKEN" \'
echo '  -H "Accept: application/json" \'
echo '  -F "items[0][rachma_id]=1" \'
echo '  -F "items[1][rachma_id]=1" \'
echo '  -F "items[2][rachma_id]=2" \'
echo '  -F "payment_method=dahabiya" \'
echo '  -F "payment_proof=@/path/to/payment_proof.jpg"'
echo ""

print_section "4. Get Order Details"

print_test "Get specific order details (includes order items):"
echo 'curl -H "Authorization: Bearer $JWT_TOKEN" \'
echo '  -H "Accept: application/json" \'
echo '  '$API_URL'/orders/{order_id}'
echo ""

print_section "5. Get My Orders"

print_test "Get all my orders (includes order items):"
echo 'curl -H "Authorization: Bearer $JWT_TOKEN" \'
echo '  -H "Accept: application/json" \'
echo '  '$API_URL'/my-orders'
echo ""

print_test "Get my orders with pagination:"
echo 'curl -H "Authorization: Bearer $JWT_TOKEN" \'
echo '  -H "Accept: application/json" \'
echo '  "'$API_URL'/my-orders?per_page=10&page=1"'
echo ""

print_test "Get my orders filtered by status:"
echo 'curl -H "Authorization: Bearer $JWT_TOKEN" \'
echo '  -H "Accept: application/json" \'
echo '  "'$API_URL'/my-orders?status=pending"'
echo ""

print_section "6. Expected Response Structure"

echo -e "${GREEN}Single Item Order Response:${NC}"
cat << 'EOF'
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 123,
    "client_id": 1,
    "rachma_id": 1,
    "amount": 5000.00,
    "payment_method": "ccp",
    "status": "pending",
    "order_items": [
      {
        "id": 1,
        "rachma_id": 1,
        "price": 5000.00,
        "rachma": {
          "id": 1,
          "title_ar": "رشمة جميلة",
          "price": 5000.00
        }
      }
    ]
  }
}
EOF

echo ""
echo -e "${GREEN}Multi-Item Order Response:${NC}"
cat << 'EOF'
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 124,
    "client_id": 1,
    "rachma_id": null,
    "amount": 15000.00,
    "payment_method": "baridi_mob",
    "status": "pending",
    "order_items": [
      {
        "id": 2,
        "rachma_id": 1,
        "price": 5000.00,
        "rachma": {
          "id": 1,
          "title_ar": "رشمة جميلة"
        }
      },
      {
        "id": 3,
        "rachma_id": 2,
        "price": 3000.00,
        "rachma": {
          "id": 2,
          "title_ar": "رشمة أخرى"
        }
      },
      {
        "id": 4,
        "rachma_id": 3,
        "price": 7000.00,
        "rachma": {
          "id": 3,
          "title_ar": "رشمة ثالثة"
        }
      }
    ]
  }
}
EOF

echo ""
print_section "7. Validation Rules"

echo -e "${GREEN}Single Item Order Validation:${NC}"
echo "- rachma_id: required, must exist in rachmat table"
echo "- payment_method: required, must be one of: ccp, baridi_mob, dahabiya"
echo "- payment_proof: required, image file (jpeg,png,jpg), max:2MB"
echo ""

echo -e "${GREEN}Multi-Item Order Validation:${NC}"
echo "- items: required, array, min:1, max:20 items"
echo "- items.*.rachma_id: required, must exist in rachmat table"
echo "- payment_method: required, must be one of: ccp, baridi_mob, dahabiya"
echo "- payment_proof: required, image file (jpeg,png,jpg), max:2MB"
echo ""
echo "Note: To order multiple copies of the same rachma, include multiple array entries with the same rachma_id"
echo ""

print_section "8. Error Responses"

echo -e "${GREEN}Validation Error Example:${NC}"
cat << 'EOF'
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "items.0.rachma_id": ["The selected rachma id is invalid."],
    "payment_proof": ["The payment proof field is required."]
  }
}
EOF

echo ""
echo -e "${GREEN}Rachma Not Available Error:${NC}"
cat << 'EOF'
{
  "success": false,
  "message": "Rachma 'رشمة غير متاحة' is not available for purchase"
}
EOF

echo ""
print_section "9. Testing Checklist"

echo "□ Test single-item order creation (backward compatibility)"
echo "□ Test multi-item order creation with 2-3 items"
echo "□ Test validation errors (invalid rachma_id, missing fields)"
echo "□ Test order retrieval with order items included"
echo "□ Test my-orders endpoint with pagination and filtering"
echo "□ Verify admin UI shows multiple items correctly"
echo "□ Verify designer UI shows orders with multiple items"
echo "□ Test order completion workflow for multi-item orders"
echo "□ Verify total amount calculation is correct"
echo "□ Test with inactive rachmat (should fail)"
echo ""

echo -e "${BLUE}=== End of Testing Guide ===${NC}"
