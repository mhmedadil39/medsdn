# دليل REST API للتحويل البنكي - مطوري Flutter

## نظرة عامة

هذا الدليل الشامل لمطوري Flutter لاستخدام REST API الخاص بطريقة الدفع بالتحويل البنكي في MedSDN.

## المحتويات

1. [الإعداد الأولي](#الإعداد-الأولي)
2. [المصادقة](#المصادقة)
3. [نقاط API](#نقاط-api)
4. [نماذج البيانات](#نماذج-البيانات)
5. [أمثلة كاملة](#أمثلة-كاملة)

---

## الإعداد الأولي

### 1. إضافة التبعيات

أضف إلى `pubspec.yaml`:

```yaml
dependencies:
  http: ^1.1.0
  dio: ^5.4.0  # اختياري - بديل لـ http
  image_picker: ^1.0.4  # لاختيار الصور
  file_picker: ^6.1.1  # لاختيار الملفات
  path: ^1.8.3
  shared_preferences: ^2.2.2  # لحفظ الرمز
```

### 2. إعداد الثوابت

```dart
// lib/config/api_config.dart
class ApiConfig {
  static const String baseUrl = 'https://your-domain.com';
  static const String apiBaseUrl = '$baseUrl/api/shop/bank-transfer';
  
  // نقاط API
  static const String configEndpoint = '$apiBaseUrl/config';
  static const String uploadEndpoint = '$apiBaseUrl/upload';
  static const String paymentsEndpoint = '$apiBaseUrl/payments';
  static const String statisticsEndpoint = '$apiBaseUrl/statistics';
  
  // إعدادات
  static const int maxFileSize = 4 * 1024 * 1024; // 4MB
  static const List<String> allowedFileTypes = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
  static const Duration requestTimeout = Duration(seconds: 30);
}
```

---

## المصادقة

### حفظ واسترجاع الرمز

```dart
// lib/services/auth_service.dart
import 'package:shared_preferences/shared_preferences.dart';

class AuthService {
  static const String _tokenKey = 'auth_token';
  
  // حفظ الرمز
  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }
  
  // استرجاع الرمز
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }
  
  // حذف الرمز
  static Future<void> deleteToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }
  
  // التحقق من وجود رمز
  static Future<bool> hasToken() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }
}
```

---

## نقاط API

### 1. الحصول على الإعدادات

**لا يحتاج مصادقة**

```dart
// lib/services/bank_transfer_api.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/bank_transfer_config.dart';

class BankTransferApi {
  /// الحصول على إعدادات التحويل البنكي
  /// 
  /// Returns: BankTransferConfig
  /// Throws: Exception عند الفشل
  static Future<BankTransferConfig> getConfig() async {
    try {
      final response = await http
          .get(
            Uri.parse(ApiConfig.configEndpoint),
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
          )
          .timeout(ApiConfig.requestTimeout);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return BankTransferConfig.fromJson(data);
      } else if (response.statusCode == 404) {
        throw Exception('طريقة الدفع بالتحويل البنكي غير متاحة');
      } else {
        throw Exception('فشل في جلب الإعدادات: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('خطأ في الاتصال: $e');
    }
  }
}
```


### 2. رفع إثبات الدفع

**محدود: 5 رفعات في الدقيقة**

```dart
  /// رفع إثبات الدفع وإنشاء الطلب
  /// 
  /// Parameters:
  ///   - file: ملف إثبات الدفع
  ///   - transactionReference: رقم المعاملة (اختياري)
  /// 
  /// Returns: UploadPaymentResponse
  /// Throws: Exception عند الفشل
  static Future<UploadPaymentResponse> uploadPaymentProof({
    required File file,
    String? transactionReference,
  }) async {
    try {
      // التحقق من حجم الملف
      final fileSize = await file.length();
      if (fileSize > ApiConfig.maxFileSize) {
        throw Exception('حجم الملف يتجاوز 4MB');
      }

      // التحقق من نوع الملف
      final extension = file.path.split('.').last.toLowerCase();
      if (!ApiConfig.allowedFileTypes.contains(extension)) {
        throw Exception('نوع الملف غير مدعوم. الأنواع المسموحة: ${ApiConfig.allowedFileTypes.join(", ")}');
      }

      // إنشاء الطلب
      var request = http.MultipartRequest(
        'POST',
        Uri.parse(ApiConfig.uploadEndpoint),
      );

      // إضافة الرؤوس
      final token = await AuthService.getToken();
      request.headers.addAll({
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      });

      // إضافة الملف
      request.files.add(
        await http.MultipartFile.fromPath(
          'payment_proof',
          file.path,
        ),
      );

      // إضافة رقم المعاملة إن وجد
      if (transactionReference != null && transactionReference.isNotEmpty) {
        request.fields['transaction_reference'] = transactionReference;
      }

      // إرسال الطلب
      final streamedResponse = await request.send().timeout(
        const Duration(seconds: 60), // وقت أطول للرفع
      );

      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 201) {
        final data = json.decode(response.body);
        return UploadPaymentResponse.fromJson(data);
      } else if (response.statusCode == 422) {
        final data = json.decode(response.body);
        throw Exception(data['message'] ?? 'خطأ في التحقق من البيانات');
      } else if (response.statusCode == 429) {
        throw Exception('تم تجاوز الحد المسموح. يرجى المحاولة بعد دقيقة');
      } else {
        throw Exception('فشل رفع الملف: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('خطأ في رفع الملف: $e');
    }
  }
```

### 3. الحصول على قائمة المدفوعات

**يحتاج مصادقة**

```dart
  /// الحصول على قائمة مدفوعات العميل
  /// 
  /// Parameters:
  ///   - page: رقم الصفحة (افتراضي: 1)
  ///   - perPage: عدد العناصر في الصفحة (افتراضي: 15)
  /// 
  /// Returns: PaymentListResponse
  /// Throws: Exception عند الفشل
  static Future<PaymentListResponse> getPayments({
    int page = 1,
    int perPage = 15,
  }) async {
    try {
      final token = await AuthService.getToken();
      if (token == null) {
        throw Exception('يجب تسجيل الدخول أولاً');
      }

      final uri = Uri.parse(ApiConfig.paymentsEndpoint).replace(
        queryParameters: {
          'page': page.toString(),
          'per_page': perPage.toString(),
        },
      );

      final response = await http
          .get(
            uri,
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': 'Bearer $token',
            },
          )
          .timeout(ApiConfig.requestTimeout);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return PaymentListResponse.fromJson(data);
      } else if (response.statusCode == 401) {
        throw Exception('انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى');
      } else {
        throw Exception('فشل في جلب المدفوعات: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('خطأ في جلب المدفوعات: $e');
    }
  }
```

### 4. الحصول على تفاصيل الدفع

**يحتاج مصادقة**

```dart
  /// الحصول على تفاصيل دفع محدد
  /// 
  /// Parameters:
  ///   - paymentId: معرف الدفع
  /// 
  /// Returns: PaymentDetails
  /// Throws: Exception عند الفشل
  static Future<PaymentDetails> getPaymentDetails(int paymentId) async {
    try {
      final token = await AuthService.getToken();
      if (token == null) {
        throw Exception('يجب تسجيل الدخول أولاً');
      }

      final response = await http
          .get(
            Uri.parse('${ApiConfig.paymentsEndpoint}/$paymentId'),
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': 'Bearer $token',
            },
          )
          .timeout(ApiConfig.requestTimeout);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return PaymentDetails.fromJson(data['data']);
      } else if (response.statusCode == 404) {
        throw Exception('الدفعة غير موجودة');
      } else if (response.statusCode == 401) {
        throw Exception('انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى');
      } else {
        throw Exception('فشل في جلب تفاصيل الدفع: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('خطأ في جلب تفاصيل الدفع: $e');
    }
  }
```

### 5. الحصول على الإحصائيات

**يحتاج مصادقة**

```dart
  /// الحصول على إحصائيات المدفوعات
  /// 
  /// Returns: PaymentStatistics
  /// Throws: Exception عند الفشل
  static Future<PaymentStatistics> getStatistics() async {
    try {
      final token = await AuthService.getToken();
      if (token == null) {
        throw Exception('يجب تسجيل الدخول أولاً');
      }

      final response = await http
          .get(
            Uri.parse(ApiConfig.statisticsEndpoint),
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': 'Bearer $token',
            },
          )
          .timeout(ApiConfig.requestTimeout);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return PaymentStatistics.fromJson(data['data']);
      } else if (response.statusCode == 401) {
        throw Exception('انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى');
      } else {
        throw Exception('فشل في جلب الإحصائيات: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('خطأ في جلب الإحصائيات: $e');
    }
  }
}
```

---

## نماذج البيانات

### 1. نموذج الإعدادات

```dart
// lib/models/bank_transfer_config.dart

class BankTransferConfig {
  final bool success;
  final String? message;
  final ConfigData? data;

  BankTransferConfig({
    required this.success,
    this.message,
    this.data,
  });

  factory BankTransferConfig.fromJson(Map<String, dynamic> json) {
    return BankTransferConfig(
      success: json['success'] ?? false,
      message: json['message'],
      data: json['data'] != null ? ConfigData.fromJson(json['data']) : null,
    );
  }
}

class ConfigData {
  final String title;
  final String? description;
  final List<BankAccount> bankAccounts;
  final String? instructions;
  final String maxFileSize;
  final List<String> allowedFileTypes;

  ConfigData({
    required this.title,
    this.description,
    required this.bankAccounts,
    this.instructions,
    required this.maxFileSize,
    required this.allowedFileTypes,
  });

  factory ConfigData.fromJson(Map<String, dynamic> json) {
    return ConfigData(
      title: json['title'] ?? '',
      description: json['description'],
      bankAccounts: (json['bank_accounts'] as List?)
              ?.map((e) => BankAccount.fromJson(e))
              .toList() ??
          [],
      instructions: json['instructions'],
      maxFileSize: json['max_file_size'] ?? '4MB',
      allowedFileTypes: List<String>.from(json['allowed_file_types'] ?? []),
    );
  }
}

class BankAccount {
  final String bankName;
  final String? branchName;
  final String accountHolder;
  final String accountNumber;
  final String? iban;

  BankAccount({
    required this.bankName,
    this.branchName,
    required this.accountHolder,
    required this.accountNumber,
    this.iban,
  });

  factory BankAccount.fromJson(Map<String, dynamic> json) {
    return BankAccount(
      bankName: json['bank_name'] ?? '',
      branchName: json['branch_name'],
      accountHolder: json['account_holder'] ?? '',
      accountNumber: json['account_number'] ?? '',
      iban: json['iban'],
    );
  }
}
```

### 2. نموذج استجابة الرفع

```dart
// lib/models/upload_payment_response.dart

class UploadPaymentResponse {
  final bool success;
  final String message;
  final UploadData? data;

  UploadPaymentResponse({
    required this.success,
    required this.message,
    this.data,
  });

  factory UploadPaymentResponse.fromJson(Map<String, dynamic> json) {
    return UploadPaymentResponse(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: json['data'] != null ? UploadData.fromJson(json['data']) : null,
    );
  }
}

class UploadData {
  final OrderInfo order;
  final PaymentInfo payment;

  UploadData({
    required this.order,
    required this.payment,
  });

  factory UploadData.fromJson(Map<String, dynamic> json) {
    return UploadData(
      order: OrderInfo.fromJson(json['order']),
      payment: PaymentInfo.fromJson(json['payment']),
    );
  }
}

class OrderInfo {
  final int id;
  final String incrementId;
  final String status;
  final double grandTotal;
  final String createdAt;

  OrderInfo({
    required this.id,
    required this.incrementId,
    required this.status,
    required this.grandTotal,
    required this.createdAt,
  });

  factory OrderInfo.fromJson(Map<String, dynamic> json) {
    return OrderInfo(
      id: json['id'] ?? 0,
      incrementId: json['increment_id'] ?? '',
      status: json['status'] ?? '',
      grandTotal: (json['grand_total'] ?? 0).toDouble(),
      createdAt: json['created_at'] ?? '',
    );
  }
}

class PaymentInfo {
  final int id;
  final int orderId;
  final String methodCode;
  final String? transactionReference;
  final String status;
  final String statusLabel;
  final String createdAt;
  final bool isPending;
  final bool isApproved;
  final bool isRejected;

  PaymentInfo({
    required this.id,
    required this.orderId,
    required this.methodCode,
    this.transactionReference,
    required this.status,
    required this.statusLabel,
    required this.createdAt,
    required this.isPending,
    required this.isApproved,
    required this.isRejected,
  });

  factory PaymentInfo.fromJson(Map<String, dynamic> json) {
    return PaymentInfo(
      id: json['id'] ?? 0,
      orderId: json['order_id'] ?? 0,
      methodCode: json['method_code'] ?? '',
      transactionReference: json['transaction_reference'],
      status: json['status'] ?? '',
      statusLabel: json['status_label'] ?? '',
      createdAt: json['created_at'] ?? '',
      isPending: json['is_pending'] ?? false,
      isApproved: json['is_approved'] ?? false,
      isRejected: json['is_rejected'] ?? false,
    );
  }
}
```


### 3. نموذج قائمة المدفوعات

```dart
// lib/models/payment_list_response.dart

class PaymentListResponse {
  final bool success;
  final List<PaymentDetails> data;
  final PaginationMeta meta;

  PaymentListResponse({
    required this.success,
    required this.data,
    required this.meta,
  });

  factory PaymentListResponse.fromJson(Map<String, dynamic> json) {
    return PaymentListResponse(
      success: json['success'] ?? false,
      data: (json['data'] as List?)
              ?.map((e) => PaymentDetails.fromJson(e))
              .toList() ??
          [],
      meta: PaginationMeta.fromJson(json['meta'] ?? {}),
    );
  }
}

class PaginationMeta {
  final int currentPage;
  final int lastPage;
  final int perPage;
  final int total;

  PaginationMeta({
    required this.currentPage,
    required this.lastPage,
    required this.perPage,
    required this.total,
  });

  factory PaginationMeta.fromJson(Map<String, dynamic> json) {
    return PaginationMeta(
      currentPage: json['current_page'] ?? 1,
      lastPage: json['last_page'] ?? 1,
      perPage: json['per_page'] ?? 15,
      total: json['total'] ?? 0,
    );
  }

  bool get hasNextPage => currentPage < lastPage;
  bool get hasPreviousPage => currentPage > 1;
}
```

### 4. نموذج تفاصيل الدفع

```dart
// lib/models/payment_details.dart

class PaymentDetails {
  final int id;
  final int orderId;
  final OrderDetails? order;
  final int customerId;
  final String methodCode;
  final String? transactionReference;
  final String status;
  final String statusLabel;
  final int? reviewedBy;
  final ReviewerInfo? reviewer;
  final String? reviewedAt;
  final String? adminNote;
  final String createdAt;
  final String updatedAt;
  final bool isPending;
  final bool isApproved;
  final bool isRejected;

  PaymentDetails({
    required this.id,
    required this.orderId,
    this.order,
    required this.customerId,
    required this.methodCode,
    this.transactionReference,
    required this.status,
    required this.statusLabel,
    this.reviewedBy,
    this.reviewer,
    this.reviewedAt,
    this.adminNote,
    required this.createdAt,
    required this.updatedAt,
    required this.isPending,
    required this.isApproved,
    required this.isRejected,
  });

  factory PaymentDetails.fromJson(Map<String, dynamic> json) {
    return PaymentDetails(
      id: json['id'] ?? 0,
      orderId: json['order_id'] ?? 0,
      order: json['order'] != null ? OrderDetails.fromJson(json['order']) : null,
      customerId: json['customer_id'] ?? 0,
      methodCode: json['method_code'] ?? '',
      transactionReference: json['transaction_reference'],
      status: json['status'] ?? '',
      statusLabel: json['status_label'] ?? '',
      reviewedBy: json['reviewed_by'],
      reviewer: json['reviewer'] != null ? ReviewerInfo.fromJson(json['reviewer']) : null,
      reviewedAt: json['reviewed_at'],
      adminNote: json['admin_note'],
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'] ?? '',
      isPending: json['is_pending'] ?? false,
      isApproved: json['is_approved'] ?? false,
      isRejected: json['is_rejected'] ?? false,
    );
  }
}

class OrderDetails {
  final int id;
  final String incrementId;
  final String status;
  final double grandTotal;
  final String grandTotalFormatted;
  final String createdAt;

  OrderDetails({
    required this.id,
    required this.incrementId,
    required this.status,
    required this.grandTotal,
    required this.grandTotalFormatted,
    required this.createdAt,
  });

  factory OrderDetails.fromJson(Map<String, dynamic> json) {
    return OrderDetails(
      id: json['id'] ?? 0,
      incrementId: json['increment_id'] ?? '',
      status: json['status'] ?? '',
      grandTotal: (json['grand_total'] ?? 0).toDouble(),
      grandTotalFormatted: json['grand_total_formatted'] ?? '',
      createdAt: json['created_at'] ?? '',
    );
  }
}

class ReviewerInfo {
  final int id;
  final String name;

  ReviewerInfo({
    required this.id,
    required this.name,
  });

  factory ReviewerInfo.fromJson(Map<String, dynamic> json) {
    return ReviewerInfo(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
    );
  }
}
```

### 5. نموذج الإحصائيات

```dart
// lib/models/payment_statistics.dart

class PaymentStatistics {
  final int total;
  final int pending;
  final int approved;
  final int rejected;

  PaymentStatistics({
    required this.total,
    required this.pending,
    required this.approved,
    required this.rejected,
  });

  factory PaymentStatistics.fromJson(Map<String, dynamic> json) {
    return PaymentStatistics(
      total: json['total'] ?? 0,
      pending: json['pending'] ?? 0,
      approved: json['approved'] ?? 0,
      rejected: json['rejected'] ?? 0,
    );
  }

  // نسب مئوية
  double get pendingPercentage => total > 0 ? (pending / total) * 100 : 0;
  double get approvedPercentage => total > 0 ? (approved / total) * 100 : 0;
  double get rejectedPercentage => total > 0 ? (rejected / total) * 100 : 0;
}
```

---

## أمثلة كاملة

### مثال 1: عرض الإعدادات

```dart
// lib/screens/bank_transfer_config_screen.dart
import 'package:flutter/material.dart';
import '../services/bank_transfer_api.dart';
import '../models/bank_transfer_config.dart';

class BankTransferConfigScreen extends StatefulWidget {
  @override
  _BankTransferConfigScreenState createState() => _BankTransferConfigScreenState();
}

class _BankTransferConfigScreenState extends State<BankTransferConfigScreen> {
  bool _isLoading = true;
  String? _error;
  BankTransferConfig? _config;

  @override
  void initState() {
    super.initState();
    _loadConfig();
  }

  Future<void> _loadConfig() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final config = await BankTransferApi.getConfig();
      setState(() {
        _config = config;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('التحويل البنكي'),
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(_error!, style: TextStyle(color: Colors.red)),
                      SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadConfig,
                        child: Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                )
              : _buildConfigContent(),
    );
  }

  Widget _buildConfigContent() {
    if (_config?.data == null) {
      return Center(child: Text('لا توجد بيانات'));
    }

    final data = _config!.data!;

    return SingleChildScrollView(
      padding: EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // العنوان والوصف
          Text(
            data.title,
            style: Theme.of(context).textTheme.headlineSmall,
          ),
          if (data.description != null) ...[
            SizedBox(height: 8),
            Text(data.description!),
          ],
          
          SizedBox(height: 24),
          
          // الحسابات البنكية
          Text(
            'الحسابات البنكية',
            style: Theme.of(context).textTheme.titleLarge,
          ),
          SizedBox(height: 16),
          
          ...data.bankAccounts.map((account) => _buildBankAccountCard(account)),
          
          SizedBox(height: 24),
          
          // التعليمات
          if (data.instructions != null) ...[
            Text(
              'التعليمات',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            SizedBox(height: 8),
            Text(data.instructions!),
            SizedBox(height: 24),
          ],
          
          // معلومات الرفع
          Card(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'متطلبات رفع الملف',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  SizedBox(height: 8),
                  Text('الحد الأقصى للحجم: ${data.maxFileSize}'),
                  Text('الأنواع المسموحة: ${data.allowedFileTypes.join(", ")}'),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBankAccountCard(BankAccount account) {
    return Card(
      margin: EdgeInsets.only(bottom: 16),
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              account.bankName,
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            if (account.branchName != null) ...[
              SizedBox(height: 4),
              Text('الفرع: ${account.branchName}'),
            ],
            SizedBox(height: 8),
            _buildInfoRow('اسم الحساب', account.accountHolder),
            _buildInfoRow('رقم الحساب', account.accountNumber),
            if (account.iban != null)
              _buildInfoRow('IBAN', account.iban!),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Text('$label: ', style: TextStyle(fontWeight: FontWeight.w500)),
          Expanded(child: Text(value)),
          IconButton(
            icon: Icon(Icons.copy, size: 20),
            onPressed: () {
              // نسخ إلى الحافظة
              // Clipboard.setData(ClipboardData(text: value));
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('تم النسخ')),
              );
            },
          ),
        ],
      ),
    );
  }
}
```


### مثال 2: رفع إثبات الدفع

```dart
// lib/screens/upload_payment_screen.dart
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:file_picker/file_picker.dart';
import '../services/bank_transfer_api.dart';
import '../models/upload_payment_response.dart';

class UploadPaymentScreen extends StatefulWidget {
  @override
  _UploadPaymentScreenState createState() => _UploadPaymentScreenState();
}

class _UploadPaymentScreenState extends State<UploadPaymentScreen> {
  final _formKey = GlobalKey<FormState>();
  final _transactionRefController = TextEditingController();
  
  File? _selectedFile;
  bool _isUploading = false;
  String? _error;

  @override
  void dispose() {
    _transactionRefController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    try {
      final picker = ImagePicker();
      final pickedFile = await picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1920,
        maxHeight: 1920,
        imageQuality: 85,
      );

      if (pickedFile != null) {
        setState(() {
          _selectedFile = File(pickedFile.path);
          _error = null;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'فشل في اختيار الصورة: $e';
      });
    }
  }

  Future<void> _pickFile() async {
    try {
      final result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
      );

      if (result != null && result.files.single.path != null) {
        setState(() {
          _selectedFile = File(result.files.single.path!);
          _error = null;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'فشل في اختيار الملف: $e';
      });
    }
  }

  Future<void> _uploadPayment() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_selectedFile == null) {
      setState(() {
        _error = 'يرجى اختيار ملف إثبات الدفع';
      });
      return;
    }

    setState(() {
      _isUploading = true;
      _error = null;
    });

    try {
      final response = await BankTransferApi.uploadPaymentProof(
        file: _selectedFile!,
        transactionReference: _transactionRefController.text.trim().isEmpty
            ? null
            : _transactionRefController.text.trim(),
      );

      if (response.success) {
        // نجح الرفع - الانتقال إلى صفحة النجاح
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (context) => UploadSuccessScreen(response: response),
          ),
        );
      } else {
        setState(() {
          _error = response.message;
          _isUploading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isUploading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('رفع إثبات الدفع'),
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // معاينة الملف
              if (_selectedFile != null) ...[
                Card(
                  child: Padding(
                    padding: EdgeInsets.all(16),
                    child: Column(
                      children: [
                        if (_selectedFile!.path.toLowerCase().endsWith('.pdf'))
                          Icon(Icons.picture_as_pdf, size: 100, color: Colors.red)
                        else
                          Image.file(
                            _selectedFile!,
                            height: 200,
                            fit: BoxFit.contain,
                          ),
                        SizedBox(height: 8),
                        Text(
                          _selectedFile!.path.split('/').last,
                          style: TextStyle(fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ),
                SizedBox(height: 16),
              ],

              // أزرار اختيار الملف
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: _isUploading ? null : _pickImage,
                      icon: Icon(Icons.photo_library),
                      label: Text('اختر صورة'),
                    ),
                  ),
                  SizedBox(width: 16),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: _isUploading ? null : _pickFile,
                      icon: Icon(Icons.attach_file),
                      label: Text('اختر ملف'),
                    ),
                  ),
                ],
              ),

              SizedBox(height: 24),

              // رقم المعاملة
              TextFormField(
                controller: _transactionRefController,
                decoration: InputDecoration(
                  labelText: 'رقم المعاملة (اختياري)',
                  hintText: 'أدخل رقم المعاملة البنكية',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.receipt),
                ),
                maxLength: 255,
                enabled: !_isUploading,
              ),

              SizedBox(height: 24),

              // رسالة الخطأ
              if (_error != null) ...[
                Card(
                  color: Colors.red.shade50,
                  child: Padding(
                    padding: EdgeInsets.all(12),
                    child: Row(
                      children: [
                        Icon(Icons.error, color: Colors.red),
                        SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            _error!,
                            style: TextStyle(color: Colors.red.shade900),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                SizedBox(height: 16),
              ],

              // زر الرفع
              ElevatedButton(
                onPressed: _isUploading ? null : _uploadPayment,
                style: ElevatedButton.styleFrom(
                  padding: EdgeInsets.symmetric(vertical: 16),
                ),
                child: _isUploading
                    ? Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          ),
                          SizedBox(width: 12),
                          Text('جاري الرفع...'),
                        ],
                      )
                    : Text('رفع إثبات الدفع', style: TextStyle(fontSize: 16)),
              ),

              SizedBox(height: 16),

              // ملاحظة
              Card(
                color: Colors.blue.shade50,
                child: Padding(
                  padding: EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.info, color: Colors.blue),
                          SizedBox(width: 8),
                          Text(
                            'ملاحظة مهمة',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              color: Colors.blue.shade900,
                            ),
                          ),
                        ],
                      ),
                      SizedBox(height: 8),
                      Text(
                        '• الحد الأقصى لحجم الملف: 4MB\n'
                        '• الأنواع المسموحة: JPG, PNG, WEBP, PDF\n'
                        '• سيتم مراجعة الدفع خلال 24 ساعة\n'
                        '• تأكد من وضوح الصورة وقراءة جميع التفاصيل',
                        style: TextStyle(color: Colors.blue.shade900),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// شاشة النجاح
class UploadSuccessScreen extends StatelessWidget {
  final UploadPaymentResponse response;

  const UploadSuccessScreen({required this.response});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('تم الرفع بنجاح'),
      ),
      body: Center(
        child: Padding(
          padding: EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.check_circle,
                color: Colors.green,
                size: 100,
              ),
              SizedBox(height: 24),
              Text(
                response.message,
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                textAlign: TextAlign.center,
              ),
              SizedBox(height: 32),
              if (response.data != null) ...[
                Card(
                  child: Padding(
                    padding: EdgeInsets.all(16),
                    child: Column(
                      children: [
                        _buildInfoRow('رقم الطلب', response.data!.order.incrementId),
                        _buildInfoRow('حالة الطلب', response.data!.order.status),
                        _buildInfoRow(
                          'المبلغ الإجمالي',
                          '${response.data!.order.grandTotal} ريال',
                        ),
                        _buildInfoRow('حالة الدفع', response.data!.payment.statusLabel),
                      ],
                    ),
                  ),
                ),
              ],
              SizedBox(height: 32),
              ElevatedButton(
                onPressed: () {
                  Navigator.of(context).popUntil((route) => route.isFirst);
                },
                child: Text('العودة للرئيسية'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontWeight: FontWeight.w500)),
          Text(value),
        ],
      ),
    );
  }
}
```


### مثال 3: عرض قائمة المدفوعات

```dart
// lib/screens/payments_list_screen.dart
import 'package:flutter/material.dart';
import '../services/bank_transfer_api.dart';
import '../models/payment_list_response.dart';
import '../models/payment_details.dart';

class PaymentsListScreen extends StatefulWidget {
  @override
  _PaymentsListScreenState createState() => _PaymentsListScreenState();
}

class _PaymentsListScreenState extends State<PaymentsListScreen> {
  final ScrollController _scrollController = ScrollController();
  
  List<PaymentDetails> _payments = [];
  bool _isLoading = false;
  bool _isLoadingMore = false;
  String? _error;
  
  int _currentPage = 1;
  int _lastPage = 1;
  int _total = 0;

  @override
  void initState() {
    super.initState();
    _loadPayments();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      if (!_isLoadingMore && _currentPage < _lastPage) {
        _loadMore();
      }
    }
  }

  Future<void> _loadPayments() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final response = await BankTransferApi.getPayments(page: 1);
      setState(() {
        _payments = response.data;
        _currentPage = response.meta.currentPage;
        _lastPage = response.meta.lastPage;
        _total = response.meta.total;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _loadMore() async {
    if (_isLoadingMore) return;

    setState(() {
      _isLoadingMore = true;
    });

    try {
      final response = await BankTransferApi.getPayments(page: _currentPage + 1);
      setState(() {
        _payments.addAll(response.data);
        _currentPage = response.meta.currentPage;
        _isLoadingMore = false;
      });
    } catch (e) {
      setState(() {
        _isLoadingMore = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('فشل في تحميل المزيد: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('مدفوعاتي'),
        actions: [
          IconButton(
            icon: Icon(Icons.refresh),
            onPressed: _loadPayments,
          ),
        ],
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildErrorWidget()
              : _payments.isEmpty
                  ? _buildEmptyWidget()
                  : _buildPaymentsList(),
    );
  }

  Widget _buildErrorWidget() {
    return Center(
      child: Padding(
        padding: EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 64, color: Colors.red),
            SizedBox(height: 16),
            Text(
              _error!,
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.red),
            ),
            SizedBox(height: 24),
            ElevatedButton(
              onPressed: _loadPayments,
              child: Text('إعادة المحاولة'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyWidget() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.inbox, size: 64, color: Colors.grey),
          SizedBox(height: 16),
          Text(
            'لا توجد مدفوعات',
            style: TextStyle(fontSize: 18, color: Colors.grey),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentsList() {
    return RefreshIndicator(
      onRefresh: _loadPayments,
      child: Column(
        children: [
          // عداد المدفوعات
          Container(
            padding: EdgeInsets.all(16),
            color: Colors.grey.shade100,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'إجمالي المدفوعات',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                Text(
                  '$_total',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    color: Theme.of(context).primaryColor,
                  ),
                ),
              ],
            ),
          ),
          
          // قائمة المدفوعات
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              itemCount: _payments.length + (_isLoadingMore ? 1 : 0),
              itemBuilder: (context, index) {
                if (index == _payments.length) {
                  return Center(
                    child: Padding(
                      padding: EdgeInsets.all(16),
                      child: CircularProgressIndicator(),
                    ),
                  );
                }
                
                return _buildPaymentCard(_payments[index]);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentCard(PaymentDetails payment) {
    return Card(
      margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: InkWell(
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => PaymentDetailsScreen(paymentId: payment.id),
            ),
          );
        },
        child: Padding(
          padding: EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // رأس البطاقة
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'طلب #${payment.order?.incrementId ?? payment.orderId}',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  _buildStatusChip(payment),
                ],
              ),
              
              SizedBox(height: 12),
              
              // المبلغ
              if (payment.order != null)
                Row(
                  children: [
                    Icon(Icons.attach_money, size: 20, color: Colors.grey),
                    SizedBox(width: 4),
                    Text(
                      payment.order!.grandTotalFormatted,
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.green,
                      ),
                    ),
                  ],
                ),
              
              SizedBox(height: 8),
              
              // رقم المعاملة
              if (payment.transactionReference != null) ...[
                Row(
                  children: [
                    Icon(Icons.receipt, size: 16, color: Colors.grey),
                    SizedBox(width: 4),
                    Text(
                      'رقم المعاملة: ${payment.transactionReference}',
                      style: TextStyle(color: Colors.grey.shade700),
                    ),
                  ],
                ),
                SizedBox(height: 8),
              ],
              
              // التاريخ
              Row(
                children: [
                  Icon(Icons.calendar_today, size: 16, color: Colors.grey),
                  SizedBox(width: 4),
                  Text(
                    _formatDate(payment.createdAt),
                    style: TextStyle(color: Colors.grey.shade700),
                  ),
                ],
              ),
              
              // ملاحظة المسؤول (إن وجدت)
              if (payment.adminNote != null) ...[
                SizedBox(height: 12),
                Container(
                  padding: EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.orange.shade50,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.note, size: 16, color: Colors.orange),
                      SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          payment.adminNote!,
                          style: TextStyle(fontSize: 12),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatusChip(PaymentDetails payment) {
    Color color;
    IconData icon;
    
    if (payment.isPending) {
      color = Colors.orange;
      icon = Icons.pending;
    } else if (payment.isApproved) {
      color = Colors.green;
      icon = Icons.check_circle;
    } else if (payment.isRejected) {
      color = Colors.red;
      icon = Icons.cancel;
    } else {
      color = Colors.grey;
      icon = Icons.help;
    }

    return Container(
      padding: EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: color),
          SizedBox(width: 4),
          Text(
            payment.statusLabel,
            style: TextStyle(
              color: color,
              fontWeight: FontWeight.bold,
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return '${date.day}/${date.month}/${date.year}';
    } catch (e) {
      return dateStr;
    }
  }
}
```

