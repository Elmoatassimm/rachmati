# Rachmat API Documentation

## Table of Contents
- [Base URL & Authentication](#base-url--authentication)
- [Response Format](#response-format)
- [Error Handling](#error-handling)
- [Public Endpoints](#public-endpoints)
  - [Authentication](#authentication)
  - [Rachmat Browsing](#rachmat-browsing)
  - [Categories](#categories)
  - [Admin Payment Information](#admin-payment-information)
- [Protected Endpoints (JWT Required)](#protected-endpoints-jwt-required)
  - [Authentication Management](#authentication-management)
  - [Orders](#orders)
  - [Ratings](#ratings)
- [Designer Endpoints](#designer-endpoints)
- [Admin Endpoints](#admin-endpoints)
- [Rate Limiting](#rate-limiting)
- [File Upload Guidelines](#file-upload-guidelines)
- [Localization](#localization)
- [Versioning](#versioning)
- [Support and Contact](#support-and-contact)

## Base URL & Authentication

**Base URL**: `https://your-domain.com/api`

### Authentication Types
1. **Public Routes**: No authentication required
2. **JWT Authentication**: Requires `Authorization: Bearer {token}` header
3. **Designer Routes**: JWT + Designer role required
4. **Admin Routes**: JWT + Admin role required

### JWT Token Management
Tokens are obtained through login and can be refreshed using the refresh endpoint.

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "رسالة النجاح باللغة العربية",
  "data": {
    // Response data here
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": {
    "data": [...],
    "links": {
      "first": "...",
      "last": "...",
      "prev": null,
      "next": "..."
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 5,
      "per_page": 15,
      "to": 15,
      "total": 75
    }
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "رسالة الخطأ باللغة العربية",
  "errors": {
    "field_name": ["رسالة التحقق"]
  }
}
```

---

## Error Handling

### HTTP Status Codes
- `200` - Success
- `201` - Created successfully
- `400` - Bad request
- `401` - Unauthorized (authentication required)
- `403` - Forbidden (insufficient permissions)
- `404` - Resource not found
- `422` - Validation error
- `500` - Internal server error

### Common Error Responses

#### Authentication Required (401)
```json
{
  "success": false,
  "message": "غير مصرح بالوصول"
}
```

#### Validation Error (422)
```json
{
  "success": false,
  "message": "بيانات غير صحيحة",
  "errors": {
    "email": ["حقل البريد الإلكتروني مطلوب"],
    "password": ["كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل"]
  }
}
```

---

## Public Endpoints

### Authentication

#### Register User
**POST** `/auth/register`

Creates a new user account.

**Request Body:**
```json
{
  "name": "أحمد محمد",
  "email": "ahmed@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "0123456789",
  "user_type": "client"
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `email`: required, email, unique:users
- `password`: required, string, min:8, confirmed
- `phone`: required, string, max:20, unique:users
- `user_type`: required, in:client,designer

**Success Response (201):**
```json
{
  "success": true,
  "message": "تم إنشاء الحساب بنجاح",
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "email": "ahmed@example.com",
      "phone": "0123456789",
      "user_type": "client",
      "email_verified_at": null,
      "created_at": "2024-01-15T10:30:00.000000Z"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

#### Login User
**POST** `/auth/login`

Authenticates user and returns JWT token.

**Request Body:**
```json
{
  "email": "ahmed@example.com",
  "password": "password123"
}
```

**Validation Rules:**
- `email`: required, email
- `password`: required, string

**Success Response (200):**
```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "email": "ahmed@example.com",
      "user_type": "client"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "بيانات الدخول غير صحيحة"
}
```

### Rachmat Browsing

#### Get Rachmat List
**GET** `/rachmat`

Retrieves paginated list of rachmat with optional filtering.

**Query Parameters:**
- `page`: integer (default: 1) - Page number
- `per_page`: integer (default: 15, max: 50) - Items per page
- `category_id`: integer - Filter by category
- `designer_id`: integer - Filter by designer
- `min_price`: decimal - Minimum price filter
- `max_price`: decimal - Maximum price filter
- `search`: string - Search in title/description
- `sort`: string (latest, price_asc, price_desc, popular) - Sort order

**Example Request:**
```
GET /api/rachmat?category_id=1&min_price=100&max_price=500&search=فستان&sort=price_asc&page=1&per_page=20
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "title": "فستان سهرة أنيق",
        "description": "فستان سهرة جميل بتصميم عصري",
        "price": 250.00,
        "discounted_price": 200.00,
        "main_image": "https://example.com/images/dress1.jpg",
        "category": {
          "id": 1,
          "name": "فساتين"
        },
        "designer": {
          "id": 1,
          "store_name": "متجر الأناقة",
          "average_rating": 4.5
        },
        "images_count": 5,
        "rating": 4.3,
        "ratings_count": 25,
        "created_at": "2024-01-15T10:30:00.000000Z"
      }
    ],
    "links": {
      "first": "http://localhost/api/rachmat?page=1",
      "last": "http://localhost/api/rachmat?page=10",
      "prev": null,
      "next": "http://localhost/api/rachmat?page=2"
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 10,
      "per_page": 15,
      "to": 15,
      "total": 150
    }
  }
}
```

#### Get Single Rachma
**GET** `/rachmat/{id}`

Retrieves detailed information about a specific rachma.

**Path Parameters:**
- `id`: integer (required) - Rachma ID

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "فستان سهرة أنيق",
    "description": "فستان سهرة جميل بتصميم عصري مناسب للمناسبات الخاصة",
    "price": 250.00,
    "discounted_price": 200.00,
    "images": [
      {
        "id": 1,
        "image_path": "https://example.com/images/dress1_1.jpg",
        "is_main": true
      },
      {
        "id": 2,
        "image_path": "https://example.com/images/dress1_2.jpg",
        "is_main": false
      }
    ],
    "category": {
      "id": 1,
      "name": "فساتين",
      "subcategory": {
        "id": 1,
        "name": "فساتين سهرة"
      }
    },
    "designer": {
      "id": 1,
      "store_name": "متجر الأناقة",
      "description": "متجر متخصص في الأزياء العصرية",
      "average_rating": 4.5,
      "total_rachmat": 45,
      "phone": "0123456789"
    },
    "rating": 4.3,
    "ratings_count": 25,
    "recent_ratings": [
      {
        "id": 1,
        "rating": 5,
        "comment": "تصميم رائع وجودة ممتازة",
        "user_name": "فاطمة أحمد",
        "created_at": "2024-01-14T15:20:00.000000Z"
      }
    ],
    "sizes": ["S", "M", "L", "XL"],
    "colors": ["أسود", "أبيض", "أحمر"],
    "is_active": true,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "الرسمة غير موجودة"
}
```

### Categories

#### Get Categories with Subcategories
**GET** `/categories`

Retrieves all categories with their associated subcategories for browsing and filtering.

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "فساتين",
      "description": "تصنيف الفساتين والأزياء النسائية",
      "slug": "fesatin",
      "sub_categories": [
        {
          "id": 1,
          "name": "فساتين سهرة",
          "description": "فساتين مناسبة للمناسبات الخاصة والسهرات",
          "slug": "fesatin-sahre",
          "category_id": 1,
          "created_at": "2024-01-15T10:30:00.000000Z",
          "updated_at": "2024-01-15T10:30:00.000000Z"
        },
        {
          "id": 2,
          "name": "فساتين كاجوال",
          "description": "فساتين يومية وكاجوال",
          "slug": "fesatin-casual",
          "category_id": 1,
          "created_at": "2024-01-15T10:30:00.000000Z",
          "updated_at": "2024-01-15T10:30:00.000000Z"
        }
      ],
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    },
    {
      "id": 2,
      "name": "قمصان",
      "description": "تصنيف القمصان والبلوزات",
      "slug": "gumsan",
      "sub_categories": [
        {
          "id": 3,
          "name": "قمصان رسمية",
          "description": "قمصان مناسبة للعمل والمناسبات الرسمية",
          "slug": "gumsan-rasmia",
          "category_id": 2,
          "created_at": "2024-01-15T10:30:00.000000Z",
          "updated_at": "2024-01-15T10:30:00.000000Z"
        }
      ],
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    }
  ]
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "فشل في جلب التصنيفات",
  "error": "Internal server error details"
}
```

### Admin Payment Information

#### Get Payment Information
**GET** `/admin-payment-info`

Retrieves public payment information for the mobile app.

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "payment_method": "ccp",
      "account_number": "0021000123456789",
      "account_name": "شركة رسمات",
      "additional_info": "يرجى كتابة رقم الطلب في ملاحظات التحويل",
      "is_active": true
    },
    {
      "id": 2,
      "payment_method": "baridimob",
      "account_number": "0021001987654321",
      "account_name": "شركة رسمات",
      "additional_info": null,
      "is_active": true
    }
  ]
}
```

---

## Protected Endpoints (JWT Required)

All endpoints in this section require `Authorization: Bearer {token}` header.

### Authentication Management

#### Logout
**POST** `/auth/logout`

Invalidates the current JWT token.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "تم تسجيل الخروج بنجاح"
}
```

#### Refresh Token
**POST** `/auth/refresh`

Refreshes the JWT token and returns a new one.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "تم تحديث الرمز المميز بنجاح",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

#### Get Current User
**GET** `/auth/me`

Retrieves the current authenticated user's information.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "phone": "0123456789",
    "user_type": "client",
    "email_verified_at": "2024-01-15T11:00:00.000000Z",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

#### Update Profile
**PUT** `/auth/profile`

Updates the current user's profile information.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "أحمد محمد علي",
  "phone": "0123456788",
  "current_password": "currentpassword",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `phone`: required, string, max:20, unique:users,phone,{user_id}
- `current_password`: required_with:password
- `password`: nullable, string, min:8, confirmed
- `password_confirmation`: required_with:password

**Success Response (200):**
```json
{
  "success": true,
  "message": "تم تحديث الملف الشخصي بنجاح",
  "data": {
    "id": 1,
    "name": "أحمد محمد علي",
    "email": "ahmed@example.com",
    "phone": "0123456788",
    "user_type": "client",
    "updated_at": "2024-01-15T12:00:00.000000Z"
  }
}
```

### Orders

#### Create Order
**POST** `/orders`

Creates a new order for a rachma.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
rachma_id: 1
size: "M"
color: "أحمر"
quantity: 2
total_amount: 400.00
delivery_address: "شارع الجامعة، المدينة، الولاية"
phone: "0123456789"
notes: "ملاحظات خاصة للطلب"
reference_images[]: [file1.jpg, file2.jpg] (optional files)
```

**Validation Rules:**
- `rachma_id`: required, exists:rachmat,id
- `size`: required, string, max:10
- `color`: required, string, max:50
- `quantity`: required, integer, min:1, max:10
- `total_amount`: required, numeric, min:0
- `delivery_address`: required, string, max:500
- `phone`: required, string, max:20
- `notes`: nullable, string, max:1000
- `reference_images.*`: nullable, file, mimes:jpg,jpeg,png, max:5120

**Success Response (201):**
```json
{
  "success": true,
  "message": "تم إنشاء الطلب بنجاح",
  "data": {
    "id": 1,
    "rachma": {
      "id": 1,
      "title": "فستان سهرة أنيق",
      "price": 200.00
    },
    "size": "M",
    "color": "أحمر",
    "quantity": 2,
    "total_amount": 400.00,
    "status": "pending",
    "delivery_address": "شارع الجامعة، المدينة، الولاية",
    "phone": "0123456789",
    "notes": "ملاحظات خاصة للطلب",
    "reference_images": [
      {
        "id": 1,
        "image_path": "https://example.com/uploads/orders/ref1.jpg"
      }
    ],
    "created_at": "2024-01-15T14:30:00.000000Z"
  }
}
```

#### Get Order Details
**GET** `/orders/{id}`

Retrieves detailed information about a specific order.

**Headers:**
```
Authorization: Bearer {token}
```

**Path Parameters:**
- `id`: integer (required) - Order ID

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "rachma": {
      "id": 1,
      "title": "فستان سهرة أنيق",
      "price": 200.00,
      "main_image": "https://example.com/images/dress1.jpg",
      "designer": {
        "id": 1,
        "store_name": "متجر الأناقة"
      }
    },
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "phone": "0123456789"
    },
    "size": "M",
    "color": "أحمر",
    "quantity": 2,
    "total_amount": 400.00,
    "status": "pending",
    "delivery_address": "شارع الجامعة، المدينة، الولاية",
    "phone": "0123456789",
    "notes": "ملاحظات خاصة للطلب",
    "reference_images": [
      {
        "id": 1,
        "image_path": "https://example.com/uploads/orders/ref1.jpg"
      }
    ],
    "admin_notes": null,
    "created_at": "2024-01-15T14:30:00.000000Z",
    "updated_at": "2024-01-15T14:30:00.000000Z"
  }
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "غير مصرح لك بعرض هذا الطلب"
}
```

#### Get My Orders
**GET** `/my-orders`

Retrieves paginated list of current user's orders.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`: integer (default: 1)
- `per_page`: integer (default: 15, max: 50)
- `status`: string (pending, processing, completed, cancelled)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "rachma": {
          "id": 1,
          "title": "فستان سهرة أنيق",
          "main_image": "https://example.com/images/dress1.jpg"
        },
        "quantity": 2,
        "total_amount": 400.00,
        "status": "pending",
        "created_at": "2024-01-15T14:30:00.000000Z"
      }
    ],
    "links": {
      "first": "...",
      "last": "...",
      "prev": null,
      "next": "..."
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 3,
      "per_page": 15,
      "to": 15,
      "total": 42
    }
  }
}
```

### Ratings

#### Submit Rating
**POST** `/ratings`

Submits a rating and review for a rachma or designer.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "target_type": "rachma",
  "target_id": 1,
  "rating": 5,
  "comment": "تصميم رائع وجودة ممتازة، أنصح بالشراء"
}
```

**Validation Rules:**
- `target_type`: required, in:rachma,designer
- `target_id`: required, integer, exists:{target_type}s,id
- `rating`: required, integer, min:1, max:5
- `comment`: nullable, string, max:1000

**Success Response (201):**
```json
{
  "success": true,
  "message": "تم إضافة التقييم بنجاح",
  "data": {
    "id": 1,
    "target_type": "rachma",
    "target_id": 1,
    "rating": 5,
    "comment": "تصميم رائع وجودة ممتازة، أنصح بالشراء",
    "user": {
      "id": 1,
      "name": "أحمد محمد"
    },
    "created_at": "2024-01-15T16:00:00.000000Z"
  }
}
```

**Error Response (422) - Duplicate Rating:**
```json
{
  "success": false,
  "message": "لقد قمت بتقييم هذا العنصر مسبقاً"
}
```

#### Get Ratings
**GET** `/ratings/{targetType}/{targetId}`

Retrieves paginated ratings for a specific rachma or designer.

**Headers:**
```
Authorization: Bearer {token}
```

**Path Parameters:**
- `targetType`: string (required) - "rachma" or "designer"
- `targetId`: integer (required) - Target ID

**Query Parameters:**
- `page`: integer (default: 1)
- `per_page`: integer (default: 15, max: 50)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "average_rating": 4.3,
    "total_ratings": 25,
    "rating_breakdown": {
      "5": 12,
      "4": 8,
      "3": 3,
      "2": 1,
      "1": 1
    },
    "ratings": {
      "data": [
        {
          "id": 1,
          "rating": 5,
          "comment": "تصميم رائع وجودة ممتازة",
          "user": {
            "id": 1,
            "name": "أحمد محمد"
          },
          "created_at": "2024-01-15T16:00:00.000000Z"
        }
      ],
      "links": {...},
      "meta": {...}
    }
  }
}
```

---

## File Upload Guidelines

### Supported File Types
- **Images**: JPG, JPEG, PNG
- **Maximum file size**: 5MB per file
- **Maximum files per request**: 5 files

### File Naming
- Files are automatically renamed with unique identifiers
- Original file extensions are preserved
- Files are stored in organized directory structure

### Security
- All uploaded files are scanned for malicious content
- File type validation is performed server-side
- Files are stored outside the web root for security

---

## Localization

- All API responses are in Arabic (العربية)
- Error messages are localized in Arabic
- Date formats follow Arabic locale standards
- Numeric formats use Arabic numerals where appropriate

---

## Versioning

- Current API version: v1
- Version is implicit in the base URL structure
- Future versions will be accessible via `/api/v2/` etc.
- Backward compatibility is maintained for at least 6 months

---

## Support and Contact

For API support and technical questions:
- **Email**: api-support@rachmat.com
- **Documentation**: [API Documentation Portal]
- **Status Page**: [API Status Page]

---

*Last updated: January 2024*
*API Version: 1.0*