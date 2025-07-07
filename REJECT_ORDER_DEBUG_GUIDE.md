# Reject Order Debug Guide

## 🐛 **Issue**: "رفض الطلب" (Reject Order) not working

**URL**: `http://127.0.0.1:8000/admin/orders/31`

## 🔧 **Debugging Enhancements Applied**

### 1. **Frontend Improvements**
Enhanced the `handleRejectOrder` function with better error handling and user feedback:

```javascript
const handleRejectOrder = () => {
  const reason = prompt('سبب الرفض:');
  
  if (reason === null) {
    // User clicked Cancel
    console.log('Order rejection cancelled by user');
    return;
  }
  
  if (reason.trim() === '') {
    alert('يرجى إدخال سبب الرفض');
    return;
  }

  // Enhanced logging and error handling
  updateForm.put(route('admin.orders.update', order.id), {
    onSuccess: () => {
      console.log('Order rejected successfully');
      alert('تم رفض الطلب بنجاح');
    },
    onError: (errors) => {
      console.error('Error rejecting order:', errors);
      alert('حدث خطأ أثناء رفض الطلب. يرجى المحاولة مرة أخرى.');
    },
  });
};
```

### 2. **Backend Logging**
Added detailed logging to track the rejection process:

```php
// In OrderController update method
\Log::info("Order update request", [
    'order_id' => $order->id,
    'old_status' => $oldStatus,
    'new_status' => $newStatus,
    'validated_data' => $validated
]);

// When processing rejection
\Log::info("Order {$order->id} being rejected", [
    'rejection_reason' => $validated['rejection_reason'],
    'admin_notes' => $validated['admin_notes']
]);
```

## 🧪 **Step-by-Step Testing**

### 1. **Open Browser Dev Tools**
- Press `F12` to open developer tools
- Go to **Console** tab
- Go to **Network** tab

### 2. **Test Reject Order**
1. Access: `http://127.0.0.1:8000/admin/orders/31`
2. Click "رفض الطلب" button
3. **Check what happens:**

#### **Scenario A: Prompt doesn't appear**
- **Issue**: JavaScript error
- **Check**: Console tab for errors
- **Solution**: Refresh page and try again

#### **Scenario B: Prompt appears, user clicks Cancel**
- **Expected**: Console shows "Order rejection cancelled by user"
- **Action**: Click "رفض الطلب" again and click OK

#### **Scenario C: Prompt appears, user enters empty reason**
- **Expected**: Alert shows "يرجى إدخال سبب الرفض"
- **Action**: Enter a valid rejection reason

#### **Scenario D: User enters reason and clicks OK**
- **Expected**: 
  - Console shows submission data
  - Network tab shows PUT request to `/admin/orders/31`
  - Success alert or error alert appears

### 3. **Check Network Request**
In the Network tab, look for:
- **Request URL**: `PUT /admin/orders/31`
- **Request Headers**: Should include CSRF token
- **Request Payload**: Should include:
  ```json
  {
    "status": "rejected",
    "admin_notes": "تم رفض الطلب",
    "rejection_reason": "user entered reason"
  }
  ```

### 4. **Check Response**
- **200/302**: Success - order should be rejected
- **419**: CSRF token mismatch - refresh page
- **422**: Validation error - check rejection reason
- **500**: Server error - check Laravel logs

## 📋 **Common Issues and Solutions**

### 1. **User Clicks Cancel**
**Symptom**: Nothing happens when clicking reject button
**Solution**: Click "رفض الطلب" again and click OK in prompt

### 2. **Empty Rejection Reason**
**Symptom**: Alert shows "يرجى إدخال سبب الرفض"
**Solution**: Enter a valid rejection reason (not empty)

### 3. **CSRF Token Mismatch**
**Symptom**: 419 error in network tab
**Solution**: Refresh the page and try again

### 4. **Validation Error**
**Symptom**: 422 error with validation messages
**Check**: Rejection reason is required and not empty
**Solution**: Ensure reason is provided

### 5. **JavaScript Error**
**Symptom**: Console shows JavaScript errors
**Solution**: Check browser compatibility, refresh page

## 🔍 **Laravel Logs Check**

Check the Laravel logs for detailed information:

```bash
tail -f storage/logs/laravel.log
```

Look for:
- Order update request logs
- Rejection processing logs
- Any error messages

## 🧪 **Manual API Test**

Test the API directly with curl:

```bash
curl -X PUT 'http://127.0.0.1:8000/admin/orders/31' \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -H 'X-CSRF-TOKEN: your_csrf_token' \
  -H 'Cookie: your_session_cookie' \
  -d '{
    "status": "rejected",
    "admin_notes": "تم رفض الطلب",
    "rejection_reason": "سبب الرفض للاختبار"
  }'
```

## ✅ **Expected Working Flow**

1. **Click "رفض الطلب"** → Prompt appears
2. **Enter rejection reason** → Click OK
3. **Console logs** → Shows submission data
4. **Network request** → PUT to `/admin/orders/31`
5. **Success response** → 302 redirect
6. **Page updates** → Shows "تم رفض الطلب" status
7. **Success alert** → "تم رفض الطلب بنجاح"

## 🎯 **Next Steps**

1. **Follow the testing steps above**
2. **Check browser console for any errors**
3. **Check network tab for request/response details**
4. **Check Laravel logs for backend processing**
5. **Report specific error messages or behavior**

The enhanced debugging should help identify exactly where the issue is occurring!
