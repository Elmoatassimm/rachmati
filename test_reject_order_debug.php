<?php

/**
 * Debug script for reject order functionality
 */

echo "=== Reject Order Debug Script ===\n\n";

// Check if all necessary components are in place
$files_to_check = [
    'app/Http/Controllers/Admin/OrderController.php',
    'app/Http/Requests/Admin/UpdateOrderRequest.php',
    'app/Models/Order.php',
    'resources/js/pages/Admin/Orders/Show.tsx'
];

echo "✅ File Existence Check:\n";
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "  ✓ $file exists\n";
    } else {
        echo "  ✗ $file missing\n";
    }
}

echo "\n✅ Backend Components Check:\n";

// Check Order model
$orderModelContent = file_get_contents('app/Models/Order.php');
if (strpos($orderModelContent, 'rejection_reason') !== false) {
    echo "  ✓ Order model has rejection_reason in fillable\n";
} else {
    echo "  ✗ Order model missing rejection_reason in fillable\n";
}

// Check UpdateOrderRequest
$requestContent = file_get_contents('app/Http/Requests/Admin/UpdateOrderRequest.php');
if (strpos($requestContent, 'required_if:status,rejected') !== false) {
    echo "  ✓ UpdateOrderRequest has rejection_reason validation\n";
} else {
    echo "  ✗ UpdateOrderRequest missing rejection_reason validation\n";
}

// Check OrderController
$controllerContent = file_get_contents('app/Http/Controllers/Admin/OrderController.php');
if (strpos($controllerContent, 'rejected') !== false && strpos($controllerContent, 'rejection_reason') !== false) {
    echo "  ✓ OrderController handles rejected status\n";
} else {
    echo "  ✗ OrderController missing rejected status handling\n";
}

echo "\n✅ Frontend Components Check:\n";

// Check React component
$reactContent = file_get_contents('resources/js/pages/Admin/Orders/Show.tsx');
if (strpos($reactContent, 'handleRejectOrder') !== false) {
    echo "  ✓ React component has handleRejectOrder function\n";
} else {
    echo "  ✗ React component missing handleRejectOrder function\n";
}

if (strpos($reactContent, 'rejection_reason') !== false) {
    echo "  ✓ React component handles rejection_reason\n";
} else {
    echo "  ✗ React component missing rejection_reason handling\n";
}

echo "\n✅ Potential Issues to Check:\n";
echo "1. JavaScript Console Errors:\n";
echo "   - Open browser dev tools (F12)\n";
echo "   - Check Console tab for errors\n";
echo "   - Look for validation or network errors\n\n";

echo "2. Network Request Issues:\n";
echo "   - Open browser dev tools (F12)\n";
echo "   - Go to Network tab\n";
echo "   - Click 'رفض الطلب' button\n";
echo "   - Check if PUT request is sent to /admin/orders/31\n";
echo "   - Check response status and content\n\n";

echo "3. CSRF Token Issues:\n";
echo "   - Check if CSRF token is present in request headers\n";
echo "   - Look for 419 status code (CSRF token mismatch)\n\n";

echo "4. Validation Errors:\n";
echo "   - Check if rejection_reason is being sent\n";
echo "   - Look for 422 status code (validation failed)\n\n";

echo "✅ Manual Testing Steps:\n";
echo "1. Access: http://127.0.0.1:8000/admin/orders/31\n";
echo "2. Open browser dev tools (F12)\n";
echo "3. Go to Console and Network tabs\n";
echo "4. Click 'رفض الطلب' button\n";
echo "5. Enter rejection reason when prompted\n";
echo "6. Check for:\n";
echo "   - Console errors\n";
echo "   - Network request details\n";
echo "   - Response status and content\n\n";

echo "✅ Expected Behavior:\n";
echo "- Prompt appears asking for rejection reason\n";
echo "- User enters reason and clicks OK\n";
echo "- PUT request sent to /admin/orders/31\n";
echo "- Request includes: status='rejected', rejection_reason='user input'\n";
echo "- Response: 302 redirect to orders index\n";
echo "- Order status changes to 'rejected'\n";
echo "- Page shows 'تم رفض الطلب' with reason\n\n";

echo "✅ Common Issues and Solutions:\n";
echo "1. Prompt Cancelled:\n";
echo "   - User clicks Cancel in prompt\n";
echo "   - Solution: Click OK and enter reason\n\n";

echo "2. Empty Rejection Reason:\n";
echo "   - User enters empty string\n";
echo "   - Solution: Enter actual rejection reason\n\n";

echo "3. CSRF Token Mismatch:\n";
echo "   - 419 error in network tab\n";
echo "   - Solution: Refresh page and try again\n\n";

echo "4. Validation Error:\n";
echo "   - 422 error with validation messages\n";
echo "   - Solution: Check rejection_reason is not empty\n\n";

echo "5. JavaScript Error:\n";
echo "   - Error in console tab\n";
echo "   - Solution: Check browser compatibility\n\n";

echo "✅ Debug CURL Command:\n";
echo "curl -X PUT 'http://127.0.0.1:8000/admin/orders/31' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Accept: application/json' \\\n";
echo "  -H 'X-CSRF-TOKEN: your_csrf_token' \\\n";
echo "  -H 'Cookie: your_session_cookie' \\\n";
echo "  -d '{\n";
echo "    \"status\": \"rejected\",\n";
echo "    \"admin_notes\": \"تم رفض الطلب\",\n";
echo "    \"rejection_reason\": \"سبب الرفض للاختبار\"\n";
echo "  }'\n\n";

echo "✅ Next Steps:\n";
echo "1. Follow manual testing steps above\n";
echo "2. Check browser dev tools for specific errors\n";
echo "3. Report exact error message or behavior\n";
echo "4. Check if prompt appears and what happens after\n";

?>
