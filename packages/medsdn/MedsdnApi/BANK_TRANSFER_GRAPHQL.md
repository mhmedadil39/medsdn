# Bank Transfer GraphQL API Documentation

Complete GraphQL API reference for bank transfer payment method integration.

## Table of Contents

- [Authentication](#authentication)
- [Queries](#queries)
  - [Get Configuration](#get-configuration)
  - [Get Customer Payments](#get-customer-payments)
  - [Get Payment Details](#get-payment-details)
  - [Get Statistics](#get-statistics)
- [Mutations](#mutations)
  - [Upload Payment Proof](#upload-payment-proof)
- [Types](#types)
- [Examples](#examples)

---

## Authentication

Most operations require customer authentication using Bearer token:

```json
# HTTP Headers
{
  "Authorization": "Bearer YOUR_TOKEN_HERE"
}
```

### Getting a Token

```graphql
mutation CustomerLogin {
  customerLogin(input: {
    email: "customer@example.com"
    password: "password123"
  }) {
    token
    customer {
      id
      firstName
      lastName
      email
    }
  }
}
```

---

## Queries

### Get Configuration

Get bank transfer payment method configuration including bank accounts and upload requirements.

**Authentication:** Not required

**Query:**

```graphql
query GetBankTransferConfig {
  bankTransferConfig {
    success
    message
    data {
      title
      description
      bankAccounts {
        bankName
        branchName
        accountHolder
        accountNumber
        iban
      }
      instructions
      maxFileSize
      allowedFileTypes
    }
  }
}
```

**Response:**

```json
{
  "data": {
    "bankTransferConfig": {
      "success": true,
      "message": null,
      "data": {
        "title": "Bank Transfer",
        "description": "Pay securely via bank transfer",
        "bankAccounts": [
          {
            "bankName": "National Bank",
            "branchName": "Main Branch",
            "accountHolder": "MedSDN Store",
            "accountNumber": "1234567890",
            "iban": "SA1234567890123456789012"
          }
        ],
        "instructions": "Please transfer the exact order amount to one of the bank accounts listed above...",
        "maxFileSize": "4MB",
        "allowedFileTypes": ["jpg", "jpeg", "png", "webp", "pdf"]
      }
    }
  }
}
```

---

### Get Customer Payments

Get paginated list of authenticated customer's bank transfer payments.

**Authentication:** Required

**Query:**

```graphql
query GetCustomerPayments($page: Int, $perPage: Int) {
  bankTransferPayments(page: $page, itemsPerPage: $perPage) {
    edges {
      node {
        id
        orderId
        order {
          id
          incrementId
          status
          grandTotal
          grandTotalFormatted
          createdAt
        }
        customerId
        methodCode
        transactionReference
        status
        statusLabel
        reviewedBy
        reviewedAt
        adminNote
        createdAt
        updatedAt
        isPending
        isApproved
        isRejected
      }
    }
    pageInfo {
      hasNextPage
      hasPreviousPage
      startCursor
      endCursor
    }
    totalCount
  }
}
```

**Variables:**

```json
{
  "page": 1,
  "perPage": 15
}
```

**Response:**

```json
{
  "data": {
    "bankTransferPayments": {
      "edges": [
        {
          "node": {
            "id": 45,
            "orderId": 123,
            "order": {
              "id": 123,
              "incrementId": "000000123",
              "status": "pending",
              "grandTotal": 150.00,
              "grandTotalFormatted": "$150.00",
              "createdAt": "2024-03-15T10:30:00Z"
            },
            "customerId": 10,
            "methodCode": "banktransfer",
            "transactionReference": "TXN123456",
            "status": "pending",
            "statusLabel": "Pending Review",
            "reviewedBy": null,
            "reviewedAt": null,
            "adminNote": null,
            "createdAt": "2024-03-15T10:30:00Z",
            "updatedAt": "2024-03-15T10:30:00Z",
            "isPending": true,
            "isApproved": false,
            "isRejected": false
          }
        }
      ],
      "pageInfo": {
        "hasNextPage": true,
        "hasPreviousPage": false,
        "startCursor": "YXJyYXljb25uZWN0aW9uOjA=",
        "endCursor": "YXJyYXljb25uZWN0aW9uOjE0"
      },
      "totalCount": 42
    }
  }
}
```

---

### Get Payment Details

Get detailed information about a specific payment.

**Authentication:** Required

**Query:**

```graphql
query GetPaymentDetails($id: ID!) {
  bankTransferPayment(id: $id) {
    id
    orderId
    order {
      id
      incrementId
      status
      grandTotal
      grandTotalFormatted
      createdAt
    }
    customerId
    methodCode
    transactionReference
    status
    statusLabel
    reviewedBy
    reviewer {
      id
      name
    }
    reviewedAt
    adminNote
    createdAt
    updatedAt
    isPending
    isApproved
    isRejected
  }
}
```

**Variables:**

```json
{
  "id": "45"
}
```

**Response:**

```json
{
  "data": {
    "bankTransferPayment": {
      "id": 45,
      "orderId": 123,
      "order": {
        "id": 123,
        "incrementId": "000000123",
        "status": "processing",
        "grandTotal": 150.00,
        "grandTotalFormatted": "$150.00",
        "createdAt": "2024-03-15T10:30:00Z"
      },
      "customerId": 10,
      "methodCode": "banktransfer",
      "transactionReference": "TXN123456",
      "status": "approved",
      "statusLabel": "Approved",
      "reviewedBy": 5,
      "reviewer": {
        "id": 5,
        "name": "Admin User"
      },
      "reviewedAt": "2024-03-15T11:00:00Z",
      "adminNote": "Payment verified successfully",
      "createdAt": "2024-03-15T10:30:00Z",
      "updatedAt": "2024-03-15T11:00:00Z",
      "isPending": false,
      "isApproved": true,
      "isRejected": false
    }
  }
}
```

---

### Get Statistics

Get payment statistics for the authenticated customer.

**Authentication:** Required

**Query:**

```graphql
query GetPaymentStatistics {
  bankTransferStatistics {
    success
    data {
      total
      pending
      approved
      rejected
    }
  }
}
```

**Response:**

```json
{
  "data": {
    "bankTransferStatistics": {
      "success": true,
      "data": {
        "total": 42,
        "pending": 5,
        "approved": 35,
        "rejected": 2
      }
    }
  }
}
```

---

## Mutations

### Upload Payment Proof

Upload payment proof and create order.

**Authentication:** Optional (uses cart token)

**Mutation:**

```graphql
mutation UploadPaymentProof($input: BankTransferPaymentInput!) {
  uploadBankTransferPayment(input: $input) {
    success
    message
    data {
      order {
        id
        incrementId
        status
        grandTotal
        createdAt
      }
      payment {
        id
        orderId
        methodCode
        transactionReference
        status
        statusLabel
        createdAt
        isPending
        isApproved
        isRejected
      }
    }
  }
}
```

**Variables:**

```json
{
  "input": {
    "paymentProof": "base64_encoded_file_or_upload",
    "transactionReference": "TXN123456",
    "cartToken": "your_cart_token_here"
  }
}
```

**Note:** For file uploads in GraphQL, you'll need to use multipart form data or base64 encoding depending on your GraphQL client implementation.

**Response:**

```json
{
  "data": {
    "uploadBankTransferPayment": {
      "success": true,
      "message": "Order created successfully. Your payment is under review.",
      "data": {
        "order": {
          "id": 123,
          "incrementId": "000000123",
          "status": "pending",
          "grandTotal": 150.00,
          "createdAt": "2024-03-15T10:30:00Z"
        },
        "payment": {
          "id": 45,
          "orderId": 123,
          "methodCode": "banktransfer",
          "transactionReference": "TXN123456",
          "status": "pending",
          "statusLabel": "Pending Review",
          "createdAt": "2024-03-15T10:30:00Z",
          "isPending": true,
          "isApproved": false,
          "isRejected": false
        }
      }
    }
  }
}
```

**Error Response:**

```json
{
  "errors": [
    {
      "message": "The payment proof field is required.",
      "extensions": {
        "category": "validation"
      }
    }
  ]
}
```

---

## Types

### BankTransferConfigOutput

```graphql
type BankTransferConfigOutput {
  success: Boolean!
  message: String
  data: BankTransferConfigData
}

type BankTransferConfigData {
  title: String!
  description: String
  bankAccounts: [BankAccount!]!
  instructions: String
  maxFileSize: String!
  allowedFileTypes: [String!]!
}

type BankAccount {
  bankName: String!
  branchName: String
  accountHolder: String!
  accountNumber: String!
  iban: String
}
```

### BankTransferPaymentOutput

```graphql
type BankTransferPaymentOutput {
  id: ID!
  success: Boolean
  message: String
  orderId: Int
  order: Order
  customerId: Int
  methodCode: String
  transactionReference: String
  status: String!
  statusLabel: String!
  reviewedBy: Int
  reviewer: Admin
  reviewedAt: String
  adminNote: String
  createdAt: String!
  updatedAt: String!
  isPending: Boolean!
  isApproved: Boolean!
  isRejected: Boolean!
  data: BankTransferPaymentData
}

type Order {
  id: Int!
  incrementId: String!
  status: String!
  grandTotal: Float!
  grandTotalFormatted: String!
  createdAt: String!
}

type Admin {
  id: Int!
  name: String!
}

type BankTransferPaymentData {
  order: Order
  payment: PaymentDetails
}

type PaymentDetails {
  id: Int!
  orderId: Int!
  methodCode: String!
  transactionReference: String
  status: String!
  statusLabel: String!
  createdAt: String!
  isPending: Boolean!
  isApproved: Boolean!
  isRejected: Boolean!
}
```

### BankTransferStatisticsOutput

```graphql
type BankTransferStatisticsOutput {
  success: Boolean!
  data: BankTransferStatistics
}

type BankTransferStatistics {
  total: Int!
  pending: Int!
  approved: Int!
  rejected: Int!
}
```

### BankTransferPaymentInput

```graphql
input BankTransferPaymentInput {
  paymentProof: Upload!
  transactionReference: String
  cartToken: String!
}
```

---

## Examples

### Complete Checkout Flow with Bank Transfer

```graphql
# Step 1: Get bank transfer configuration
query GetConfig {
  bankTransferConfig {
    success
    data {
      title
      bankAccounts {
        bankName
        accountNumber
        iban
      }
      instructions
    }
  }
}

# Step 2: Select bank transfer as payment method
mutation SelectPaymentMethod($cartToken: String!) {
  saveCheckoutPaymentMethod(input: {
    cartToken: $cartToken
    paymentMethod: "banktransfer"
  }) {
    success
    message
  }
}

# Step 3: Upload payment proof and create order
mutation UploadProof($input: BankTransferPaymentInput!) {
  uploadBankTransferPayment(input: $input) {
    success
    message
    data {
      order {
        id
        incrementId
        status
      }
      payment {
        id
        status
        statusLabel
      }
    }
  }
}

# Step 4: Check payment status
query CheckStatus($id: ID!) {
  bankTransferPayment(id: $id) {
    status
    statusLabel
    isPending
    isApproved
    isRejected
    adminNote
  }
}
```

### Get Payment History

```graphql
query GetPaymentHistory {
  bankTransferPayments(itemsPerPage: 10) {
    edges {
      node {
        id
        order {
          incrementId
          grandTotalFormatted
        }
        transactionReference
        status
        statusLabel
        createdAt
      }
    }
    totalCount
  }
}
```

### Get Dashboard Statistics

```graphql
query GetDashboard {
  # Get payment statistics
  bankTransferStatistics {
    data {
      total
      pending
      approved
      rejected
    }
  }
  
  # Get recent payments
  bankTransferPayments(itemsPerPage: 5) {
    edges {
      node {
        id
        order {
          incrementId
        }
        status
        createdAt
      }
    }
  }
}
```

---

## Error Handling

### Common Error Codes

| Code | Message | Description |
|------|---------|-------------|
| AUTHENTICATION_REQUIRED | Unauthenticated | Customer authentication required |
| INVALID_TOKEN | Invalid cart token | Cart token is invalid or expired |
| VALIDATION_ERROR | Validation failed | Input validation error |
| PAYMENT_METHOD_NOT_AVAILABLE | Payment method not available | Bank transfer is disabled or not configured |
| RESOURCE_NOT_FOUND | Payment not found | Payment ID does not exist or doesn't belong to customer |
| OPERATION_FAILED | Operation failed | Server error occurred |

### Error Response Format

```json
{
  "errors": [
    {
      "message": "Error description",
      "extensions": {
        "category": "validation",
        "code": "VALIDATION_ERROR"
      },
      "locations": [
        {
          "line": 2,
          "column": 3
        }
      ],
      "path": ["uploadBankTransferPayment"]
    }
  ]
}
```

---

## Rate Limiting

Upload mutation is rate-limited to prevent abuse:

- **Limit:** 5 uploads per minute per user
- **Response:** Error with code `RATE_LIMIT_EXCEEDED`

---

## Best Practices

1. **Always check configuration first** before showing bank transfer option
2. **Validate file size client-side** before upload (max 4MB)
3. **Show clear instructions** from configuration to customers
4. **Poll payment status** periodically after upload
5. **Handle errors gracefully** with user-friendly messages
6. **Use pagination** for payment lists
7. **Cache configuration** to reduce API calls

---

## Testing with GraphQL Playground

Access GraphQL Playground at:
```
https://your-domain.com/graphql
```

Set authentication header:
```json
{
  "Authorization": "Bearer YOUR_TOKEN"
}
```

---

## Support

For GraphQL API support:
- Email: api-support@webkul.com
- Documentation: https://medsdn.com/docs/graphql
- GitHub Issues: https://github.com/webkul/medsdn/issues

---

**Last Updated:** March 2024  
**API Version:** 1.0.0
