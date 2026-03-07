# Bank Transfer API Documentation

Complete API reference for mobile applications and headless implementations.

## Base URL

```
https://your-domain.com/api/bank-transfer
```

## Authentication

Most endpoints require Laravel Sanctum authentication. Include the bearer token in the Authorization header:

```
Authorization: Bearer {your-token}
```

### Getting a Token

Use the standard MedSDN customer authentication endpoint:

```http
POST /api/customer/login
Content-Type: application/json

{
  "email": "customer@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "token": "1|abc123...",
  "customer": {
    "id": 1,
    "name": "John Doe",
    "email": "customer@example.com"
  }
}
```

## Endpoints

### 1. Get Configuration

Get bank transfer payment method configuration including bank accounts and upload requirements.

**Endpoint:** `GET /api/bank-transfer/config`

**Authentication:** Not required

**Response:**

```json
{
  "success": true,
  "data": {
    "title": "Bank Transfer",
    "description": "Pay securely via bank transfer",
    "bank_accounts": [
      {
        "bank_name": "National Bank",
        "branch_name": "Main Branch",
        "account_holder": "MedSDN Store",
        "account_number": "1234567890",
        "iban": "SA1234567890123456789012"
      }
    ],
    "instructions": "Please transfer the exact order amount...",
    "max_file_size": "4MB",
    "allowed_file_types": ["jpg", "jpeg", "png", "webp", "pdf"]
  }
}
```

**Error Responses:**

```json
{
  "success": false,
  "message": "Payment method not available"
}
```

**Status Codes:**
- `200` - Success
- `404` - Payment method not available or not configured
- `500` - Server error

---

### 2. Upload Payment Proof

Upload payment proof and create order. This endpoint processes the cart and creates an order with pending payment status.

**Endpoint:** `POST /api/bank-transfer/upload`

**Authentication:** Not required (uses cart session)

**Rate Limit:** 5 requests per minute per user

**Content-Type:** `multipart/form-data`

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| payment_proof | File | Yes | Payment receipt (JPG, PNG, WEBP, PDF, max 4MB) |
| transaction_reference | String | No | Bank transaction reference number (max 255 chars) |

**cURL Example:**

```bash
curl -X POST https://your-domain.com/api/bank-transfer/upload \
  -H "Authorization: Bearer {token}" \
  -F "payment_proof=@/path/to/receipt.jpg" \
  -F "transaction_reference=TXN123456"
```

**JavaScript Example:**

```javascript
const formData = new FormData();
formData.append('payment_proof', fileInput.files[0]);
formData.append('transaction_reference', 'TXN123456');

const response = await fetch('https://your-domain.com/api/bank-transfer/upload', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`
  },
  body: formData
});

const result = await response.json();
```

**Success Response (201):**

```json
{
  "success": true,
  "message": "Order created successfully. Your payment is under review.",
  "data": {
    "order": {
      "id": 123,
      "increment_id": "000000123",
      "status": "pending",
      "grand_total": 150.00,
      "created_at": "2024-03-15T10:30:00Z"
    },
    "payment": {
      "id": 45,
      "order_id": 123,
      "method_code": "banktransfer",
      "transaction_reference": "TXN123456",
      "status": "pending",
      "status_label": "Pending Review",
      "created_at": "2024-03-15T10:30:00Z",
      "is_pending": true,
      "is_approved": false,
      "is_rejected": false
    }
  }
}
```

**Error Responses:**

```json
// Validation Error (422)
{
  "success": false,
  "message": "The payment proof field is required.",
  "errors": {
    "payment_proof": ["The payment proof field is required."]
  }
}

// File Too Large (422)
{
  "success": false,
  "message": "The file size must not exceed 4MB.",
  "errors": {
    "payment_proof": ["The file size must not exceed 4MB."]
  }
}

// Invalid File Type (422)
{
  "success": false,
  "message": "The file must be a file of type: jpg, jpeg, png, webp, pdf.",
  "errors": {
    "payment_proof": ["The file must be a file of type: jpg, jpeg, png, webp, pdf."]
  }
}

// Rate Limit Exceeded (429)
{
  "message": "Too Many Attempts."
}

// Server Error (500)
{
  "success": false,
  "message": "Failed to create order. Please try again."
}
```

**Status Codes:**
- `201` - Order created successfully
- `422` - Validation error
- `429` - Rate limit exceeded
- `500` - Server error

---

### 3. Get Customer Payments

Get paginated list of authenticated customer's bank transfer payments.

**Endpoint:** `GET /api/bank-transfer/payments`

**Authentication:** Required (Sanctum)

**Query Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| per_page | Integer | 15 | Number of items per page (1-100) |

**cURL Example:**

```bash
curl -X GET "https://your-domain.com/api/bank-transfer/payments?per_page=20" \
  -H "Authorization: Bearer {token}"
```

**Success Response (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": 45,
      "order_id": 123,
      "order": {
        "id": 123,
        "increment_id": "000000123",
        "status": "pending",
        "grand_total": 150.00,
        "grand_total_formatted": "$150.00",
        "created_at": "2024-03-15T10:30:00Z"
      },
      "customer_id": 10,
      "method_code": "banktransfer",
      "transaction_reference": "TXN123456",
      "status": "pending",
      "status_label": "Pending Review",
      "reviewed_by": null,
      "reviewed_at": null,
      "admin_note": null,
      "created_at": "2024-03-15T10:30:00Z",
      "updated_at": "2024-03-15T10:30:00Z",
      "is_pending": true,
      "is_approved": false,
      "is_rejected": false
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

**Error Responses:**

```json
// Unauthenticated (401)
{
  "success": false,
  "message": "Unauthenticated."
}

// Server Error (500)
{
  "success": false,
  "message": "Failed to fetch payments. Please try again."
}
```

**Status Codes:**
- `200` - Success
- `401` - Unauthenticated
- `500` - Server error

---

### 4. Get Payment Details

Get detailed information about a specific payment.

**Endpoint:** `GET /api/bank-transfer/payments/{id}`

**Authentication:** Required (Sanctum)

**Path Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| id | Integer | Payment ID |

**cURL Example:**

```bash
curl -X GET "https://your-domain.com/api/bank-transfer/payments/45" \
  -H "Authorization: Bearer {token}"
```

**Success Response (200):**

```json
{
  "success": true,
  "data": {
    "id": 45,
    "order_id": 123,
    "order": {
      "id": 123,
      "increment_id": "000000123",
      "status": "processing",
      "grand_total": 150.00,
      "grand_total_formatted": "$150.00",
      "created_at": "2024-03-15T10:30:00Z"
    },
    "customer_id": 10,
    "method_code": "banktransfer",
    "transaction_reference": "TXN123456",
    "status": "approved",
    "status_label": "Approved",
    "reviewed_by": 5,
    "reviewer": {
      "id": 5,
      "name": "Admin User"
    },
    "reviewed_at": "2024-03-15T11:00:00Z",
    "admin_note": "Payment verified successfully",
    "created_at": "2024-03-15T10:30:00Z",
    "updated_at": "2024-03-15T11:00:00Z",
    "is_pending": false,
    "is_approved": true,
    "is_rejected": false
  }
}
```

**Error Responses:**

```json
// Not Found (404)
{
  "success": false,
  "message": "Payment not found."
}

// Unauthenticated (401)
{
  "success": false,
  "message": "Unauthenticated."
}
```

**Status Codes:**
- `200` - Success
- `401` - Unauthenticated
- `404` - Payment not found
- `500` - Server error

---

### 5. Get Payment Statistics

Get payment statistics for the authenticated customer.

**Endpoint:** `GET /api/bank-transfer/statistics`

**Authentication:** Required (Sanctum)

**cURL Example:**

```bash
curl -X GET "https://your-domain.com/api/bank-transfer/statistics" \
  -H "Authorization: Bearer {token}"
```

**Success Response (200):**

```json
{
  "success": true,
  "data": {
    "total": 42,
    "pending": 5,
    "approved": 35,
    "rejected": 2
  }
}
```

**Error Responses:**

```json
// Unauthenticated (401)
{
  "success": false,
  "message": "Unauthenticated."
}
```

**Status Codes:**
- `200` - Success
- `401` - Unauthenticated
- `500` - Server error

---

## Payment Status Flow

```
pending → approved (order status updated to processing)
        ↘ rejected (order remains pending, customer notified)
```

### Status Descriptions

| Status | Description | Customer Action |
|--------|-------------|-----------------|
| pending | Payment under admin review | Wait for review (usually 24 hours) |
| approved | Payment verified and accepted | Order will be processed |
| rejected | Payment rejected by admin | Check rejection reason and resubmit |

---

## Error Handling

All API responses follow a consistent format:

**Success Response:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional success message"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }  // Optional validation errors
}
```

### Common HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 401 | Unauthorized | Authentication required or failed |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error occurred |

---

## Mobile App Integration Guide

### Flutter Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class BankTransferAPI {
  final String baseUrl = 'https://your-domain.com/api/bank-transfer';
  final String token;

  BankTransferAPI(this.token);

  // Get configuration
  Future<Map<String, dynamic>> getConfig() async {
    final response = await http.get(
      Uri.parse('$baseUrl/config'),
    );
    return json.decode(response.body);
  }

  // Upload payment proof
  Future<Map<String, dynamic>> uploadPaymentProof(
    File file,
    String? transactionRef,
  ) async {
    var request = http.MultipartRequest(
      'POST',
      Uri.parse('$baseUrl/upload'),
    );
    
    request.headers['Authorization'] = 'Bearer $token';
    request.files.add(await http.MultipartFile.fromPath(
      'payment_proof',
      file.path,
    ));
    
    if (transactionRef != null) {
      request.fields['transaction_reference'] = transactionRef;
    }

    final response = await request.send();
    final responseBody = await response.stream.bytesToString();
    return json.decode(responseBody);
  }

  // Get payments
  Future<Map<String, dynamic>> getPayments({int perPage = 15}) async {
    final response = await http.get(
      Uri.parse('$baseUrl/payments?per_page=$perPage'),
      headers: {'Authorization': 'Bearer $token'},
    );
    return json.decode(response.body);
  }

  // Get payment details
  Future<Map<String, dynamic>> getPayment(int id) async {
    final response = await http.get(
      Uri.parse('$baseUrl/payments/$id'),
      headers: {'Authorization': 'Bearer $token'},
    );
    return json.decode(response.body);
  }

  // Get statistics
  Future<Map<String, dynamic>> getStatistics() async {
    final response = await http.get(
      Uri.parse('$baseUrl/statistics'),
      headers: {'Authorization': 'Bearer $token'},
    );
    return json.decode(response.body);
  }
}
```

### React Native Example

```javascript
import axios from 'axios';

const API_BASE_URL = 'https://your-domain.com/api/bank-transfer';

class BankTransferAPI {
  constructor(token) {
    this.token = token;
    this.client = axios.create({
      baseURL: API_BASE_URL,
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
  }

  // Get configuration
  async getConfig() {
    const response = await axios.get(`${API_BASE_URL}/config`);
    return response.data;
  }

  // Upload payment proof
  async uploadPaymentProof(file, transactionRef = null) {
    const formData = new FormData();
    formData.append('payment_proof', {
      uri: file.uri,
      type: file.type,
      name: file.name
    });
    
    if (transactionRef) {
      formData.append('transaction_reference', transactionRef);
    }

    const response = await this.client.post('/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    return response.data;
  }

  // Get payments
  async getPayments(perPage = 15) {
    const response = await this.client.get('/payments', {
      params: { per_page: perPage }
    });
    return response.data;
  }

  // Get payment details
  async getPayment(id) {
    const response = await this.client.get(`/payments/${id}`);
    return response.data;
  }

  // Get statistics
  async getStatistics() {
    const response = await this.client.get('/statistics');
    return response.data;
  }
}

export default BankTransferAPI;
```

---

## Testing with Postman

### Import Collection

Create a new Postman collection with the following structure:

1. **Environment Variables:**
   - `base_url`: `https://your-domain.com`
   - `token`: Your Sanctum token

2. **Collection Authorization:**
   - Type: Bearer Token
   - Token: `{{token}}`

3. **Requests:**
   - GET Config
   - POST Upload Payment Proof
   - GET Payments List
   - GET Payment Details
   - GET Statistics

### Sample Postman Request

```json
{
  "info": {
    "name": "Bank Transfer API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Get Config",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/api/bank-transfer/config"
      }
    },
    {
      "name": "Upload Payment Proof",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/api/bank-transfer/upload",
        "body": {
          "mode": "formdata",
          "formdata": [
            {
              "key": "payment_proof",
              "type": "file",
              "src": "/path/to/receipt.jpg"
            },
            {
              "key": "transaction_reference",
              "value": "TXN123456",
              "type": "text"
            }
          ]
        }
      }
    }
  ]
}
```

---

## Rate Limiting

The upload endpoint is rate-limited to prevent abuse:

- **Limit:** 5 uploads per minute per user
- **Response:** HTTP 429 when exceeded
- **Reset:** Automatically after 1 minute

**Rate Limit Headers:**

```
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 4
Retry-After: 60
```

---

## Localization

All API responses support multi-language based on the `Accept-Language` header:

```
Accept-Language: ar
```

Supported languages:
- `en` - English (default)
- `ar` - Arabic

**Example:**

```bash
curl -X GET "https://your-domain.com/api/bank-transfer/config" \
  -H "Accept-Language: ar"
```

---

## Security Best Practices

### For Mobile Apps

1. **Store tokens securely** using platform-specific secure storage
2. **Use HTTPS only** for all API requests
3. **Validate file types** before upload
4. **Handle token expiration** gracefully
5. **Implement retry logic** with exponential backoff
6. **Log errors** for debugging but never log tokens

### File Upload Security

1. **Client-side validation:**
   - Check file size before upload
   - Validate file extension
   - Preview image before upload

2. **Server-side validation:**
   - MIME type verification
   - Content inspection
   - Malware scanning (recommended)

3. **Network security:**
   - Use HTTPS/TLS
   - Implement certificate pinning
   - Validate SSL certificates

---

## Webhooks (Future Feature)

Planned webhook support for real-time payment status updates:

```json
{
  "event": "payment.approved",
  "data": {
    "payment_id": 45,
    "order_id": 123,
    "status": "approved",
    "reviewed_at": "2024-03-15T11:00:00Z"
  }
}
```

---

## Support

For API support and questions:
- Email: api-support@webkul.com
- Documentation: https://medsdn.com/docs/api
- GitHub Issues: https://github.com/webkul/medsdn/issues

---

**Last Updated:** March 2026  
**API Version:** 1.0.0
