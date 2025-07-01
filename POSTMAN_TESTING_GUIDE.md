# 🧪 Postman Testing Guide for Rachmat API

## 📋 Overview
This guide explains how to use the Postman collections to test all rachmat API endpoints with comprehensive filtration cases and language switching.

## 📁 Collection Files
1. **`postman_collection.json`** - Complete API collection (all endpoints)
2. **`postman_filtration_tests.json`** - Specialized filtration & language tests
3. **`postman_environment.json`** - Environment variables with test data

## 🔧 Setup Instructions

### 1. Import Collections
1. Open Postman
2. Click **Import** button
3. Select all three JSON files:
   - `postman_collection.json`
   - `postman_filtration_tests.json` 
   - `postman_environment.json`

### 2. Set Environment
1. Click the environment dropdown (top right)
2. Select **"Rachmat API Environment"**
3. Verify environment variables are loaded

### 3. Start Laravel Server
```bash
cd /path/to/rachmat/project
php artisan serve --host=127.0.0.1 --port=8000
```

## 🎯 Testing Scenarios

### 🔐 Authentication Tests
**Collection**: Main collection > Authentication
**Purpose**: Test JWT authentication flow

**Key Tests**:
- ✅ Register new user
- ✅ Login with valid credentials  
- ✅ Get user profile (protected)
- ✅ Refresh JWT token
- ✅ Logout

### 🔍 Filtration Tests 
**Collection**: Filtration Tests > Rachmat Filtration Tests
**Purpose**: Test all rachmat filtering capabilities

**Filter Types**:
1. **Category Filter**: `?category_id=1`
2. **Subcategory Filter**: `?subcategory_id=1`
3. **Designer Filter**: `?designer_id=1`
4. **Price Range**: `?min_price=100&max_price=1000`
5. **Search Text**: `?search=تطريز`

**Sort Options**:
- `sort_by=latest` - Latest first
- `sort_by=price_asc` - Price low to high
- `sort_by=price_desc` - Price high to low
- `sort_by=popular` - Most popular
- `sort_by=rating` - Best rated

### 🌐 Language Switching Tests
**Collection**: Filtration Tests > Language Switching Tests
**Purpose**: Test Arabic/French language support

**Language Tests**:
- 🇩🇿 **Arabic**: `Accept-Language: ar`
- 🇫🇷 **French**: `Accept-Language: fr`

**Endpoints Tested**:
- Categories (multilingual names)
- Rachmat listings
- Error messages
- Popular patterns

### 📄 Pagination Tests
**Collection**: Filtration Tests > Pagination Tests
**Purpose**: Test pagination functionality

**Parameters**:
- `page=1,2,3...` - Page number
- `per_page=5,10,20,50` - Items per page

### 🔥 Complex Combination Tests
**Collection**: Filtration Tests > Complex Combinations
**Purpose**: Test multiple filters together

**Examples**:
```
# Multi-filter combination
?category_id=1&min_price=100&max_price=1000&sort_by=popular&per_page=5

# Search + Category + Sort
?search=تطريز&category_id=1&sort_by=price_asc&per_page=5

# Full filter stack
?search=تطريز&category_id=1&subcategory_id=1&designer_id=1&min_price=100&max_price=1000&sort_by=rating&per_page=3
```

## 🔧 Environment Variables

### 🔑 Authentication
```json
{
  "test_client_email": "aicha@client.com",
  "test_client_password": "password",
  "test_designer_email": "fatima@designer.com", 
  "test_designer_password": "password",
  "test_admin_email": "admin@rachmat.com",
  "test_admin_password": "password"
}
```

### 🎛️ Filter Parameters
```json
{
  "category_id": "1",
  "subcategory_id": "1", 
  "designer_id": "1",
  "min_price": "100",
  "max_price": "1000",
  "search_term": "تطريز",
  "page_size": "10",
  "language": "ar"
}
```

### 🌐 Base Configuration
```json
{
  "base_url": "http://127.0.0.1:8000",
  "auth_token": "", // Auto-populated on login
  "user_id": "" // Auto-populated on login
}
```

## 🧪 Testing Workflows

### 1. Quick Filtration Test
1. Run **"Login & Save Token"** (saves JWT)
2. Run **"All Rachmat (Baseline)"** 
3. Run filter tests one by one:
   - Filter by Category
   - Filter by Price Range
   - Search by Name
   - Sort by Price

### 2. Language Switching Test
1. Run **"Categories in Arabic"** 
2. Run **"Categories in French"**
3. Compare responses for language differences
4. Test error messages in both languages

### 3. Comprehensive Test Suite
1. **Setup**: Login & Save Token
2. **Basic Filters**: Run all basic filtration tests
3. **Search**: Test Arabic/French search terms
4. **Sorting**: Test all sort options
5. **Combinations**: Test complex filter combinations
6. **Edge Cases**: Test invalid parameters
7. **Languages**: Switch between AR/FR for all tests

## 📊 Expected Results

### ✅ Successful Response Format
```json
{
  "success": true,
  "message": "رسالة النجاح باللغة العربية",
  "data": {
    "data": [...], // Array of rachmat
    "links": {...}, // Pagination links
    "meta": {...}   // Pagination metadata
  }
}
```

### ❌ Error Response Format
```json
{
  "success": false,
  "message": "رسالة الخطأ باللغة العربية",
  "errors": {
    "field_name": ["رسالة التحقق"]
  }
}
```

## 🎯 Key Test Validations

### 1. Filtration Validation
- ✅ Filters return only matching results
- ✅ Empty results when no matches
- ✅ Pagination works with filters
- ✅ Multiple filters work together
- ✅ Invalid filters handled gracefully

### 2. Language Validation  
- ✅ Content changes based on Accept-Language header
- ✅ Arabic content for `Accept-Language: ar`
- ✅ French content for `Accept-Language: fr`
- ✅ Error messages in correct language
- ✅ Fallback to default language if unsupported

### 3. Pagination Validation
- ✅ Correct page numbers in metadata
- ✅ Proper total count
- ✅ Links for next/previous pages
- ✅ Consistent results per page
- ✅ Last page handling

### 4. Performance Validation
- ✅ Response time < 1 second for basic queries
- ✅ Response time < 2 seconds for complex filters
- ✅ Proper HTTP status codes
- ✅ Consistent response format

## 🔧 Troubleshooting

### Common Issues

#### 1. Authentication Failed
**Symptoms**: 401 Unauthorized errors
**Solution**: 
1. Run "Login & Save Token" first
2. Check if token is saved in environment variables
3. Verify test credentials are correct

#### 2. Server Not Running
**Symptoms**: Connection refused errors
**Solution**:
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

#### 3. Empty Results
**Symptoms**: All filter tests return empty data
**Solution**:
1. Check if database is seeded:
   ```bash
   php artisan db:seed
   ```
2. Verify test data exists

#### 4. Language Not Switching
**Symptoms**: Always returns same language
**Solution**:
1. Check Accept-Language header is set
2. Verify language parameter in environment
3. Check Laravel localization configuration

### Debug Tips

1. **Check Console**: Postman console shows detailed request/response
2. **Environment Variables**: Verify all variables are populated
3. **Response Inspection**: Check response structure and data
4. **Network Tab**: Monitor actual HTTP requests being sent

## 📈 Testing Metrics

### Test Coverage
- **23 Total Endpoints** covered
- **15 Filtration Scenarios** tested
- **6 Language Test Cases** included
- **8 Pagination Test Cases** covered
- **5 Edge Cases** handled

### Expected Results
- ✅ **100% Success Rate** for valid requests
- ✅ **Proper Error Handling** for invalid requests
- ✅ **Language Switching** working correctly
- ✅ **All Filters** returning appropriate results
- ✅ **Pagination** functioning across all scenarios

## 🚀 Advanced Usage

### Custom Environment Setup
1. Duplicate environment
2. Modify base_url for different servers
3. Adjust test data for your specific setup
4. Create custom test scenarios

### Automated Testing
1. Use Postman Runner for batch testing
2. Export Newman collection for CI/CD
3. Set up monitoring for production API
4. Create custom test scripts for validation

### API Documentation
- Review `API_DOCUMENTATION.md` for complete endpoint reference
- Check `API_TESTING_SUMMARY.md` for detailed test results
- Use collections as living documentation for API capabilities

## 📞 Support

For issues or questions:
1. Check existing documentation files
2. Review test results in console
3. Verify environment configuration
4. Test with CURL commands for comparison

**Happy Testing! 🎉** 