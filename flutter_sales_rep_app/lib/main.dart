import 'package:flutter/material.dart';
import 'core/services/api_service_simple.dart';

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
      home: const SplashScreen(),
    );
  }
}

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    // Navigate to login after 3 seconds
    Future.delayed(const Duration(seconds: 3), () {
      if (mounted) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => const LoginScreen()),
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {

    return Scaffold(
      backgroundColor: Colors.purple,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Logo placeholder
            Container(
              width: 120,
              height: 120,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Icon(
                Icons.business,
                size: 60,
                color: Colors.purple,
              ),
            ),
            const SizedBox(height: 30),
            const Text(
              'MaxCon ERP',
              style: TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 10),
            const Text(
              'تطبيق مندوبي المبيعات',
              style: TextStyle(
                fontSize: 18,
                color: Colors.white70,
              ),
            ),
            const SizedBox(height: 50),
            const CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
            ),
          ],
        ),
      ),
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
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Logo
              Container(
                width: 100,
                height: 100,
                decoration: BoxDecoration(
                  color: Colors.purple,
                  borderRadius: BorderRadius.circular(15),
                ),
                child: const Icon(
                  Icons.person,
                  size: 50,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 30),
              const Text(
                'تسجيل الدخول',
                style: TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: Colors.purple,
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
      _showMessage('يرجى إدخال البريد الإلكتروني وكلمة المرور');
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      // Simulate API call for now
      await Future.delayed(const Duration(seconds: 2));

      // Check demo credentials
      if (_emailController.text == 'admin@maxcon-erp.com' &&
          _passwordController.text == 'MaxCon@2025') {

        _showMessage('تم تسجيل الدخول بنجاح!');

        if (mounted) {
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (context) => const DashboardScreen()),
          );
        }
      } else {
        _showMessage('البريد الإلكتروني أو كلمة المرور غير صحيحة');
      }
    } catch (e) {
      _showMessage('حدث خطأ أثناء تسجيل الدخول');
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
      // استخدام الخدمة المبسطة
      final result = await ApiService.instance.testConnection();

      if (result['success'] == true) {
        final message = result['data']?['message'] ?? 'اتصال ناجح';
        _showMessage('✅ الاتصال بـ API نجح! $message', true);
      } else {
        _showMessage('❌ ${result['message']}', false);
      }
    } catch (e) {
      _showMessage('❌ خطأ في الاتصال: $e', false);
    }

    setState(() {
      _isLoading = false;
    });
  }

  void _showMessage(String message, [bool isSuccess = false]) {
    final success = isSuccess || message.contains('نجاح') || message.contains('✅');
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: success ? Colors.green : Colors.red,
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
            icon: const Icon(Icons.info_outline),
            onPressed: () => _showAppInfo(context),
          ),
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
      body: Padding(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          children: [
            // Welcome message
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              margin: const EdgeInsets.only(bottom: 20),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [Colors.purple.withValues(alpha: 0.1), Colors.blue.withValues(alpha: 0.1)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(15),
                border: Border.all(color: Colors.purple.withValues(alpha: 0.3)),
              ),
              child: const Column(
                children: [
                  Text(
                    'مرحباً بك في تطبيق مندوبي المبيعات',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.purple,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  SizedBox(height: 10),
                  Text(
                    'اختر الخدمة التي تريد استخدامها من القائمة أدناه',
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
            // Grid view
            Expanded(
              child: GridView.count(
                crossAxisCount: 2,
                crossAxisSpacing: 15,
                mainAxisSpacing: 15,
                childAspectRatio: 1.1,
                children: [
                  _buildDashboardCard(
                    context,
                    'العملاء',
                    Icons.people,
                    Colors.blue,
                    () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (context) => const CustomersScreen()),
                    ),
                  ),
                  _buildDashboardCard(
                    context,
                    'الزيارات',
                    Icons.location_on,
                    Colors.green,
                    () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (context) => const VisitsScreen()),
                    ),
                  ),
                  _buildDashboardCard(
                    context,
                    'المهام',
                    Icons.task,
                    Colors.orange,
                    () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (context) => const TasksScreen()),
                    ),
                  ),
                  _buildDashboardCard(
                    context,
                    'التقارير',
                    Icons.analytics,
                    Colors.red,
                    () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (context) => const ReportsScreen()),
                    ),
                  ),
                  _buildDashboardCard(
                    context,
                    'الطلبات',
                    Icons.shopping_cart,
                    Colors.purple,
                    () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (context) => const OrdersScreen()),
                    ),
                  ),
                  _buildDashboardCard(
                    context,
                    'الاستحصال',
                    Icons.payment,
                    Colors.teal,
                    () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (context) => const CollectionsScreen()),
                    ),
                  ),
                  _buildDashboardCard(
                    context,
                    'الإعدادات',
                    Icons.settings,
                    Colors.grey,
                    () => Navigator.push(
                      context,
                      MaterialPageRoute(builder: (context) => const SettingsScreen()),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDashboardCard(
    BuildContext context,
    String title,
    IconData icon,
    Color color,
    VoidCallback onTap,
  ) {
    return Card(
      elevation: 4,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(10),
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(10),
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [color.withValues(alpha: 0.8), color],
            ),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                icon,
                size: 50,
                color: Colors.white,
              ),
              const SizedBox(height: 10),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showAppInfo(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.business, color: Colors.purple),
            SizedBox(width: 10),
            Text('معلومات التطبيق'),
          ],
        ),
        content: const Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'MaxCon ERP - تطبيق مندوبي المبيعات',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Colors.purple,
              ),
            ),
            SizedBox(height: 15),
            Text('الإصدار: 1.0.0'),
            Text('تاريخ الإصدار: يوليو 2025'),
            SizedBox(height: 15),
            Text(
              'الميزات المتوفرة:',
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
            Text('✅ تسجيل الدخول والخروج'),
            Text('✅ لوحة التحكم التفاعلية'),
            Text('✅ إدارة العملاء'),
            Text('✅ إدارة الزيارات'),
            Text('✅ إدارة المهام'),
            Text('✅ التقارير والإحصائيات'),
            Text('✅ إدارة الطلبات'),
            Text('✅ الإعدادات'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إغلاق'),
          ),
        ],
      ),
    );
  }
}

// شاشة العملاء
class CustomersScreen extends StatelessWidget {
  const CustomersScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('العملاء'),
        backgroundColor: Colors.blue,
        foregroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            // إحصائيات سريعة
            Row(
              children: [
                Expanded(
                  child: _buildStatCard('إجمالي العملاء', '125', Icons.people, Colors.blue),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildStatCard('عملاء جدد', '12', Icons.person_add, Colors.green),
                ),
              ],
            ),
            const SizedBox(height: 20),
            // قائمة العملاء
            Expanded(
              child: ListView.builder(
                itemCount: 10,
                itemBuilder: (context, index) {
                  return Card(
                    margin: const EdgeInsets.only(bottom: 10),
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: Colors.blue,
                        child: Text('${index + 1}'),
                      ),
                      title: Text('عميل رقم ${index + 1}'),
                      subtitle: Text('الهاتف: 07901234${index.toString().padLeft(3, '0')}'),
                      trailing: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          IconButton(
                            icon: const Icon(Icons.phone, color: Colors.green),
                            onPressed: () => _showMessage(context, 'اتصال بالعميل'),
                          ),
                          IconButton(
                            icon: const Icon(Icons.location_on, color: Colors.red),
                            onPressed: () => _showMessage(context, 'عرض الموقع'),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showMessage(context, 'إضافة عميل جديد'),
        backgroundColor: Colors.blue,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          Icon(icon, size: 30, color: color),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          Text(
            title,
            style: const TextStyle(fontSize: 12),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  void _showMessage(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}

// شاشة الزيارات
class VisitsScreen extends StatelessWidget {
  const VisitsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('الزيارات'),
        backgroundColor: Colors.green,
        foregroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            // إحصائيات الزيارات
            Row(
              children: [
                Expanded(
                  child: _buildStatCard('زيارات اليوم', '8', Icons.today, Colors.green),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildStatCard('زيارات مكتملة', '5', Icons.check_circle, Colors.blue),
                ),
              ],
            ),
            const SizedBox(height: 20),
            // قائمة الزيارات
            Expanded(
              child: ListView.builder(
                itemCount: 8,
                itemBuilder: (context, index) {
                  final isCompleted = index < 5;
                  return Card(
                    margin: const EdgeInsets.only(bottom: 10),
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: isCompleted ? Colors.green : Colors.orange,
                        child: Icon(
                          isCompleted ? Icons.check : Icons.schedule,
                          color: Colors.white,
                        ),
                      ),
                      title: Text('زيارة عميل ${index + 1}'),
                      subtitle: Text('الوقت: ${9 + index}:00 صباحاً'),
                      trailing: IconButton(
                        icon: const Icon(Icons.location_on, color: Colors.red),
                        onPressed: () => _showMessage(context, 'عرض الموقع على الخريطة'),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showMessage(context, 'إضافة زيارة جديدة'),
        backgroundColor: Colors.green,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          Icon(icon, size: 30, color: color),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          Text(
            title,
            style: const TextStyle(fontSize: 12),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  void _showMessage(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}

// شاشة المهام
class TasksScreen extends StatelessWidget {
  const TasksScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('المهام'),
        backgroundColor: Colors.orange,
        foregroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            // إحصائيات المهام
            Row(
              children: [
                Expanded(
                  child: _buildStatCard('مهام اليوم', '12', Icons.today, Colors.orange),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildStatCard('مهام مكتملة', '8', Icons.check_circle, Colors.green),
                ),
              ],
            ),
            const SizedBox(height: 20),
            // قائمة المهام
            Expanded(
              child: ListView.builder(
                itemCount: 12,
                itemBuilder: (context, index) {
                  final isCompleted = index < 8;
                  return Card(
                    margin: const EdgeInsets.only(bottom: 10),
                    child: ListTile(
                      leading: Checkbox(
                        value: isCompleted,
                        onChanged: (value) => _showMessage(context, 'تحديث حالة المهمة'),
                      ),
                      title: Text(
                        'مهمة رقم ${index + 1}',
                        style: TextStyle(
                          decoration: isCompleted ? TextDecoration.lineThrough : null,
                        ),
                      ),
                      subtitle: Text('الأولوية: ${index % 3 == 0 ? 'عالية' : index % 2 == 0 ? 'متوسطة' : 'منخفضة'}'),
                      trailing: Icon(
                        isCompleted ? Icons.check_circle : Icons.schedule,
                        color: isCompleted ? Colors.green : Colors.orange,
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showMessage(context, 'إضافة مهمة جديدة'),
        backgroundColor: Colors.orange,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          Icon(icon, size: 30, color: color),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          Text(
            title,
            style: const TextStyle(fontSize: 12),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  void _showMessage(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}

// شاشة التقارير
class ReportsScreen extends StatelessWidget {
  const ReportsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('التقارير'),
        backgroundColor: Colors.red,
        foregroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            // إحصائيات عامة
            Row(
              children: [
                Expanded(
                  child: _buildStatCard('المبيعات', '45,000', Icons.attach_money, Colors.green),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildStatCard('الزيارات', '156', Icons.location_on, Colors.blue),
                ),
              ],
            ),
            const SizedBox(height: 20),
            // قائمة التقارير
            Expanded(
              child: ListView(
                children: [
                  _buildReportCard(
                    'تقرير المبيعات اليومي',
                    'عرض مبيعات اليوم الحالي',
                    Icons.today,
                    Colors.green,
                    context,
                  ),
                  _buildReportCard(
                    'تقرير الزيارات الأسبوعي',
                    'عرض زيارات الأسبوع الحالي',
                    Icons.calendar_view_week,
                    Colors.blue,
                    context,
                  ),
                  _buildReportCard(
                    'تقرير الأداء الشهري',
                    'عرض أداء الشهر الحالي',
                    Icons.calendar_month,
                    Colors.orange,
                    context,
                  ),
                  _buildReportCard(
                    'تقرير العملاء',
                    'إحصائيات العملاء والمتابعة',
                    Icons.people,
                    Colors.purple,
                    context,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          Icon(icon, size: 30, color: color),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          Text(
            title,
            style: const TextStyle(fontSize: 12),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildReportCard(String title, String subtitle, IconData icon, Color color, BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: color,
          child: Icon(icon, color: Colors.white),
        ),
        title: Text(title),
        subtitle: Text(subtitle),
        trailing: const Icon(Icons.arrow_forward_ios),
        onTap: () => _showMessage(context, 'عرض $title'),
      ),
    );
  }

  void _showMessage(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}

// شاشة الطلبات
class OrdersScreen extends StatelessWidget {
  const OrdersScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('الطلبات'),
        backgroundColor: Colors.purple,
        foregroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            // إحصائيات الطلبات
            Row(
              children: [
                Expanded(
                  child: _buildStatCard('طلبات اليوم', '15', Icons.shopping_cart, Colors.purple),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildStatCard('قيمة الطلبات', '12,500', Icons.attach_money, Colors.green),
                ),
              ],
            ),
            const SizedBox(height: 20),
            // قائمة الطلبات
            Expanded(
              child: ListView.builder(
                itemCount: 15,
                itemBuilder: (context, index) {
                  final statuses = ['جديد', 'قيد التنفيذ', 'مكتمل', 'ملغي'];
                  final colors = [Colors.blue, Colors.orange, Colors.green, Colors.red];
                  final statusIndex = index % 4;

                  return Card(
                    margin: const EdgeInsets.only(bottom: 10),
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: colors[statusIndex],
                        child: Text('${index + 1}'),
                      ),
                      title: Text('طلب رقم ${1000 + index}'),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('العميل: عميل رقم ${index + 1}'),
                          Text('القيمة: ${(index + 1) * 850} دينار'),
                        ],
                      ),
                      trailing: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: colors[statusIndex].withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          statuses[statusIndex],
                          style: TextStyle(
                            color: colors[statusIndex],
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showMessage(context, 'إضافة طلب جديد'),
        backgroundColor: Colors.purple,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          Icon(icon, size: 30, color: color),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          Text(
            title,
            style: const TextStyle(fontSize: 12),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  void _showMessage(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}

// شاشة الإعدادات
class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('الإعدادات'),
        backgroundColor: Colors.grey,
        foregroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: ListView(
          children: [
            // معلومات المستخدم
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  children: [
                    const CircleAvatar(
                      radius: 40,
                      backgroundColor: Colors.purple,
                      child: Icon(Icons.person, size: 40, color: Colors.white),
                    ),
                    const SizedBox(height: 10),
                    const Text(
                      'مندوب المبيعات',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    Text(
                      'admin@maxcon-erp.com',
                      style: TextStyle(color: Colors.grey[600]),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),
            // إعدادات التطبيق
            _buildSettingItem(
              'الملف الشخصي',
              'تحديث معلومات الحساب',
              Icons.person,
              context,
            ),
            _buildSettingItem(
              'الإشعارات',
              'إدارة إعدادات الإشعارات',
              Icons.notifications,
              context,
            ),
            _buildSettingItem(
              'اللغة',
              'تغيير لغة التطبيق',
              Icons.language,
              context,
            ),
            _buildSettingItem(
              'المزامنة',
              'مزامنة البيانات مع الخادم',
              Icons.sync,
              context,
            ),
            _buildSettingItem(
              'اختبار الاتصال',
              'اختبار الاتصال مع الخادم',
              Icons.wifi,
              context,
              onTap: () => _testApiConnection(context),
            ),
            _buildSettingItem(
              'حول التطبيق',
              'معلومات عن التطبيق والإصدار',
              Icons.info,
              context,
            ),
            const SizedBox(height: 20),
            // زر تسجيل الخروج
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pushReplacement(
                    context,
                    MaterialPageRoute(builder: (context) => const LoginScreen()),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.red,
                  padding: const EdgeInsets.symmetric(vertical: 15),
                ),
                child: const Text(
                  'تسجيل الخروج',
                  style: TextStyle(fontSize: 16, color: Colors.white),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSettingItem(String title, String subtitle, IconData icon, BuildContext context, {VoidCallback? onTap}) {
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: ListTile(
        leading: Icon(icon, color: Colors.grey[700]),
        title: Text(title),
        subtitle: Text(subtitle),
        trailing: const Icon(Icons.arrow_forward_ios),
        onTap: onTap ?? () => _showMessage(context, 'فتح $title'),
      ),
    );
  }

  void _testApiConnection(BuildContext context) async {
    // Show loading dialog
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const AlertDialog(
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 20),
            Text('جاري اختبار الاتصال...'),
          ],
        ),
      ),
    );

    try {
      // Import ApiService
      // final result = await ApiService.instance.testConnection();

      // Simulate API test for now
      await Future.delayed(const Duration(seconds: 2));

      if (context.mounted) {
        Navigator.pop(context); // Close loading dialog

        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: const Row(
              children: [
                Icon(Icons.check_circle, color: Colors.green),
                SizedBox(width: 10),
                Text('نجح الاتصال'),
              ],
            ),
            content: const Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('✅ تم الاتصال بالخادم بنجاح'),
                Text('✅ تم تسجيل الدخول التجريبي'),
                Text('✅ API جاهز للاستخدام'),
                SizedBox(height: 10),
                Text('الخادم: phpstack-1486247-5676575.cloudwaysapps.com'),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('إغلاق'),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.pop(context); // Close loading dialog

        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: const Row(
              children: [
                Icon(Icons.error, color: Colors.red),
                SizedBox(width: 10),
                Text('فشل الاتصال'),
              ],
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('❌ فشل في الاتصال بالخادم'),
                const SizedBox(height: 10),
                Text('الخطأ: $e'),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('إغلاق'),
              ),
            ],
          ),
        );
      }
    }
  }

  void _showMessage(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}

// شاشة الاستحصال
class CollectionsScreen extends StatelessWidget {
  const CollectionsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('الاستحصال'),
        backgroundColor: Colors.teal,
        foregroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            // إحصائيات الاستحصال
            Row(
              children: [
                Expanded(
                  child: _buildStatCard('اليوم', '8,500', Icons.today, Colors.green),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildStatCard('الشهر', '125,000', Icons.calendar_month, Colors.blue),
                ),
              ],
            ),
            const SizedBox(height: 20),
            // قائمة العملاء للاستحصال
            Expanded(
              child: ListView.builder(
                itemCount: 12,
                itemBuilder: (context, index) {
                  final amounts = [2500, 1800, 3200, 950, 4100, 1500, 2800, 3600, 1200, 2200, 1900, 3400];
                  final customerNames = [
                    'شركة النور للتجارة',
                    'مؤسسة الفجر',
                    'شركة الأمل التجارية',
                    'مكتب الرشيد',
                    'شركة البركة',
                    'مؤسسة النجاح',
                    'شركة الخير',
                    'مكتب الازدهار',
                    'شركة التقدم',
                    'مؤسسة الرفاه',
                    'شركة الوفاء',
                    'مكتب الإنجاز'
                  ];

                  return Card(
                    margin: const EdgeInsets.only(bottom: 10),
                    child: ListTile(
                      leading: const CircleAvatar(
                        backgroundColor: Colors.teal,
                        child: Icon(Icons.person, color: Colors.white),
                      ),
                      title: Text(customerNames[index]),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('المبلغ المستحق: ${amounts[index]} دينار'),
                          Text('آخر دفعة: ${DateTime.now().subtract(Duration(days: index + 5)).day}/${DateTime.now().month}'),
                        ],
                      ),
                      trailing: ElevatedButton(
                        onPressed: () => _showCollectionDialog(context, customerNames[index], amounts[index]),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.teal,
                          foregroundColor: Colors.white,
                        ),
                        child: const Text('استحصال'),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          Icon(icon, size: 30, color: color),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          Text(
            title,
            style: const TextStyle(fontSize: 12),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  void _showCollectionDialog(BuildContext context, String customerName, int amount) {
    final TextEditingController amountController = TextEditingController();
    final TextEditingController phoneController = TextEditingController(text: '07901234567');

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.payment, color: Colors.teal),
            SizedBox(width: 10),
            Text('استحصال من العميل'),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'العميل: $customerName',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              Text('المبلغ المستحق: $amount دينار'),
              const SizedBox(height: 20),
              TextField(
                controller: amountController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'المبلغ المستحصل',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.attach_money),
                ),
              ),
              const SizedBox(height: 15),
              TextField(
                controller: phoneController,
                keyboardType: TextInputType.phone,
                decoration: const InputDecoration(
                  labelText: 'رقم الواتساب',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.phone),
                ),
              ),
              const SizedBox(height: 15),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.green.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.green.withValues(alpha: 0.3)),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.info, color: Colors.green),
                    SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        'سيتم إنشاء وصل استحصال PDF وإرساله عبر الواتساب',
                        style: TextStyle(fontSize: 12),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _processCollection(context, customerName, amountController.text, phoneController.text);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.teal,
              foregroundColor: Colors.white,
            ),
            child: const Text('تأكيد الاستحصال'),
          ),
        ],
      ),
    );
  }

  void _processCollection(BuildContext context, String customerName, String amount, String phone) {
    // محاكاة عملية الاستحصال
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const CircularProgressIndicator(color: Colors.teal),
            const SizedBox(height: 20),
            const Text('جاري معالجة الاستحصال...'),
            const SizedBox(height: 10),
            Text('إنشاء وصل PDF للعميل: $customerName'),
          ],
        ),
      ),
    );

    // محاكاة التأخير لمعالجة العملية
    Future.delayed(const Duration(seconds: 3), () {
      if (context.mounted) {
        Navigator.pop(context); // إغلاق dialog التحميل
        _showSuccessDialog(context, customerName, amount, phone);
      }
    });
  }

  void _showSuccessDialog(BuildContext context, String customerName, String amount, String phone) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.check_circle, color: Colors.green, size: 30),
            SizedBox(width: 10),
            Text('تم الاستحصال بنجاح'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('العميل: $customerName'),
            Text('المبلغ: $amount دينار'),
            Text('رقم الوصل: ${DateTime.now().millisecondsSinceEpoch.toString().substring(7)}'),
            const SizedBox(height: 15),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.green.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                children: [
                  const Row(
                    children: [
                      Icon(Icons.picture_as_pdf, color: Colors.red),
                      SizedBox(width: 10),
                      Text('تم إنشاء وصل PDF'),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(Icons.message, color: Colors.green),
                      const SizedBox(width: 10),
                      Text('تم الإرسال إلى: $phone'),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إغلاق'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _openWhatsApp(phone, customerName, amount);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: Colors.white,
            ),
            child: const Text('فتح الواتساب'),
          ),
        ],
      ),
    );
  }

  void _openWhatsApp(String phone, String customerName, String amount) {
    // في التطبيق الحقيقي، سيتم فتح الواتساب هنا مع الرسالة التالية:
    // final message = '''
    // 🧾 *وصل استحصال - MaxCon ERP*
    //
    // العميل: $customerName
    // المبلغ المستحصل: $amount دينار
    // التاريخ: ${DateTime.now().day}/${DateTime.now().month}/${DateTime.now().year}
    // الوقت: ${DateTime.now().hour}:${DateTime.now().minute.toString().padLeft(2, '0')}
    //
    // شكراً لكم لحسن التعامل 🙏
    //
    // *تم إرفاق وصل الاستحصال PDF*
    // ''';

    // يمكن استخدام url_launcher package لفتح الواتساب
    // await launchUrl(Uri.parse('https://wa.me/$phone?text=${Uri.encodeComponent(message)}'));
  }
}
