import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

void main() {
  runApp(const MaxConSalesRepApp());
}

class MaxConSalesRepApp extends StatelessWidget {
  const MaxConSalesRepApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'MaxCon Sales Rep',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        primarySwatch: Colors.purple,
        fontFamily: 'Arial',
      ),
      home: const LoginScreen(),
    );
  }
}

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController = TextEditingController(text: 'admin@maxcon-erp.com');
  final _passwordController = TextEditingController(text: 'MaxCon@2025');
  bool _isLoading = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Logo
              Container(
                width: 120,
                height: 120,
                decoration: BoxDecoration(
                  color: Colors.purple,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Icon(
                  Icons.business,
                  size: 60,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 30),
              
              // Title
              const Text(
                'MaxCon ERP',
                style: TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: Colors.purple,
                ),
              ),
              const Text(
                'تطبيق مندوبي المبيعات',
                style: TextStyle(
                  fontSize: 16,
                  color: Colors.grey,
                ),
              ),
              const SizedBox(height: 40),
              
              // Email field
              TextField(
                controller: _emailController,
                decoration: InputDecoration(
                  labelText: 'البريد الإلكتروني',
                  prefixIcon: const Icon(Icons.email),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              
              // Password field
              TextField(
                controller: _passwordController,
                obscureText: true,
                decoration: InputDecoration(
                  labelText: 'كلمة المرور',
                  prefixIcon: const Icon(Icons.lock),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              ),
              const SizedBox(height: 30),
              
              // Login button
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _login,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.purple,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  child: _isLoading
                      ? const CircularProgressIndicator(color: Colors.white)
                      : const Text(
                          'تسجيل الدخول',
                          style: TextStyle(
                            fontSize: 18,
                            color: Colors.white,
                          ),
                        ),
                ),
              ),
              const SizedBox(height: 20),
              
              // API Test button
              SizedBox(
                width: double.infinity,
                height: 50,
                child: OutlinedButton(
                  onPressed: _isLoading ? null : _testApi,
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Colors.purple),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  child: const Text(
                    'اختبار الاتصال بـ API',
                    style: TextStyle(
                      fontSize: 16,
                      color: Colors.purple,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              
              // Demo credentials
              Container(
                padding: const EdgeInsets.all(15),
                decoration: BoxDecoration(
                  color: Colors.blue[50],
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: Colors.blue[200]!),
                ),
                child: const Column(
                  children: [
                    Text(
                      'بيانات تجريبية:',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        color: Colors.blue,
                      ),
                    ),
                    SizedBox(height: 5),
                    Text('البريد: admin@maxcon-erp.com'),
                    Text('كلمة المرور: MaxCon@2025'),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _login() async {
    if (_emailController.text.isEmpty || _passwordController.text.isEmpty) {
      _showMessage('يرجى إدخال البريد الإلكتروني وكلمة المرور', false);
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      // Check demo credentials
      if (_emailController.text == 'admin@maxcon-erp.com' && 
          _passwordController.text == 'MaxCon@2025') {
        
        await Future.delayed(const Duration(seconds: 2));
        _showMessage('تم تسجيل الدخول بنجاح!', true);
        
        if (mounted) {
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (context) => const DashboardScreen()),
          );
        }
      } else {
        _showMessage('البريد الإلكتروني أو كلمة المرور غير صحيحة', false);
      }
    } catch (e) {
      _showMessage('حدث خطأ أثناء تسجيل الدخول', false);
    }

    setState(() {
      _isLoading = false;
    });
  }

  void _testApi() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final response = await http.get(
        Uri.parse('https://phpstack-1486247-5676575.cloudwaysapps.com/api/test/sales-reps'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        _showMessage('✅ الاتصال بـ API نجح! البيانات: ${data['message']}', true);
      } else {
        _showMessage('❌ فشل الاتصال بـ API. كود الخطأ: ${response.statusCode}', false);
      }
    } catch (e) {
      _showMessage('❌ خطأ في الاتصال: $e', false);
    }

    setState(() {
      _isLoading = false;
    });
  }

  void _showMessage(String message, bool isSuccess) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isSuccess ? Colors.green : Colors.red,
        duration: const Duration(seconds: 3),
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

class DashboardScreen extends StatelessWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('لوحة التحكم'),
        backgroundColor: Colors.purple,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () {
              Navigator.pushReplacement(
                context,
                MaterialPageRoute(builder: (context) => const LoginScreen()),
              );
            },
          ),
        ],
      ),
      body: const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.check_circle,
              size: 100,
              color: Colors.green,
            ),
            SizedBox(height: 20),
            Text(
              'مرحباً بك في تطبيق مندوبي المبيعات!',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
              textAlign: TextAlign.center,
            ),
            SizedBox(height: 10),
            Text(
              'تم تسجيل الدخول بنجاح',
              style: TextStyle(
                fontSize: 16,
                color: Colors.grey,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
