# دليل MedSDN API الشامل لمطوري Flutter

## 📋 المحتويات

1. [الإعداد الأولي](#الإعداد-الأولي)
2. [المصادقة وإدارة الحساب](#المصادقة-وإدارة-الحساب)
3. [المنتجات والفئات](#المنتجات-والفئات)
4. [إدارة السلة](#إدارة-السلة)
5. [عملية الدفع](#عملية-الدفع)
6. [إدارة الطلبات](#إدارة-الطلبات)
7. [قائمة الأمنيات](#قائمة-الأمنيات)
8. [المقارنة](#المقارنة)
9. [المراجعات والتقييمات](#المراجعات-والتقييمات)
10. [التحويل البنكي](#التحويل-البنكي)
11. [معالجة الأخطاء](#معالجة-الأخطاء)
12. [أمثلة واجهة المستخدم](#أمثلة-واجهة-المستخدم)

---

## الإعداد الأولي

### 1. إضافة التبعيات

```yaml
# pubspec.yaml
dependencies:
  flutter:
    sdk: flutter
  
  # HTTP Clients
  http: ^1.1.0
  dio: ^5.4.0
  
  # State Management
  provider: ^6.1.1
  # أو
  riverpod: ^3.2.1
  
  # Storage
  shared_preferences: ^2.2.2
  flutter_secure_storage: ^10.0.0
  
  # File Handling
  image_picker: ^1.0.4
  file_picker: ^10.3.10
  path: ^1.8.3
  mime: ^1.0.4
  
  # UI
  cached_network_image: ^3.3.0
  flutter_svg: ^2.0.9
  
  # Utils
  intl: ^0.18.1
  json_annotation: ^4.8.1

dev_dependencies:
  build_runner: ^2.4.7
  json_serializable: ^6.7.1
```

**ملاحظة:** بعد تحديث الحزم، قم بتشغيل `flutter pub get` والتحقق من ملاحظات الإصدار لكل حزمة:
- riverpod: راجع [دليل الترحيل من v2 إلى v3](https://riverpod.dev/docs/migration/from_v2_to_v3)
- file_picker: راجع [سجل التغييرات](https://pub.dev/packages/file_picker/changelog) للتغييرات في API
- flutter_secure_storage: راجع [سجل التغييرات](https://pub.dev/packages/flutter_secure_storage/changelog) لأي تغييرات في الإعداد

### 2. هيكل المشروع

```
lib/
├── config/
│   ├── api_config.dart
│   └── app_config.dart
├── models/
│   ├── customer/
│   ├── product/
│   ├── cart/
│   ├── order/
│   └── payment/
├── services/
│   ├── api/
│   │   ├── auth_api.dart
│   │   ├── product_api.dart
│   │   ├── cart_api.dart
│   │   ├── order_api.dart
│   │   ├── wishlist_api.dart
│   │   ├── compare_api.dart
│   │   ├── review_api.dart
│   │   └── payment_api.dart
│   ├── auth_service.dart
│   └── storage_service.dart
├── providers/
│   ├── auth_provider.dart
│   ├── cart_provider.dart
│   ├── product_provider.dart
│   └── order_provider.dart
├── screens/
│   ├── auth/
│   ├── products/
│   ├── cart/
│   ├── orders/
│   └── payment/
└── widgets/
    └── common/
```

### 3. إعداد الثوابت

```dart
// lib/config/api_config.dart
class ApiConfig {
  // Base URLs
  static const String baseUrl = 'https://your-domain.com';
  static const String apiUrl = '$baseUrl/api/shop';
  static const String graphqlUrl = '$baseUrl/graphql';
  
  // Timeouts
  static const Duration connectTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
  
  // Headers
  static Map<String, String> get defaultHeaders => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  };
  
  // Auth Endpoints
  static const String login = '$apiUrl/customer/login';
  static const String register = '$apiUrl/customer/register';
  static const String logout = '$apiUrl/customer/logout';
  static const String profile = '$apiUrl/customer/profile';
  static const String forgotPassword = '$apiUrl/customer/forgot-password';
  
  // Product Endpoints
  static const String products = '$apiUrl/products';
  static const String categories = '$apiUrl/categories';
  
  // Cart Endpoints
  static const String cart = '$apiUrl/cart';
  static const String addToCart = '$apiUrl/cart/add';
  static const String updateCart = '$apiUrl/cart/update';
  static const String removeFromCart = '$apiUrl/cart/remove';
  
  // Checkout Endpoints
  static const String checkout = '$apiUrl/checkout';
  static const String checkoutAddress = '$apiUrl/checkout/address';
  static const String shippingMethods = '$apiUrl/checkout/shipping-methods';
  static const String paymentMethods = '$apiUrl/checkout/payment-methods';
  static const String placeOrder = '$apiUrl/checkout/place-order';
  
  // Order Endpoints
  static const String orders = '$apiUrl/orders';
  static const String orderDetails = '$apiUrl/orders';
  static const String cancelOrder = '$apiUrl/orders/cancel';
  static const String reorder = '$apiUrl/orders/reorder';
  
  // Wishlist Endpoints
  static const String wishlist = '$apiUrl/wishlist';
  static const String addToWishlist = '$apiUrl/wishlist/add';
  static const String removeFromWishlist = '$apiUrl/wishlist/remove';
  static const String moveToCart = '$apiUrl/wishlist/move-to-cart';
  
  // Compare Endpoints
  static const String compare = '$apiUrl/compare';
  static const String addToCompare = '$apiUrl/compare/add';
  static const String removeFromCompare = '$apiUrl/compare/remove';
  
  // Review Endpoints
  static const String reviews = '$apiUrl/reviews';
  static const String createReview = '$apiUrl/reviews/create';
  
  // Bank Transfer Endpoints
  static const String bankTransfer = '$apiUrl/bank-transfer';
  static const String bankTransferConfig = '$apiUrl/bank-transfer/config';
  static const String bankTransferUpload = '$apiUrl/bank-transfer/upload';
  static const String bankTransferPayments = '$apiUrl/bank-transfer/payments';
}
```


---

## المصادقة وإدارة الحساب

### خدمة التخزين الآمن

```dart
// lib/services/storage_service.dart
import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  static final _storage = FlutterSecureStorage();
  static const String _tokenKey = 'auth_token';
  static const String _customerKey = 'customer_data';
  static const String _cartTokenKey = 'cart_token';
  
  // حفظ الرمز
  static Future<void> saveToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
  }
  
  // استرجاع الرمز
  static Future<String?> getToken() async {
    return await _storage.read(key: _tokenKey);
  }
  
  // حذف الرمز
  static Future<void> deleteToken() async {
    await _storage.delete(key: _tokenKey);
  }
  
  // حفظ رمز السلة
  static Future<void> saveCartToken(String token) async {
    await _storage.write(key: _cartTokenKey, value: token);
  }
  
  // استرجاع رمز السلة
  static Future<String?> getCartToken() async {
    return await _storage.read(key: _cartTokenKey);
  }
  
  // حفظ بيانات العميل
  static Future<void> saveCustomer(Map<String, dynamic> customer) async {
    await _storage.write(key: _customerKey, value: jsonEncode(customer));
  }
  
  // استرجاع بيانات العميل
  static Future<Map<String, dynamic>?> getCustomer() async {
    final data = await _storage.read(key: _customerKey);
    if (data != null) {
      return jsonDecode(data);
    }
    return null;
  }
  
  // مسح جميع البيانات
  static Future<void> clearAll() async {
    await _storage.deleteAll();
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
  }
}
```

### نماذج المصادقة

```dart
// lib/models/customer/customer.dart
import 'package:json_annotation/json_annotation.dart';

part 'customer.g.dart';

@JsonSerializable()
class Customer {
  final int id;
  @JsonKey(name: 'first_name')
  final String firstName;
  @JsonKey(name: 'last_name')
  final String lastName;
  final String email;
  final String? phone;
  @JsonKey(name: 'date_of_birth')
  final String? dateOfBirth;
  final String? gender;
  @JsonKey(name: 'created_at')
  final String createdAt;

  Customer({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.email,
    this.phone,
    this.dateOfBirth,
    this.gender,
    required this.createdAt,
  });

  String get fullName => '$firstName $lastName';

  factory Customer.fromJson(Map<String, dynamic> json) => 
      _$CustomerFromJson(json);
  Map<String, dynamic> toJson() => _$CustomerToJson(this);
}
```


### خدمة المصادقة API

```dart
// lib/services/api/auth_api.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../config/api_config.dart';
import '../../models/customer/customer.dart';
import '../storage_service.dart';

class AuthApi {
  /// تسجيل الدخول
  static Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final response = await http.post(
      Uri.parse(ApiConfig.login),
      headers: ApiConfig.defaultHeaders,
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    ).timeout(ApiConfig.connectTimeout);

    final data = jsonDecode(response.body);

    if (response.statusCode == 200 && data['success'] == true) {
      if (data['token'] != null) {
        await StorageService.saveToken(data['token']);
      }
      if (data['data'] != null) {
        await StorageService.saveCustomer(data['data']);
      }
      return data;
    } else {
      throw Exception(data['message'] ?? 'فشل تسجيل الدخول');
    }
  }

  /// تسجيل حساب جديد
  static Future<Map<String, dynamic>> register({
    required String firstName,
    required String lastName,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
  }) async {
    final response = await http.post(
      Uri.parse(ApiConfig.register),
      headers: ApiConfig.defaultHeaders,
      body: jsonEncode({
        'first_name': firstName,
        'last_name': lastName,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
        if (phone != null) 'phone': phone,
      }),
    ).timeout(ApiConfig.connectTimeout);

    final data = jsonDecode(response.body);

    if (response.statusCode == 200 || response.statusCode == 201) {
      if (data['token'] != null) {
        await StorageService.saveToken(data['token']);
      }
      if (data['data'] != null) {
        await StorageService.saveCustomer(data['data']);
      }
      return data;
    } else {
      throw Exception(data['message'] ?? 'فشل التسجيل');
    }
  }

  /// تسجيل الخروج
  static Future<void> logout() async {
    try {
      final token = await StorageService.getToken();
      if (token != null) {
        await http.post(
          Uri.parse(ApiConfig.logout),
          headers: {
            ...ApiConfig.defaultHeaders,
            'Authorization': 'Bearer $token',
          },
        ).timeout(ApiConfig.connectTimeout);
      }
    } finally {
      await StorageService.clearAll();
    }
  }

  /// الحصول على الملف الشخصي
  static Future<Customer> getProfile() async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.get(
      Uri.parse(ApiConfig.profile),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return Customer.fromJson(data['data']);
    } else if (response.statusCode == 401) {
      await StorageService.clearAll();
      throw Exception('انتهت صلاحية الجلسة');
    } else {
      throw Exception('فشل في جلب البيانات');
    }
  }

  /// تحديث الملف الشخصي
  static Future<Customer> updateProfile({
    required String firstName,
    required String lastName,
    String? phone,
    String? dateOfBirth,
    String? gender,
  }) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.put(
      Uri.parse(ApiConfig.profile),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({
        'first_name': firstName,
        'last_name': lastName,
        if (phone != null) 'phone': phone,
        if (dateOfBirth != null) 'date_of_birth': dateOfBirth,
        if (gender != null) 'gender': gender,
      }),
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final customer = Customer.fromJson(data['data']);
      await StorageService.saveCustomer(customer.toJson());
      return customer;
    } else {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل التحديث');
    }
  }

  /// نسيت كلمة المرور
  static Future<void> forgotPassword(String email) async {
    final response = await http.post(
      Uri.parse(ApiConfig.forgotPassword),
      headers: ApiConfig.defaultHeaders,
      body: jsonEncode({'email': email}),
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل إرسال رابط إعادة التعيين');
    }
  }
}
```


---

## المنتجات والفئات

### نماذج المنتجات

```dart
// lib/models/product/product.dart
import 'package:json_annotation/json_annotation.dart';

part 'product.g.dart';

@JsonSerializable()
class Product {
  final int id;
  final String sku;
  final String name;
  final String? description;
  @JsonKey(name: 'short_description')
  final String? shortDescription;
  final double price;
  @JsonKey(name: 'special_price')
  final double? specialPrice;
  final int quantity;
  @JsonKey(name: 'in_stock')
  final bool inStock;
  final List<ProductImage>? images;
  final List<String>? categories;
  @JsonKey(name: 'created_at')
  final String createdAt;

  Product({
    required this.id,
    required this.sku,
    required this.name,
    this.description,
    this.shortDescription,
    required this.price,
    this.specialPrice,
    required this.quantity,
    required this.inStock,
    this.images,
    this.categories,
    required this.createdAt,
  });

  double get finalPrice => specialPrice ?? price;
  bool get hasDiscount => specialPrice != null && specialPrice! < price;
  double get discountPercentage => 
      hasDiscount ? ((price - specialPrice!) / price * 100) : 0;

  factory Product.fromJson(Map<String, dynamic> json) => 
      _$ProductFromJson(json);
  Map<String, dynamic> toJson() => _$ProductToJson(this);
}

@JsonSerializable()
class ProductImage {
  final int id;
  final String path;
  final String url;

  ProductImage({
    required this.id,
    required this.path,
    required this.url,
  });

  factory ProductImage.fromJson(Map<String, dynamic> json) => 
      _$ProductImageFromJson(json);
  Map<String, dynamic> toJson() => _$ProductImageToJson(this);
}

@JsonSerializable()
class Category {
  final int id;
  final String name;
  final String? description;
  @JsonKey(name: 'image_url')
  final String? imageUrl;
  @JsonKey(name: 'parent_id')
  final int? parentId;
  final List<Category>? children;

  Category({
    required this.id,
    required this.name,
    this.description,
    this.imageUrl,
    this.parentId,
    this.children,
  });

  factory Category.fromJson(Map<String, dynamic> json) => 
      _$CategoryFromJson(json);
  Map<String, dynamic> toJson() => _$CategoryToJson(this);
}
```

### خدمة المنتجات API

```dart
// lib/services/api/product_api.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../config/api_config.dart';
import '../../models/product/product.dart';
import '../storage_service.dart';

class ProductApi {
  /// الحصول على قائمة المنتجات
  static Future<Map<String, dynamic>> getProducts({
    int page = 1,
    int perPage = 20,
    String? search,
    int? categoryId,
    double? minPrice,
    double? maxPrice,
    String? sortBy,
    String? sortOrder,
  }) async {
    final queryParams = {
      'page': page.toString(),
      'per_page': perPage.toString(),
      if (search != null) 'search': search,
      if (categoryId != null) 'category_id': categoryId.toString(),
      if (minPrice != null) 'min_price': minPrice.toString(),
      if (maxPrice != null) 'max_price': maxPrice.toString(),
      if (sortBy != null) 'sort_by': sortBy,
      if (sortOrder != null) 'sort_order': sortOrder,
    };

    final uri = Uri.parse(ApiConfig.products).replace(
      queryParameters: queryParams,
    );

    final response = await http.get(
      uri,
      headers: ApiConfig.defaultHeaders,
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return {
        'products': (data['data'] as List)
            .map((item) => Product.fromJson(item))
            .toList(),
        'total': data['meta']['total'],
        'current_page': data['meta']['current_page'],
        'last_page': data['meta']['last_page'],
      };
    } else {
      throw Exception('فشل في جلب المنتجات');
    }
  }

  /// الحصول على تفاصيل منتج
  static Future<Product> getProduct(int productId) async {
    final response = await http.get(
      Uri.parse('${ApiConfig.products}/$productId'),
      headers: ApiConfig.defaultHeaders,
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return Product.fromJson(data['data']);
    } else {
      throw Exception('فشل في جلب تفاصيل المنتج');
    }
  }

  /// الحصول على الفئات
  static Future<List<Category>> getCategories() async {
    final response = await http.get(
      Uri.parse(ApiConfig.categories),
      headers: ApiConfig.defaultHeaders,
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List)
          .map((item) => Category.fromJson(item))
          .toList();
    } else {
      throw Exception('فشل في جلب الفئات');
    }
  }

  /// البحث عن المنتجات
  static Future<List<Product>> searchProducts(String query) async {
    final result = await getProducts(search: query);
    return result['products'] as List<Product>;
  }
}
```


---

## إدارة السلة

### نماذج السلة

```dart
// lib/models/cart/cart.dart
import 'package:json_annotation/json_annotation.dart';

part 'cart.g.dart';

@JsonSerializable()
class Cart {
  final int id;
  @JsonKey(name: 'customer_id')
  final int? customerId;
  @JsonKey(name: 'items_count')
  final int itemsCount;
  @JsonKey(name: 'items_qty')
  final int itemsQty;
  @JsonKey(name: 'grand_total')
  final double grandTotal;
  @JsonKey(name: 'sub_total')
  final double subTotal;
  @JsonKey(name: 'tax_total')
  final double taxTotal;
  @JsonKey(name: 'discount_amount')
  final double discountAmount;
  final List<CartItem> items;
  @JsonKey(name: 'selected_shipping_rate')
  final ShippingRate? selectedShippingRate;

  Cart({
    required this.id,
    this.customerId,
    required this.itemsCount,
    required this.itemsQty,
    required this.grandTotal,
    required this.subTotal,
    required this.taxTotal,
    required this.discountAmount,
    required this.items,
    this.selectedShippingRate,
  });

  factory Cart.fromJson(Map<String, dynamic> json) => _$CartFromJson(json);
  Map<String, dynamic> toJson() => _$CartToJson(this);
}

@JsonSerializable()
class CartItem {
  final int id;
  @JsonKey(name: 'product_id')
  final int productId;
  final String name;
  final double price;
  final int quantity;
  final double total;
  @JsonKey(name: 'product_image')
  final String? productImage;
  final Map<String, dynamic>? options;

  CartItem({
    required this.id,
    required this.productId,
    required this.name,
    required this.price,
    required this.quantity,
    required this.total,
    this.productImage,
    this.options,
  });

  factory CartItem.fromJson(Map<String, dynamic> json) => 
      _$CartItemFromJson(json);
  Map<String, dynamic> toJson() => _$CartItemToJson(this);
}

@JsonSerializable()
class ShippingRate {
  final String method;
  @JsonKey(name: 'method_title')
  final String methodTitle;
  final double price;
  @JsonKey(name: 'base_price')
  final double basePrice;

  ShippingRate({
    required this.method,
    required this.methodTitle,
    required this.price,
    required this.basePrice,
  });

  factory ShippingRate.fromJson(Map<String, dynamic> json) => 
      _$ShippingRateFromJson(json);
  Map<String, dynamic> toJson() => _$ShippingRateToJson(this);
}
```

### خدمة السلة API

```dart
// lib/services/api/cart_api.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../config/api_config.dart';
import '../../models/cart/cart.dart';
import '../storage_service.dart';

class CartApi {
  /// الحصول على السلة
  static Future<Cart> getCart() async {
    final token = await StorageService.getToken();
    final cartToken = await StorageService.getCartToken();

    final headers = {
      ...ApiConfig.defaultHeaders,
      if (token != null) 'Authorization': 'Bearer $token',
      if (cartToken != null) 'X-Cart-Token': cartToken,
    };

    final response = await http.get(
      Uri.parse(ApiConfig.cart),
      headers: headers,
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      
      // حفظ رمز السلة إذا كان موجوداً
      if (data['cart_token'] != null) {
        await StorageService.saveCartToken(data['cart_token']);
      }
      
      return Cart.fromJson(data['data']);
    } else {
      throw Exception('فشل في جلب السلة');
    }
  }

  /// إضافة منتج إلى السلة
  static Future<Cart> addToCart({
    required int productId,
    required int quantity,
    Map<String, dynamic>? options,
  }) async {
    final token = await StorageService.getToken();
    final cartToken = await StorageService.getCartToken();

    final headers = {
      ...ApiConfig.defaultHeaders,
      if (token != null) 'Authorization': 'Bearer $token',
      if (cartToken != null) 'X-Cart-Token': cartToken,
    };

    final response = await http.post(
      Uri.parse(ApiConfig.addToCart),
      headers: headers,
      body: jsonEncode({
        'product_id': productId,
        'quantity': quantity,
        if (options != null) 'options': options,
      }),
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      
      if (data['cart_token'] != null) {
        await StorageService.saveCartToken(data['cart_token']);
      }
      
      return Cart.fromJson(data['data']);
    } else {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في إضافة المنتج');
    }
  }

  /// تحديث كمية منتج في السلة
  static Future<Cart> updateCartItem({
    required int cartItemId,
    required int quantity,
  }) async {
    final token = await StorageService.getToken();
    final cartToken = await StorageService.getCartToken();

    final headers = {
      ...ApiConfig.defaultHeaders,
      if (token != null) 'Authorization': 'Bearer $token',
      if (cartToken != null) 'X-Cart-Token': cartToken,
    };

    final response = await http.put(
      Uri.parse('${ApiConfig.updateCart}/$cartItemId'),
      headers: headers,
      body: jsonEncode({'quantity': quantity}),
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return Cart.fromJson(data['data']);
    } else {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في تحديث الكمية');
    }
  }

  /// حذف منتج من السلة
  static Future<Cart> removeFromCart(int cartItemId) async {
    final token = await StorageService.getToken();
    final cartToken = await StorageService.getCartToken();

    final headers = {
      ...ApiConfig.defaultHeaders,
      if (token != null) 'Authorization': 'Bearer $token',
      if (cartToken != null) 'X-Cart-Token': cartToken,
    };

    final response = await http.delete(
      Uri.parse('${ApiConfig.removeFromCart}/$cartItemId'),
      headers: headers,
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return Cart.fromJson(data['data']);
    } else {
      throw Exception('فشل في حذف المنتج');
    }
  }

  /// تطبيق كوبون خصم
  static Future<Cart> applyCoupon(String couponCode) async {
    final token = await StorageService.getToken();
    final cartToken = await StorageService.getCartToken();

    final headers = {
      ...ApiConfig.defaultHeaders,
      if (token != null) 'Authorization': 'Bearer $token',
      if (cartToken != null) 'X-Cart-Token': cartToken,
    };

    final response = await http.post(
      Uri.parse('${ApiConfig.cart}/apply-coupon'),
      headers: headers,
      body: jsonEncode({'coupon_code': couponCode}),
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return Cart.fromJson(data['data']);
    } else {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'كوبون غير صالح');
    }
  }

  /// إزالة كوبون الخصم
  static Future<Cart> removeCoupon() async {
    final token = await StorageService.getToken();
    final cartToken = await StorageService.getCartToken();

    final headers = {
      ...ApiConfig.defaultHeaders,
      if (token != null) 'Authorization': 'Bearer $token',
      if (cartToken != null) 'X-Cart-Token': cartToken,
    };

    final response = await http.delete(
      Uri.parse('${ApiConfig.cart}/remove-coupon'),
      headers: headers,
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return Cart.fromJson(data['data']);
    } else {
      throw Exception('فشل في إزالة الكوبون');
    }
  }
}
```


---

## عملية الدفع

### نماذج الدفع

```dart
// lib/models/checkout/checkout.dart
import 'package:json_annotation/json_annotation.dart';

part 'checkout.g.dart';

@JsonSerializable()
class CheckoutAddress {
  @JsonKey(name: 'first_name')
  final String firstName;
  @JsonKey(name: 'last_name')
  final String lastName;
  final String email;
  final String address;
  final String city;
  final String state;
  final String country;
  final String postcode;
  final String phone;
  @JsonKey(name: 'company_name')
  final String? companyName;

  CheckoutAddress({
    required this.firstName,
    required this.lastName,
    required this.email,
    required this.address,
    required this.city,
    required this.state,
    required this.country,
    required this.postcode,
    required this.phone,
    this.companyName,
  });

  factory CheckoutAddress.fromJson(Map<String, dynamic> json) => 
      _$CheckoutAddressFromJson(json);
  Map<String, dynamic> toJson() => _$CheckoutAddressToJson(this);
}

@JsonSerializable()
class PaymentMethod {
  final String code;
  final String title;
  final String description;
  final bool active;

  PaymentMethod({
    required this.code,
    required this.title,
    required this.description,
    required this.active,
  });

  factory PaymentMethod.fromJson(Map<String, dynamic> json) => 
      _$PaymentMethodFromJson(json);
  Map<String, dynamic> toJson() => _$PaymentMethodToJson(this);
}

@JsonSerializable()
class Order {
  final int id;
  @JsonKey(name: 'increment_id')
  final String incrementId;
  final String status;
  @JsonKey(name: 'grand_total')
  final double grandTotal;
  @JsonKey(name: 'created_at')
  final String createdAt;

  Order({
    required this.id,
    required this.incrementId,
    required this.status,
    required this.grandTotal,
    required this.createdAt,
  });

  factory Order.fromJson(Map<String, dynamic> json) => 
      _$OrderFromJson(json);
  Map<String, dynamic> toJson() => _$OrderToJson(this);
}
```

### خدمة الدفع API

```dart
// lib/services/api/checkout_api.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../config/api_config.dart';
import '../../models/checkout/checkout.dart';
import '../../models/cart/cart.dart';
import '../storage_service.dart';

class CheckoutApi {
  /// حفظ عنوان الفواتير والشحن
  static Future<void> saveAddress({
    required CheckoutAddress billingAddress,
    CheckoutAddress? shippingAddress,
    bool useForShipping = false,
  }) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.post(
      Uri.parse(ApiConfig.checkoutAddress),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({
        'billing': billingAddress.toJson(),
        if (!useForShipping && shippingAddress != null)
          'shipping': shippingAddress.toJson(),
        'use_for_shipping': useForShipping,
      }),
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في حفظ العنوان');
    }
  }

  /// الحصول على طرق الشحن المتاحة
  static Future<List<ShippingRate>> getShippingMethods() async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.get(
      Uri.parse(ApiConfig.shippingMethods),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List)
          .map((item) => ShippingRate.fromJson(item))
          .toList();
    } else {
      throw Exception('فشل في جلب طرق الشحن');
    }
  }

  /// اختيار طريقة الشحن
  static Future<void> saveShippingMethod(String shippingMethod) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.post(
      Uri.parse('${ApiConfig.checkout}/save-shipping'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({'shipping_method': shippingMethod}),
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في حفظ طريقة الشحن');
    }
  }

  /// الحصول على طرق الدفع المتاحة
  static Future<List<PaymentMethod>> getPaymentMethods() async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.get(
      Uri.parse(ApiConfig.paymentMethods),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List)
          .map((item) => PaymentMethod.fromJson(item))
          .toList();
    } else {
      throw Exception('فشل في جلب طرق الدفع');
    }
  }

  /// اختيار طريقة الدفع
  static Future<void> savePaymentMethod(String paymentMethod) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.post(
      Uri.parse('${ApiConfig.checkout}/save-payment'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({'payment_method': paymentMethod}),
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في حفظ طريقة الدفع');
    }
  }

  /// إتمام الطلب
  static Future<Order> placeOrder() async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.post(
      Uri.parse(ApiConfig.placeOrder),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return Order.fromJson(data['data']);
    } else {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في إتمام الطلب');
    }
  }
}
```


---

## إدارة الطلبات

### نماذج الطلبات

```dart
// lib/models/order/order.dart
import 'package:json_annotation/json_annotation.dart';

part 'order.g.dart';

@JsonSerializable()
class OrderDetails {
  final int id;
  @JsonKey(name: 'increment_id')
  final String incrementId;
  final String status;
  @JsonKey(name: 'customer_email')
  final String customerEmail;
  @JsonKey(name: 'customer_first_name')
  final String customerFirstName;
  @JsonKey(name: 'customer_last_name')
  final String customerLastName;
  @JsonKey(name: 'grand_total')
  final double grandTotal;
  @JsonKey(name: 'sub_total')
  final double subTotal;
  @JsonKey(name: 'tax_amount')
  final double taxAmount;
  @JsonKey(name: 'shipping_amount')
  final double shippingAmount;
  @JsonKey(name: 'discount_amount')
  final double discountAmount;
  @JsonKey(name: 'payment_method')
  final String paymentMethod;
  @JsonKey(name: 'shipping_method')
  final String shippingMethod;
  final List<OrderItem> items;
  @JsonKey(name: 'billing_address')
  final OrderAddress billingAddress;
  @JsonKey(name: 'shipping_address')
  final OrderAddress shippingAddress;
  @JsonKey(name: 'created_at')
  final String createdAt;

  OrderDetails({
    required this.id,
    required this.incrementId,
    required this.status,
    required this.customerEmail,
    required this.customerFirstName,
    required this.customerLastName,
    required this.grandTotal,
    required this.subTotal,
    required this.taxAmount,
    required this.shippingAmount,
    required this.discountAmount,
    required this.paymentMethod,
    required this.shippingMethod,
    required this.items,
    required this.billingAddress,
    required this.shippingAddress,
    required this.createdAt,
  });

  factory OrderDetails.fromJson(Map<String, dynamic> json) => 
      _$OrderDetailsFromJson(json);
  Map<String, dynamic> toJson() => _$OrderDetailsToJson(this);
}

@JsonSerializable()
class OrderItem {
  final int id;
  final String name;
  final String sku;
  final double price;
  final int quantity;
  final double total;
  @JsonKey(name: 'product_image')
  final String? productImage;

  OrderItem({
    required this.id,
    required this.name,
    required this.sku,
    required this.price,
    required this.quantity,
    required this.total,
    this.productImage,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) => 
      _$OrderItemFromJson(json);
  Map<String, dynamic> toJson() => _$OrderItemToJson(this);
}

@JsonSerializable()
class OrderAddress {
  @JsonKey(name: 'first_name')
  final String firstName;
  @JsonKey(name: 'last_name')
  final String lastName;
  final String address;
  final String city;
  final String state;
  final String country;
  final String postcode;
  final String phone;

  OrderAddress({
    required this.firstName,
    required this.lastName,
    required this.address,
    required this.city,
    required this.state,
    required this.country,
    required this.postcode,
    required this.phone,
  });

  factory OrderAddress.fromJson(Map<String, dynamic> json) => 
      _$OrderAddressFromJson(json);
  Map<String, dynamic> toJson() => _$OrderAddressToJson(this);
}
```

### خدمة الطلبات API

```dart
// lib/services/api/order_api.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../config/api_config.dart';
import '../../models/order/order.dart';
import '../../models/checkout/checkout.dart';
import '../storage_service.dart';

class OrderApi {
  /// الحصول على قائمة الطلبات
  static Future<Map<String, dynamic>> getOrders({
    int page = 1,
    int perPage = 20,
  }) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final uri = Uri.parse(ApiConfig.orders).replace(
      queryParameters: {
        'page': page.toString(),
        'per_page': perPage.toString(),
      },
    );

    final response = await http.get(
      uri,
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return {
        'orders': (data['data'] as List)
            .map((item) => Order.fromJson(item))
            .toList(),
        'total': data['meta']['total'],
        'current_page': data['meta']['current_page'],
        'last_page': data['meta']['last_page'],
      };
    } else {
      throw Exception('فشل في جلب الطلبات');
    }
  }

  /// الحصول على تفاصيل طلب
  static Future<OrderDetails> getOrderDetails(int orderId) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.get(
      Uri.parse('${ApiConfig.orderDetails}/$orderId'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return OrderDetails.fromJson(data['data']);
    } else {
      throw Exception('فشل في جلب تفاصيل الطلب');
    }
  }

  /// إلغاء طلب
  static Future<void> cancelOrder(int orderId) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.post(
      Uri.parse('${ApiConfig.cancelOrder}/$orderId'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في إلغاء الطلب');
    }
  }

  /// إعادة طلب
  static Future<void> reorder(int orderId) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.post(
      Uri.parse('${ApiConfig.reorder}/$orderId'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في إعادة الطلب');
    }
  }
}
```


---

## قائمة الأمنيات

### نماذج قائمة الأمنيات

```dart
// lib/models/wishlist/wishlist.dart
import 'package:json_annotation/json_annotation.dart';

part 'wishlist.g.dart';

@JsonSerializable()
class WishlistItem {
  final int id;
  @JsonKey(name: 'product_id')
  final int productId;
  @JsonKey(name: 'product_name')
  final String productName;
  final double price;
  @JsonKey(name: 'product_image')
  final String? productImage;
  @JsonKey(name: 'in_stock')
  final bool inStock;
  @JsonKey(name: 'created_at')
  final String createdAt;

  WishlistItem({
    required this.id,
    required this.productId,
    required this.productName,
    required this.price,
    this.productImage,
    required this.inStock,
    required this.createdAt,
  });

  factory WishlistItem.fromJson(Map<String, dynamic> json) => 
      _$WishlistItemFromJson(json);
  Map<String, dynamic> toJson() => _$WishlistItemToJson(this);
}
```

### خدمة قائمة الأمنيات API

```dart
// lib/services/api/wishlist_api.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../config/api_config.dart';
import '../../models/wishlist/wishlist.dart';
import '../storage_service.dart';

class WishlistApi {
  /// الحصول على قائمة الأمنيات
  static Future<List<WishlistItem>> getWishlist() async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.get(
      Uri.parse(ApiConfig.wishlist),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List)
          .map((item) => WishlistItem.fromJson(item))
          .toList();
    } else {
      throw Exception('فشل في جلب قائمة الأمنيات');
    }
  }

  /// إضافة منتج إلى قائمة الأمنيات
  static Future<void> addToWishlist(int productId) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.post(
      Uri.parse(ApiConfig.addToWishlist),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({'product_id': productId}),
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في إضافة المنتج');
    }
  }

  /// حذف منتج من قائمة الأمنيات
  static Future<void> removeFromWishlist(int wishlistItemId) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.delete(
      Uri.parse('${ApiConfig.removeFromWishlist}/$wishlistItemId'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      throw Exception('فشل في حذف المنتج');
    }
  }

  /// نقل منتج من قائمة الأمنيات إلى السلة
  static Future<void> moveToCart(int wishlistItemId) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.post(
      Uri.parse('${ApiConfig.moveToCart}/$wishlistItemId'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في نقل المنتج');
    }
  }

  /// حذف جميع المنتجات من قائمة الأمنيات
  static Future<void> clearWishlist() async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.delete(
      Uri.parse('${ApiConfig.wishlist}/clear'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      throw Exception('فشل في مسح قائمة الأمنيات');
    }
  }
}
```

---

## المقارنة

### نماذج المقارنة

```dart
// lib/models/compare/compare.dart
import 'package:json_annotation/json_annotation.dart';

part 'compare.g.dart';

@JsonSerializable()
class CompareItem {
  final int id;
  @JsonKey(name: 'product_id')
  final int productId;
  @JsonKey(name: 'product_name')
  final String productName;
  final double price;
  @JsonKey(name: 'product_image')
  final String? productImage;
  final Map<String, dynamic>? attributes;

  CompareItem({
    required this.id,
    required this.productId,
    required this.productName,
    required this.price,
    this.productImage,
    this.attributes,
  });

  factory CompareItem.fromJson(Map<String, dynamic> json) => 
      _$CompareItemFromJson(json);
  Map<String, dynamic> toJson() => _$CompareItemToJson(this);
}
```

### خدمة المقارنة API

```dart
// lib/services/api/compare_api.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../config/api_config.dart';
import '../../models/compare/compare.dart';
import '../storage_service.dart';

class CompareApi {
  /// الحصول على قائمة المقارنة
  static Future<List<CompareItem>> getCompareList() async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.get(
      Uri.parse(ApiConfig.compare),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List)
          .map((item) => CompareItem.fromJson(item))
          .toList();
    } else {
      throw Exception('فشل في جلب قائمة المقارنة');
    }
  }

  /// إضافة منتج إلى قائمة المقارنة
  static Future<void> addToCompare(int productId) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.post(
      Uri.parse(ApiConfig.addToCompare),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({'product_id': productId}),
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في إضافة المنتج');
    }
  }

  /// حذف منتج من قائمة المقارنة
  static Future<void> removeFromCompare(int compareItemId) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.delete(
      Uri.parse('${ApiConfig.removeFromCompare}/$compareItemId'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      throw Exception('فشل في حذف المنتج');
    }
  }

  /// حذف جميع المنتجات من قائمة المقارنة
  static Future<void> clearCompare() async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.delete(
      Uri.parse('${ApiConfig.compare}/clear'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      throw Exception('فشل في مسح قائمة المقارنة');
    }
  }
}
```


---

## المراجعات والتقييمات

### نماذج المراجعات

```dart
// lib/models/review/review.dart
import 'package:json_annotation/json_annotation.dart';

part 'review.g.dart';

@JsonSerializable()
class ProductReview {
  final int id;
  @JsonKey(name: 'product_id')
  final int productId;
  @JsonKey(name: 'customer_name')
  final String customerName;
  final String title;
  final String comment;
  final int rating;
  final String status;
  @JsonKey(name: 'created_at')
  final String createdAt;

  ProductReview({
    required this.id,
    required this.productId,
    required this.customerName,
    required this.title,
    required this.comment,
    required this.rating,
    required this.status,
    required this.createdAt,
  });

  factory ProductReview.fromJson(Map<String, dynamic> json) => 
      _$ProductReviewFromJson(json);
  Map<String, dynamic> toJson() => _$ProductReviewToJson(this);
}
```

### خدمة المراجعات API

```dart
// lib/services/api/review_api.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../config/api_config.dart';
import '../../models/review/review.dart';
import '../storage_service.dart';

class ReviewApi {
  /// الحصول على مراجعات منتج
  static Future<List<ProductReview>> getProductReviews(int productId) async {
    final response = await http.get(
      Uri.parse('${ApiConfig.reviews}/product/$productId'),
      headers: ApiConfig.defaultHeaders,
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List)
          .map((item) => ProductReview.fromJson(item))
          .toList();
    } else {
      throw Exception('فشل في جلب المراجعات');
    }
  }

  /// إضافة مراجعة لمنتج
  static Future<ProductReview> createReview({
    required int productId,
    required String title,
    required String comment,
    required int rating,
    String? name,
  }) async {
    final token = await StorageService.getToken();
    
    final headers = {
      ...ApiConfig.defaultHeaders,
      if (token != null) 'Authorization': 'Bearer $token',
    };

    final response = await http.post(
      Uri.parse(ApiConfig.createReview),
      headers: headers,
      body: jsonEncode({
        'product_id': productId,
        'title': title,
        'comment': comment,
        'rating': rating,
        if (name != null) 'name': name,
      }),
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200 || response.statusCode == 201) {
      final data = jsonDecode(response.body);
      return ProductReview.fromJson(data['data']);
    } else {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في إضافة المراجعة');
    }
  }

  /// تحديث مراجعة
  static Future<ProductReview> updateReview({
    required int reviewId,
    required String title,
    required String comment,
    required int rating,
  }) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.put(
      Uri.parse('${ApiConfig.reviews}/$reviewId'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({
        'title': title,
        'comment': comment,
        'rating': rating,
      }),
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return ProductReview.fromJson(data['data']);
    } else {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في تحديث المراجعة');
    }
  }

  /// حذف مراجعة
  static Future<void> deleteReview(int reviewId) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.delete(
      Uri.parse('${ApiConfig.reviews}/$reviewId'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode != 200) {
      throw Exception('فشل في حذف المراجعة');
    }
  }
}
```


---

## التحويل البنكي

### نماذج التحويل البنكي

```dart
// lib/models/payment/bank_transfer.dart
import 'dart:io';
import 'package:json_annotation/json_annotation.dart';

part 'bank_transfer.g.dart';

@JsonSerializable()
class BankTransferConfig {
  final bool enabled;
  final String title;
  final String description;
  @JsonKey(name: 'bank_name')
  final String bankName;
  @JsonKey(name: 'account_name')
  final String accountName;
  @JsonKey(name: 'account_number')
  final String accountNumber;
  @JsonKey(name: 'swift_code')
  final String? swiftCode;
  @JsonKey(name: 'iban')
  final String? iban;
  @JsonKey(name: 'branch_name')
  final String? branchName;
  @JsonKey(name: 'additional_info')
  final String? additionalInfo;

  BankTransferConfig({
    required this.enabled,
    required this.title,
    required this.description,
    required this.bankName,
    required this.accountName,
    required this.accountNumber,
    this.swiftCode,
    this.iban,
    this.branchName,
    this.additionalInfo,
  });

  factory BankTransferConfig.fromJson(Map<String, dynamic> json) => 
      _$BankTransferConfigFromJson(json);
  Map<String, dynamic> toJson() => _$BankTransferConfigToJson(this);
}

@JsonSerializable()
class BankTransferPayment {
  final int id;
  @JsonKey(name: 'order_id')
  final int? orderId;
  @JsonKey(name: 'transaction_reference')
  final String? transactionReference;
  @JsonKey(name: 'payment_proof_path')
  final String paymentProofPath;
  final String status;
  @JsonKey(name: 'rejection_reason')
  final String? rejectionReason;
  @JsonKey(name: 'created_at')
  final String createdAt;

  BankTransferPayment({
    required this.id,
    this.orderId,
    this.transactionReference,
    required this.paymentProofPath,
    required this.status,
    this.rejectionReason,
    required this.createdAt,
  });

  factory BankTransferPayment.fromJson(Map<String, dynamic> json) => 
      _$BankTransferPaymentFromJson(json);
  Map<String, dynamic> toJson() => _$BankTransferPaymentToJson(this);
}

@JsonSerializable()
class BankTransferStatistics {
  final int total;
  final int pending;
  final int approved;
  final int rejected;

  BankTransferStatistics({
    required this.total,
    required this.pending,
    required this.approved,
    required this.rejected,
  });

  factory BankTransferStatistics.fromJson(Map<String, dynamic> json) => 
      _$BankTransferStatisticsFromJson(json);
  Map<String, dynamic> toJson() => _$BankTransferStatisticsToJson(this);
}
```

### خدمة التحويل البنكي API

```dart
// lib/services/api/bank_transfer_api.dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:mime/mime.dart';
import 'package:path/path.dart' as path;
import '../../config/api_config.dart';
import '../../models/payment/bank_transfer.dart';
import '../storage_service.dart';

class BankTransferApi {
  /// الحصول على إعدادات التحويل البنكي
  static Future<BankTransferConfig> getConfig() async {
    final response = await http.get(
      Uri.parse(ApiConfig.bankTransferConfig),
      headers: ApiConfig.defaultHeaders,
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return BankTransferConfig.fromJson(data['data']);
    } else {
      throw Exception('فشل في جلب إعدادات التحويل البنكي');
    }
  }

  /// رفع إثبات الدفع وإنشاء الطلب
  static Future<Map<String, dynamic>> uploadPaymentProof({
    required File paymentProof,
    String? transactionReference,
  }) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    // التحقق من نوع الملف
    final mimeType = lookupMimeType(paymentProof.path);
    if (mimeType == null || 
        !['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']
            .contains(mimeType)) {
      throw Exception('نوع الملف غير مدعوم. يرجى رفع صورة أو PDF');
    }

    // التحقق من حجم الملف (5MB)
    final fileSize = await paymentProof.length();
    if (fileSize > 5 * 1024 * 1024) {
      throw Exception('حجم الملف كبير جداً. الحد الأقصى 5MB');
    }

    var request = http.MultipartRequest(
      'POST',
      Uri.parse(ApiConfig.bankTransferUpload),
    );

    request.headers.addAll({
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    });

    request.files.add(
      await http.MultipartFile.fromPath(
        'payment_proof',
        paymentProof.path,
        filename: path.basename(paymentProof.path),
      ),
    );

    if (transactionReference != null) {
      request.fields['transaction_reference'] = transactionReference;
    }

    final streamedResponse = await request.send()
        .timeout(ApiConfig.connectTimeout);
    final response = await http.Response.fromStream(streamedResponse);

    if (response.statusCode == 200 || response.statusCode == 201) {
      final data = jsonDecode(response.body);
      return data;
    } else {
      final data = jsonDecode(response.body);
      throw Exception(data['message'] ?? 'فشل في رفع إثبات الدفع');
    }
  }

  /// الحصول على قائمة المدفوعات
  static Future<List<BankTransferPayment>> getPayments() async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.get(
      Uri.parse(ApiConfig.bankTransferPayments),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List)
          .map((item) => BankTransferPayment.fromJson(item))
          .toList();
    } else {
      throw Exception('فشل في جلب المدفوعات');
    }
  }

  /// الحصول على تفاصيل دفعة معينة
  static Future<BankTransferPayment> getPaymentDetails(int paymentId) async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.get(
      Uri.parse('${ApiConfig.bankTransferPayments}/$paymentId'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return BankTransferPayment.fromJson(data['data']);
    } else {
      throw Exception('فشل في جلب تفاصيل الدفعة');
    }
  }

  /// الحصول على إحصائيات المدفوعات
  static Future<BankTransferStatistics> getStatistics() async {
    final token = await StorageService.getToken();
    if (token == null) throw Exception('يجب تسجيل الدخول أولاً');

    final response = await http.get(
      Uri.parse('${ApiConfig.bankTransfer}/statistics'),
      headers: {
        ...ApiConfig.defaultHeaders,
        'Authorization': 'Bearer $token',
      },
    ).timeout(ApiConfig.connectTimeout);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return BankTransferStatistics.fromJson(data['data']);
    } else {
      throw Exception('فشل في جلب الإحصائيات');
    }
  }
}
```


---

## معالجة الأخطاء

### فئة معالجة الأخطاء

```dart
// lib/utils/api_exception.dart
class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final dynamic data;

  ApiException({
    required this.message,
    this.statusCode,
    this.data,
  });

  @override
  String toString() => message;
}

class NetworkException extends ApiException {
  NetworkException({String? message})
      : super(message: message ?? 'خطأ في الاتصال بالشبكة');
}

class UnauthorizedException extends ApiException {
  UnauthorizedException({String? message})
      : super(
          message: message ?? 'غير مصرح. يرجى تسجيل الدخول',
          statusCode: 401,
        );
}

class ValidationException extends ApiException {
  final Map<String, List<String>>? errors;

  ValidationException({
    String? message,
    this.errors,
  }) : super(
          message: message ?? 'خطأ في التحقق من البيانات',
          statusCode: 422,
        );
}

class NotFoundException extends ApiException {
  NotFoundException({String? message})
      : super(
          message: message ?? 'المورد غير موجود',
          statusCode: 404,
        );
}

class ServerException extends ApiException {
  ServerException({String? message})
      : super(
          message: message ?? 'خطأ في الخادم',
          statusCode: 500,
        );
}
```

### معالج الأخطاء العام

```dart
// lib/utils/error_handler.dart
import 'dart:io';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'api_exception.dart';

class ErrorHandler {
  static ApiException handleError(dynamic error) {
    if (error is SocketException) {
      return NetworkException(
        message: 'لا يوجد اتصال بالإنترنت',
      );
    }

    if (error is HttpException) {
      return NetworkException(
        message: 'خطأ في الاتصال بالخادم',
      );
    }

    if (error is FormatException) {
      return ApiException(
        message: 'خطأ في تنسيق البيانات',
      );
    }

    if (error is ApiException) {
      return error;
    }

    return ApiException(
      message: error.toString(),
    );
  }

  static ApiException handleResponse(http.Response response) {
    final statusCode = response.statusCode;
    
    try {
      final data = jsonDecode(response.body);
      final message = data['message'] ?? 'حدث خطأ غير متوقع';

      switch (statusCode) {
        case 400:
          return ApiException(
            message: message,
            statusCode: statusCode,
            data: data,
          );
        case 401:
          return UnauthorizedException(message: message);
        case 403:
          return ApiException(
            message: 'غير مسموح بالوصول',
            statusCode: statusCode,
          );
        case 404:
          return NotFoundException(message: message);
        case 422:
          return ValidationException(
            message: message,
            errors: data['errors'] != null
                ? Map<String, List<String>>.from(
                    data['errors'].map(
                      (key, value) => MapEntry(
                        key,
                        List<String>.from(value),
                      ),
                    ),
                  )
                : null,
          );
        case 500:
        case 502:
        case 503:
          return ServerException(message: message);
        default:
          return ApiException(
            message: message,
            statusCode: statusCode,
            data: data,
          );
      }
    } catch (e) {
      return ApiException(
        message: 'خطأ في معالجة الاستجابة',
        statusCode: statusCode,
      );
    }
  }

  static String getErrorMessage(dynamic error) {
    if (error is ValidationException && 
        error.errors != null && 
        error.errors!.isNotEmpty) {
      final firstError = error.errors!.values.first;
      if (firstError.isNotEmpty) {
        return firstError.first;
      }
      return error.message;
    }
    
    if (error is ApiException) {
      return error.message;
    }

    return 'حدث خطأ غير متوقع';
  }
}
```

### مثال على الاستخدام مع معالجة الأخطاء

```dart
// lib/screens/products/product_list_screen.dart
import 'package:flutter/material.dart';
import '../../services/api/product_api.dart';
import '../../models/product/product.dart';
import '../../utils/error_handler.dart';

class ProductListScreen extends StatefulWidget {
  @override
  _ProductListScreenState createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  List<Product> products = [];
  bool isLoading = false;
  String? errorMessage;

  @override
  void initState() {
    super.initState();
    loadProducts();
  }

  Future<void> loadProducts() async {
    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    try {
      final result = await ProductApi.getProducts();
      setState(() {
        products = result['products'];
        isLoading = false;
      });
    } catch (error) {
      final apiError = ErrorHandler.handleError(error);
      setState(() {
        errorMessage = ErrorHandler.getErrorMessage(apiError);
        isLoading = false;
      });

      // عرض رسالة خطأ
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(errorMessage!),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('المنتجات')),
      body: isLoading
          ? Center(child: CircularProgressIndicator())
          : errorMessage != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.error_outline, size: 64, color: Colors.red),
                      SizedBox(height: 16),
                      Text(errorMessage!),
                      SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: loadProducts,
                        child: Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                )
              : ListView.builder(
                  itemCount: products.length,
                  itemBuilder: (context, index) {
                    final product = products[index];
                    return ListTile(
                      title: Text(product.name),
                      subtitle: Text('${product.finalPrice} ر.س'),
                      leading: product.images != null &&
                              product.images!.isNotEmpty
                          ? Image.network(product.images!.first.url)
                          : null,
                    );
                  },
                ),
    );
  }
}
```


---

## أمثلة واجهة المستخدم

### 1. شاشة تسجيل الدخول

```dart
// lib/screens/auth/login_screen.dart
import 'package:flutter/material.dart';
import '../../services/api/auth_api.dart';
import '../../utils/error_handler.dart';

class LoginScreen extends StatefulWidget {
  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  bool _obscurePassword = true;

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      await AuthApi.login(
        email: _emailController.text.trim(),
        password: _passwordController.text,
      );

      // الانتقال إلى الشاشة الرئيسية
      Navigator.of(context).pushReplacementNamed('/home');
    } catch (error) {
      final apiError = ErrorHandler.handleError(error);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ErrorHandler.getErrorMessage(apiError)),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('تسجيل الدخول')),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              SizedBox(height: 32),
              Icon(Icons.medical_services, size: 80, color: Colors.blue),
              SizedBox(height: 32),
              TextFormField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  labelText: 'البريد الإلكتروني',
                  prefixIcon: Icon(Icons.email),
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'يرجى إدخال البريد الإلكتروني';
                  }
                  if (!value.contains('@')) {
                    return 'بريد إلكتروني غير صالح';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _passwordController,
                obscureText: _obscurePassword,
                decoration: InputDecoration(
                  labelText: 'كلمة المرور',
                  prefixIcon: Icon(Icons.lock),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscurePassword
                          ? Icons.visibility
                          : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() => _obscurePassword = !_obscurePassword);
                    },
                  ),
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'يرجى إدخال كلمة المرور';
                  }
                  if (value.length < 6) {
                    return 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
                  }
                  return null;
                },
              ),
              SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isLoading ? null : _login,
                style: ElevatedButton.styleFrom(
                  padding: EdgeInsets.symmetric(vertical: 16),
                ),
                child: _isLoading
                    ? CircularProgressIndicator(color: Colors.white)
                    : Text('تسجيل الدخول', style: TextStyle(fontSize: 16)),
              ),
              SizedBox(height: 16),
              TextButton(
                onPressed: () {
                  Navigator.of(context).pushNamed('/forgot-password');
                },
                child: Text('نسيت كلمة المرور؟'),
              ),
              TextButton(
                onPressed: () {
                  Navigator.of(context).pushNamed('/register');
                },
                child: Text('إنشاء حساب جديد'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }
}
```

### 2. شاشة قائمة المنتجات

```dart
// lib/screens/products/product_list_screen.dart
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../services/api/product_api.dart';
import '../../models/product/product.dart';
import '../../utils/error_handler.dart';

class ProductListScreen extends StatefulWidget {
  final int? categoryId;

  ProductListScreen({this.categoryId});

  @override
  _ProductListScreenState createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  List<Product> products = [];
  bool isLoading = false;
  bool hasMore = true;
  int currentPage = 1;
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    loadProducts();
    _scrollController.addListener(_onScroll);
  }

  void _onScroll() {
    if (_scrollController.position.pixels ==
        _scrollController.position.maxScrollExtent) {
      if (!isLoading && hasMore) {
        loadProducts(loadMore: true);
      }
    }
  }

  Future<void> loadProducts({bool loadMore = false}) async {
    if (isLoading) return;

    setState(() => isLoading = true);

    try {
      final result = await ProductApi.getProducts(
        page: loadMore ? currentPage + 1 : 1,
        categoryId: widget.categoryId,
      );

      setState(() {
        if (loadMore) {
          products.addAll(result['products']);
          currentPage++;
        } else {
          products = result['products'];
          currentPage = 1;
        }
        hasMore = currentPage < result['last_page'];
        isLoading = false;
      });
    } catch (error) {
      final apiError = ErrorHandler.handleError(error);
      setState(() => isLoading = false);
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ErrorHandler.getErrorMessage(apiError)),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('المنتجات'),
        actions: [
          IconButton(
            icon: Icon(Icons.search),
            onPressed: () {
              Navigator.of(context).pushNamed('/search');
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => loadProducts(),
        child: products.isEmpty && isLoading
            ? Center(child: CircularProgressIndicator())
            : GridView.builder(
                controller: _scrollController,
                padding: EdgeInsets.all(8),
                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  childAspectRatio: 0.7,
                  crossAxisSpacing: 8,
                  mainAxisSpacing: 8,
                ),
                itemCount: products.length + (hasMore ? 1 : 0),
                itemBuilder: (context, index) {
                  if (index == products.length) {
                    return Center(child: CircularProgressIndicator());
                  }

                  final product = products[index];
                  return ProductCard(product: product);
                },
              ),
      ),
    );
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }
}

class ProductCard extends StatelessWidget {
  final Product product;

  ProductCard({required this.product});

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () {
          Navigator.of(context).pushNamed(
            '/product-details',
            arguments: product.id,
          );
        },
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Stack(
                children: [
                  CachedNetworkImage(
                    imageUrl: (product.images != null && product.images!.isNotEmpty) 
                        ? product.images!.first.url 
                        : '',
                    width: double.infinity,
                    fit: BoxFit.cover,
                    placeholder: (context, url) =>
                        Center(child: CircularProgressIndicator()),
                    errorWidget: (context, url, error) =>
                        Icon(Icons.image_not_supported),
                  ),
                  if (product.hasDiscount)
                    Positioned(
                      top: 8,
                      right: 8,
                      child: Container(
                        padding: EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.red,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          '-${product.discountPercentage.toStringAsFixed(0)}%',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                  if (!product.inStock)
                    Positioned.fill(
                      child: Container(
                        color: Colors.black54,
                        child: Center(
                          child: Text(
                            'غير متوفر',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            Padding(
              padding: EdgeInsets.all(8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                  SizedBox(height: 4),
                  Row(
                    children: [
                      if (product.hasDiscount) ...[
                        Text(
                          '${product.price} ر.س',
                          style: TextStyle(
                            decoration: TextDecoration.lineThrough,
                            color: Colors.grey,
                            fontSize: 12,
                          ),
                        ),
                        SizedBox(width: 4),
                      ],
                      Text(
                        '${product.finalPrice} ر.س',
                        style: TextStyle(
                          color: Colors.blue,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
```

### 3. شاشة السلة

```dart
// lib/screens/cart/cart_screen.dart
import 'package:flutter/material.dart';
import '../../services/api/cart_api.dart';
import '../../models/cart/cart.dart';
import '../../utils/error_handler.dart';

class CartScreen extends StatefulWidget {
  @override
  _CartScreenState createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  Cart? cart;
  bool isLoading = false;

  @override
  void initState() {
    super.initState();
    loadCart();
  }

  Future<void> loadCart() async {
    setState(() => isLoading = true);

    try {
      final result = await CartApi.getCart();
      setState(() {
        cart = result;
        isLoading = false;
      });
    } catch (error) {
      final apiError = ErrorHandler.handleError(error);
      setState(() => isLoading = false);
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ErrorHandler.getErrorMessage(apiError)),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> updateQuantity(int itemId, int quantity) async {
    try {
      final result = await CartApi.updateCartItem(
        cartItemId: itemId,
        quantity: quantity,
      );
      setState(() => cart = result);
    } catch (error) {
      final apiError = ErrorHandler.handleError(error);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ErrorHandler.getErrorMessage(apiError)),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> removeItem(int itemId) async {
    try {
      final result = await CartApi.removeFromCart(itemId);
      setState(() => cart = result);
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('تم حذف المنتج من السلة')),
      );
    } catch (error) {
      final apiError = ErrorHandler.handleError(error);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ErrorHandler.getErrorMessage(apiError)),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('السلة'),
      ),
      body: isLoading
          ? Center(child: CircularProgressIndicator())
          : cart == null || cart!.items.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.shopping_cart_outlined,
                          size: 80, color: Colors.grey),
                      SizedBox(height: 16),
                      Text('السلة فارغة'),
                      SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () {
                          Navigator.of(context).pushNamed('/products');
                        },
                        child: Text('تصفح المنتجات'),
                      ),
                    ],
                  ),
                )
              : Column(
                  children: [
                    Expanded(
                      child: ListView.builder(
                        itemCount: cart!.items.length,
                        itemBuilder: (context, index) {
                          final item = cart!.items[index];
                          return CartItemWidget(
                            item: item,
                            onUpdateQuantity: (quantity) {
                              updateQuantity(item.id, quantity);
                            },
                            onRemove: () {
                              removeItem(item.id);
                            },
                          );
                        },
                      ),
                    ),
                    Container(
                      padding: EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black12,
                            blurRadius: 4,
                            offset: Offset(0, -2),
                          ),
                        ],
                      ),
                      child: Column(
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text('المجموع الفرعي:'),
                              Text('${cart!.subTotal} ر.س'),
                            ],
                          ),
                          if (cart!.discountAmount > 0) ...[
                            SizedBox(height: 8),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text('الخصم:'),
                                Text(
                                  '-${cart!.discountAmount} ر.س',
                                  style: TextStyle(color: Colors.green),
                                ),
                              ],
                            ),
                          ],
                          SizedBox(height: 8),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text('الضريبة:'),
                              Text('${cart!.taxTotal} ر.س'),
                            ],
                          ),
                          Divider(height: 24),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'الإجمالي:',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              Text(
                                '${cart!.grandTotal} ر.س',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.blue,
                                ),
                              ),
                            ],
                          ),
                          SizedBox(height: 16),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton(
                              onPressed: () {
                                Navigator.of(context).pushNamed('/checkout');
                              },
                              style: ElevatedButton.styleFrom(
                                padding: EdgeInsets.symmetric(vertical: 16),
                              ),
                              child: Text(
                                'إتمام الطلب',
                                style: TextStyle(fontSize: 16),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
    );
  }
}

class CartItemWidget extends StatelessWidget {
  final CartItem item;
  final Function(int) onUpdateQuantity;
  final VoidCallback onRemove;

  CartItemWidget({
    required this.item,
    required this.onUpdateQuantity,
    required this.onRemove,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: EdgeInsets.all(12),
        child: Row(
          children: [
            if (item.productImage != null)
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: Image.network(
                  item.productImage!,
                  width: 80,
                  height: 80,
                  fit: BoxFit.cover,
                ),
              ),
            SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.name,
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                  SizedBox(height: 4),
                  Text(
                    '${item.price} ر.س',
                    style: TextStyle(color: Colors.blue),
                  ),
                  SizedBox(height: 8),
                  Row(
                    children: [
                      IconButton(
                        icon: Icon(Icons.remove_circle_outline),
                        onPressed: item.quantity > 1
                            ? () => onUpdateQuantity(item.quantity - 1)
                            : null,
                      ),
                      Text(
                        '${item.quantity}',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      IconButton(
                        icon: Icon(Icons.add_circle_outline),
                        onPressed: () => onUpdateQuantity(item.quantity + 1),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            Column(
              children: [
                IconButton(
                  icon: Icon(Icons.delete_outline, color: Colors.red),
                  onPressed: onRemove,
                ),
                SizedBox(height: 8),
                Text(
                  '${item.total} ر.س',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
```


### 4. شاشة رفع إثبات التحويل البنكي

```dart
// lib/screens/payment/bank_transfer_upload_screen.dart
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import '../../services/api/bank_transfer_api.dart';
import '../../models/payment/bank_transfer.dart';
import '../../utils/error_handler.dart';

class BankTransferUploadScreen extends StatefulWidget {
  @override
  _BankTransferUploadScreenState createState() =>
      _BankTransferUploadScreenState();
}

class _BankTransferUploadScreenState extends State<BankTransferUploadScreen> {
  BankTransferConfig? config;
  File? selectedFile;
  final _transactionRefController = TextEditingController();
  bool isLoading = false;
  bool isUploading = false;

  @override
  void initState() {
    super.initState();
    loadConfig();
  }

  Future<void> loadConfig() async {
    setState(() => isLoading = true);

    try {
      final result = await BankTransferApi.getConfig();
      setState(() {
        config = result;
        isLoading = false;
      });
    } catch (error) {
      final apiError = ErrorHandler.handleError(error);
      setState(() => isLoading = false);
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ErrorHandler.getErrorMessage(apiError)),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> pickFile() async {
    try {
      FilePickerResult? result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['jpg', 'jpeg', 'png', 'pdf'],
      );

      if (result != null) {
        final file = File(result.files.single.path!);
        final fileSize = await file.length();

        // التحقق من حجم الملف (5MB)
        if (fileSize > 5 * 1024 * 1024) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('حجم الملف كبير جداً. الحد الأقصى 5MB'),
              backgroundColor: Colors.red,
            ),
          );
          return;
        }

        setState(() => selectedFile = file);
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('فشل في اختيار الملف'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> uploadProof() async {
    if (selectedFile == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('يرجى اختيار ملف إثبات الدفع'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() => isUploading = true);

    try {
      final result = await BankTransferApi.uploadPaymentProof(
        paymentProof: selectedFile!,
        transactionReference: _transactionRefController.text.trim().isEmpty
            ? null
            : _transactionRefController.text.trim(),
      );

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('تم رفع إثبات الدفع بنجاح'),
          backgroundColor: Colors.green,
        ),
      );

      // الانتقال إلى شاشة تأكيد الطلب
      Navigator.of(context).pushReplacementNamed(
        '/order-confirmation',
        arguments: result['order'],
      );
    } catch (error) {
      final apiError = ErrorHandler.handleError(error);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ErrorHandler.getErrorMessage(apiError)),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() => isUploading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('التحويل البنكي')),
      body: isLoading
          ? Center(child: CircularProgressIndicator())
          : config == null
              ? Center(child: Text('فشل في تحميل البيانات'))
              : SingleChildScrollView(
                  padding: EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Card(
                        child: Padding(
                          padding: EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'معلومات الحساب البنكي',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              Divider(height: 24),
                              _buildInfoRow('اسم البنك:', config!.bankName),
                              _buildInfoRow('اسم الحساب:', config!.accountName),
                              _buildInfoRow(
                                  'رقم الحساب:', config!.accountNumber),
                              if (config!.iban != null)
                                _buildInfoRow('IBAN:', config!.iban!),
                              if (config!.swiftCode != null)
                                _buildInfoRow(
                                    'Swift Code:', config!.swiftCode!),
                              if (config!.branchName != null)
                                _buildInfoRow('الفرع:', config!.branchName!),
                              if (config!.additionalInfo != null) ...[
                                SizedBox(height: 16),
                                Text(
                                  config!.additionalInfo!,
                                  style: TextStyle(color: Colors.grey[600]),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ),
                      SizedBox(height: 24),
                      Text(
                        'رفع إثبات الدفع',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      SizedBox(height: 16),
                      TextField(
                        controller: _transactionRefController,
                        decoration: InputDecoration(
                          labelText: 'رقم المعاملة (اختياري)',
                          hintText: 'أدخل رقم المعاملة البنكية',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.receipt),
                        ),
                      ),
                      SizedBox(height: 16),
                      if (selectedFile != null)
                        Card(
                          child: ListTile(
                            leading: Icon(Icons.insert_drive_file),
                            title: Text(selectedFile!.path.split('/').last),
                            trailing: IconButton(
                              icon: Icon(Icons.close),
                              onPressed: () {
                                setState(() => selectedFile = null);
                              },
                            ),
                          ),
                        ),
                      SizedBox(height: 16),
                      OutlinedButton.icon(
                        onPressed: isUploading ? null : pickFile,
                        icon: Icon(Icons.attach_file),
                        label: Text('اختيار ملف (JPG, PNG, PDF)'),
                        style: OutlinedButton.styleFrom(
                          padding: EdgeInsets.symmetric(vertical: 16),
                        ),
                      ),
                      SizedBox(height: 8),
                      Text(
                        'الحد الأقصى لحجم الملف: 5MB',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey,
                        ),
                        textAlign: TextAlign.center,
                      ),
                      SizedBox(height: 24),
                      ElevatedButton(
                        onPressed: isUploading ? null : uploadProof,
                        style: ElevatedButton.styleFrom(
                          padding: EdgeInsets.symmetric(vertical: 16),
                        ),
                        child: isUploading
                            ? CircularProgressIndicator(color: Colors.white)
                            : Text(
                                'رفع إثبات الدفع وإتمام الطلب',
                                style: TextStyle(fontSize: 16),
                              ),
                      ),
                    ],
                  ),
                ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: TextStyle(
                fontWeight: FontWeight.bold,
                color: Colors.grey[700],
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(fontSize: 16),
            ),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _transactionRefController.dispose();
    super.dispose();
  }
}
```

---

## إدارة الحالة باستخدام Provider

### 1. Auth Provider

```dart
// lib/providers/auth_provider.dart
import 'package:flutter/foundation.dart';
import '../services/api/auth_api.dart';
import '../services/storage_service.dart';
import '../models/customer/customer.dart';

class AuthProvider with ChangeNotifier {
  Customer? _customer;
  bool _isAuthenticated = false;
  bool _isLoading = false;

  Customer? get customer => _customer;
  bool get isAuthenticated => _isAuthenticated;
  bool get isLoading => _isLoading;

  Future<void> checkAuthStatus() async {
    _isLoading = true;
    notifyListeners();

    try {
      final token = await StorageService.getToken();
      if (token != null) {
        final customerData = await StorageService.getCustomer();
        if (customerData != null) {
          _customer = Customer.fromJson(customerData);
          _isAuthenticated = true;
        }
      }
    } catch (e) {
      _isAuthenticated = false;
      _customer = null;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> login(String email, String password) async {
    _isLoading = true;
    notifyListeners();

    try {
      final result = await AuthApi.login(email: email, password: password);
      _customer = Customer.fromJson(result['data']);
      _isAuthenticated = true;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> register({
    required String firstName,
    required String lastName,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
  }) async {
    _isLoading = true;
    notifyListeners();

    try {
      final result = await AuthApi.register(
        firstName: firstName,
        lastName: lastName,
        email: email,
        password: password,
        passwordConfirmation: passwordConfirmation,
        phone: phone,
      );
      _customer = Customer.fromJson(result['data']);
      _isAuthenticated = true;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();

    try {
      await AuthApi.logout();
    } finally {
      _customer = null;
      _isAuthenticated = false;
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> refreshProfile() async {
    try {
      _customer = await AuthApi.getProfile();
      notifyListeners();
    } catch (e) {
      // Handle error
    }
  }
}
```

### 2. Cart Provider

```dart
// lib/providers/cart_provider.dart
import 'package:flutter/foundation.dart';
import '../services/api/cart_api.dart';
import '../models/cart/cart.dart';

class CartProvider with ChangeNotifier {
  Cart? _cart;
  bool _isLoading = false;

  Cart? get cart => _cart;
  bool get isLoading => _isLoading;
  int get itemCount => _cart?.itemsCount ?? 0;
  double get total => _cart?.grandTotal ?? 0;

  Future<void> loadCart() async {
    _isLoading = true;
    notifyListeners();

    try {
      _cart = await CartApi.getCart();
    } catch (e) {
      // Handle error
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> addToCart({
    required int productId,
    required int quantity,
    Map<String, dynamic>? options,
  }) async {
    try {
      _cart = await CartApi.addToCart(
        productId: productId,
        quantity: quantity,
        options: options,
      );
      notifyListeners();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> updateQuantity(int cartItemId, int quantity) async {
    try {
      _cart = await CartApi.updateCartItem(
        cartItemId: cartItemId,
        quantity: quantity,
      );
      notifyListeners();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> removeItem(int cartItemId) async {
    try {
      _cart = await CartApi.removeFromCart(cartItemId);
      notifyListeners();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> applyCoupon(String couponCode) async {
    try {
      _cart = await CartApi.applyCoupon(couponCode);
      notifyListeners();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> removeCoupon() async {
    try {
      _cart = await CartApi.removeCoupon();
      notifyListeners();
    } catch (e) {
      rethrow;
    }
  }

  void clearCart() {
    _cart = null;
    notifyListeners();
  }
}
```

### 3. إعداد Providers في التطبيق

```dart
// lib/main.dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'providers/cart_provider.dart';
import 'screens/auth/login_screen.dart';
import 'screens/products/product_list_screen.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
      ],
      child: MaterialApp(
        title: 'MedSDN',
        theme: ThemeData(
          primarySwatch: Colors.blue,
          fontFamily: 'Cairo',
        ),
        home: SplashScreen(),
        routes: {
          '/login': (context) => LoginScreen(),
          '/products': (context) => ProductListScreen(),
          // Add more routes
        },
      ),
    );
  }
}

class SplashScreen extends StatefulWidget {
  @override
  _SplashScreenState createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    checkAuth();
  }

  Future<void> checkAuth() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    await authProvider.checkAuthStatus();

    if (authProvider.isAuthenticated) {
      final cartProvider = Provider.of<CartProvider>(context, listen: false);
      await cartProvider.loadCart();
      Navigator.of(context).pushReplacementNamed('/products');
    } else {
      Navigator.of(context).pushReplacementNamed('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: CircularProgressIndicator(),
      ),
    );
  }
}
```

---

## الخلاصة

هذا الدليل يغطي جميع نقاط REST API الأساسية في MedSDN:

✅ **المصادقة**: تسجيل الدخول، التسجيل، تسجيل الخروج، إدارة الملف الشخصي
✅ **المنتجات**: عرض المنتجات، البحث، الفئات، التفاصيل
✅ **السلة**: إضافة، تحديث، حذف، تطبيق كوبونات
✅ **الدفع**: العناوين، طرق الشحن، طرق الدفع، إتمام الطلب
✅ **الطلبات**: عرض الطلبات، التفاصيل، الإلغاء، إعادة الطلب
✅ **قائمة الأمنيات**: إضافة، حذف، نقل إلى السلة
✅ **المقارنة**: إضافة، حذف، عرض المقارنة
✅ **المراجعات**: عرض، إضافة، تحديث، حذف
✅ **التحويل البنكي**: الإعدادات، رفع إثبات الدفع، المدفوعات، الإحصائيات
✅ **معالجة الأخطاء**: معالج شامل للأخطاء
✅ **أمثلة UI**: شاشات كاملة جاهزة للاستخدام
✅ **إدارة الحالة**: Provider patterns

### ملاحظات مهمة:

1. **الأمان**: جميع الطلبات تستخدم HTTPS و Bearer Token للمصادقة
2. **التخزين**: استخدام Flutter Secure Storage للبيانات الحساسة
3. **معالجة الأخطاء**: معالجة شاملة لجميع أنواع الأخطاء
4. **التحقق من الملفات**: التحقق من نوع وحجم الملفات قبل الرفع
5. **Rate Limiting**: الالتزام بحدود معدل الطلبات (5 طلبات/دقيقة للتحويل البنكي)

### الخطوات التالية:

1. تشغيل `flutter pub get` لتثبيت التبعيات
2. تشغيل `flutter pub run build_runner build` لتوليد ملفات JSON
3. تحديث `ApiConfig.baseUrl` بعنوان الخادم الخاص بك
4. اختبار جميع نقاط API
5. تخصيص واجهة المستخدم حسب احتياجاتك

للمزيد من المعلومات، راجع:
- [MedSDN API Documentation](../API_DOCUMENTATION.md)
- [Bank Transfer Integration](../BANK_TRANSFER_INTEGRATION_AR.md)
- [GraphQL API Guide](../BANK_TRANSFER_GRAPHQL.md)
