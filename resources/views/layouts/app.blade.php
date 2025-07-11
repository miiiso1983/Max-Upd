<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'MaxCon ERP')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Custom Hover Effects -->
    <link rel="stylesheet" href="{{ asset('css/hover-effects.css') }}">

    <!-- Responsive Design -->
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        /* Select2 Custom Styling */
        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            background-color: white;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px;
            padding-left: 0;
            padding-right: 0;
            color: #374151;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 10px;
        }

        .select2-dropdown {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #3b82f6;
            color: white;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #eff6ff;
            color: #1d4ed8;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem;
            direction: rtl;
        }

        /* Focus states */
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* RTL Support */
        .select2-container[dir="rtl"] .select2-selection--single .select2-selection__rendered {
            padding-right: 0;
            padding-left: 20px;
        }

        .select2-container[dir="rtl"] .select2-selection--single .select2-selection__arrow {
            left: 10px;
            right: auto;
        }
        
        .sidebar-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #1d4ed8 100%);
            box-shadow: 4px 0 20px rgba(30, 58, 138, 0.3);
        }

        /* Advanced Sidebar Styling */
        .sidebar-gradient nav a {
            position: relative;
            border-radius: 12px;
            margin: 2px 8px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .sidebar-gradient nav a:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 197, 253, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-gradient nav a:hover:before {
            opacity: 1;
        }

        .sidebar-gradient nav a:hover {
            background: rgba(59, 130, 246, 0.15) !important;
            transform: translateX(-8px) scale(1.02);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.2);
            border-left: 4px solid #3b82f6;
        }

        .sidebar-gradient nav a:hover i {
            color: #60a5fa !important;
            transform: scale(1.1);
            transition: all 0.3s ease;
        }

        .sidebar-gradient nav a:hover span {
            color: #dbeafe !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        /* Active state styling */
        .sidebar-gradient nav a.active {
            background: rgba(59, 130, 246, 0.2) !important;
            border-left: 4px solid #3b82f6;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .sidebar-gradient nav a.active i {
            color: #60a5fa !important;
        }

        .sidebar-gradient nav a.active span {
            color: #dbeafe !important;
            font-weight: 600;
        }

        .sidebar-gradient .w-10.h-10.bg-white:hover i {
            color: white !important;
            transition: color 0.3s ease;
        }

        /* Section headers */
        .sidebar-section-header {
            background: rgba(30, 58, 138, 0.3);
            border-radius: 8px;
            margin: 8px;
            padding: 8px 16px;
            border-left: 3px solid #3b82f6;
        }

        .sidebar-section-header h3 {
            color: #93c5fd;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        /* Logo area styling */
        .sidebar-logo {
            background: rgba(30, 58, 138, 0.2);
            border-radius: 16px;
            padding: 20px;
            margin: 16px;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .sidebar-logo:hover {
            background: rgba(30, 58, 138, 0.3);
            transform: scale(1.02);
            transition: all 0.3s ease;
        }

        .sidebar-logo .logo-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        /* Submenu styling */
        .sidebar-submenu {
            margin-left: 20px;
            border-left: 2px solid rgba(59, 130, 246, 0.2);
            padding-left: 8px;
        }

        .sidebar-submenu a {
            font-size: 0.875rem;
            padding: 8px 12px;
        }

        /* Scrollbar styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(30, 58, 138, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.5);
        }

        /* Top Navigation Hover Effects */
        header h2:hover {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
            cursor: pointer;
        }

        header button:hover {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        header button:hover i {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        /* User menu hover */
        header .w-8.h-8.bg-purple-600:hover {
            background: #6f42c1 !important;
            transition: background 0.3s ease;
            transform: scale(1.1);
        }

        header .text-gray-700:hover {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        /* Notification badge hover */
        header .bg-red-400:hover {
            background: #6f42c1 !important;
            transition: background 0.3s ease;
        }

        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Card hover effects */
        .card-shadow:hover {
            box-shadow: 0 10px 25px rgba(111, 66, 193, 0.15) !important;
            transition: box-shadow 0.3s ease;
            transform: translateY(-2px);
        }
        
        .hover-scale {
            transition: transform 0.2s ease-in-out;
        }
        
        .hover-scale:hover {
            transform: scale(1.02);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Mobile Header -->
    <div class="mobile-header lg:hidden">
        <button class="mobile-menu-btn" id="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="mobile-logo">MaxCon</div>
        <div class="flex items-center space-x-2 space-x-reverse">
            <button class="text-gray-600 hover:text-purple-600">
                <i class="fas fa-bell"></i>
            </button>
            <button class="text-gray-600 hover:text-purple-600">
                <i class="fas fa-user"></i>
            </button>
        </div>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay lg:hidden" id="sidebar-overlay"></div>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 sidebar-gradient text-white flex-shrink-0 overflow-y-auto lg:relative lg:translate-x-0" id="sidebar">
            <!-- Logo Section -->
            <div class="sidebar-logo">
                <div class="flex items-center space-x-3 space-x-reverse">
                    <div class="w-12 h-12 logo-icon rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">MaxCon ERP</h1>
                        <p class="text-blue-200 text-sm">نظام إدارة الموارد المتكامل</p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="mt-4 pb-8">
                <!-- Dashboard -->
                <div class="sidebar-section-header">
                    <h3>القائمة الرئيسية</h3>
                </div>

                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt ml-3 text-lg"></i>
                    <span class="font-medium">لوحة التحكم</span>
                </a>
                
                <!-- Sales Section -->
                <div class="sidebar-section-header">
                    <h3>إدارة المبيعات</h3>
                </div>

                <a href="{{ route('sales.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('sales.index') ? 'active' : '' }}">
                    <i class="fas fa-chart-line ml-3 text-lg"></i>
                    <span class="font-medium">لوحة المبيعات</span>
                </a>

                <a href="{{ route('sales.customers.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('sales.customers.*') ? 'active' : '' }}">
                    <i class="fas fa-users ml-3 text-lg"></i>
                    <span class="font-medium">إدارة العملاء</span>
                </a>

                <a href="{{ route('sales-reps.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('sales-reps.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie ml-3 text-lg"></i>
                    <span class="font-medium">مندوبي المبيعات</span>
                </a>

                <a href="{{ route('sales.orders.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('sales.orders.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart ml-3 text-lg"></i>
                    <span class="font-medium">طلبات المبيعات</span>
                </a>

                <a href="{{ route('sales.invoices.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('sales.invoices.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar ml-3 text-lg"></i>
                    <span class="font-medium">الفواتير</span>
                </a>

                <a href="{{ route('sales.payments.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('sales.payments.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card ml-3 text-lg"></i>
                    <span class="font-medium">المدفوعات</span>
                </a>
                
                <!-- Inventory Section -->
                <div class="sidebar-section-header">
                    <h3>إدارة المخزون</h3>
                </div>

                <a href="{{ route('inventory.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('inventory.index') ? 'active' : '' }}">
                    <i class="fas fa-warehouse ml-3 text-lg"></i>
                    <span class="font-medium">لوحة المخزون</span>
                </a>

                <a href="{{ route('inventory.products.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('inventory.products.*') ? 'active' : '' }}">
                    <i class="fas fa-boxes ml-3 text-lg"></i>
                    <span class="font-medium">إدارة المنتجات</span>
                </a>

                <a href="{{ route('inventory.warehouses.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('inventory.warehouses.*') ? 'active' : '' }}">
                    <i class="fas fa-building ml-3 text-lg"></i>
                    <span class="font-medium">المستودعات</span>
                </a>

                <!-- Suppliers Section -->
                <div class="sidebar-section-header">
                    <h3>الموردين والمشتريات</h3>
                </div>

                <a href="{{ route('suppliers.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <i class="fas fa-truck ml-3 text-lg"></i>
                    <span class="font-medium">إدارة الموردين</span>
                </a>

                <a href="{{ route('purchase-orders.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-bag ml-3 text-lg"></i>
                    <span class="font-medium">طلبات الشراء</span>
                </a>

                <!-- Accounting Section -->
                <div class="sidebar-section-header">
                    <h3>المحاسبة والمالية</h3>
                </div>

                <a href="{{ route('accounting.dashboard') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('accounting.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-calculator ml-3 text-lg"></i>
                    <span class="font-medium">لوحة المحاسبة</span>
                </a>

                <a href="{{ route('accounting.chart-of-accounts.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('accounting.chart-of-accounts.*') ? 'active' : '' }}">
                    <i class="fas fa-sitemap ml-3 text-lg"></i>
                    <span class="font-medium">دليل الحسابات</span>
                </a>

                <a href="{{ route('accounting.journal-entries.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('accounting.journal-entries.*') ? 'active' : '' }}">
                    <i class="fas fa-book ml-3 text-lg"></i>
                    <span class="font-medium">القيود المحاسبية</span>
                </a>

                <a href="{{ route('accounting.financial-reports.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('accounting.financial-reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie ml-3 text-lg"></i>
                    <span class="font-medium">التقارير المالية</span>
                </a>

                <a href="{{ route('accounting.reports.trial-balance') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('accounting.reports.trial-balance') ? 'active' : '' }}">
                    <i class="fas fa-balance-scale ml-3 text-lg"></i>
                    <span class="font-medium">ميزان المراجعة</span>
                </a>

                <!-- Regulatory Affairs Section -->
                <div class="sidebar-section-header">
                    <h3>الشؤون التنظيمية</h3>
                </div>

                <a href="{{ route('regulatory-affairs.dashboard') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('regulatory-affairs.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check ml-3 text-lg"></i>
                    <span class="font-medium">لوحة الشؤون التنظيمية</span>
                </a>

                <a href="{{ route('regulatory-affairs.companies.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('regulatory-affairs.companies.*') ? 'active' : '' }}">
                    <i class="fas fa-building ml-3 text-lg"></i>
                    <span class="font-medium">تسجيل الشركات الدوائية</span>
                </a>

                <a href="{{ route('regulatory-affairs.products.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('regulatory-affairs.products.*') ? 'active' : '' }}">
                    <i class="fas fa-pills ml-3 text-lg"></i>
                    <span class="font-medium">تصنيف المنتجات الدوائية</span>
                </a>

                <a href="{{ route('regulatory-affairs.tests.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('regulatory-affairs.tests.*') ? 'active' : '' }}">
                    <i class="fas fa-flask ml-3 text-lg"></i>
                    <span class="font-medium">الفحوصات الدوائية</span>
                </a>

                <a href="{{ route('regulatory-affairs.inspections.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('regulatory-affairs.inspections.*') ? 'active' : '' }}">
                    <i class="fas fa-search ml-3 text-lg"></i>
                    <span class="font-medium">التفتيش والمراقبة</span>
                </a>

                <!-- Analytics Section -->
                <div class="sidebar-section-header">
                    <h3>التحليلات الذكية</h3>
                </div>

                <a href="{{ route('analytics.dashboard') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('analytics.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-brain ml-3 text-lg"></i>
                    <span class="font-medium">لوحة التحليلات الذكية</span>
                </a>

                <a href="{{ route('analytics.sales-prediction') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('analytics.sales-prediction') ? 'active' : '' }}">
                    <i class="fas fa-chart-line ml-3 text-lg"></i>
                    <span class="font-medium">توقعات المبيعات</span>
                </a>

                <a href="{{ route('analytics.business-intelligence') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('analytics.business-intelligence') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar ml-3 text-lg"></i>
                    <span class="font-medium">ذكاء الأعمال</span>
                </a>

                <a href="{{ route('analytics.customer-analytics') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('analytics.customer-analytics') ? 'active' : '' }}">
                    <i class="fas fa-users-cog ml-3 text-lg"></i>
                    <span class="font-medium">تحليلات العملاء</span>
                </a>

                <!-- HR Section -->
                <div class="sidebar-section-header">
                    <h3>الموارد البشرية</h3>
                </div>

                <a href="{{ route('hr.employees.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('hr.employees.*') ? 'active' : '' }}">
                    <i class="fas fa-users ml-3 text-lg"></i>
                    <span class="font-medium">إدارة الموظفين</span>
                </a>

                <a href="{{ route('hr.payroll.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('hr.payroll.*') ? 'active' : '' }}">
                    <i class="fas fa-money-check-alt ml-3 text-lg"></i>
                    <span class="font-medium">الرواتب والأجور</span>
                </a>

                <a href="{{ route('hr.attendance.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('hr.attendance.*') ? 'active' : '' }}">
                    <i class="fas fa-clock ml-3 text-lg"></i>
                    <span class="font-medium">الحضور والانصراف</span>
                </a>

                <!-- Advanced User Management Section -->
                <div class="sidebar-section-header">
                    <h3>إدارة المستخدمين المتقدمة</h3>
                </div>

                <a href="{{ route('admin.advanced.users.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('admin.advanced.users.index') ? 'active' : '' }}">
                    <i class="fas fa-users-cog ml-3 text-lg"></i>
                    <span class="font-medium">إدارة المستخدمين</span>
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-user-friends ml-3 text-lg"></i>
                    <span class="font-medium">المستخدمين</span>
                </a>

                <a href="{{ route('admin.roles.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tag ml-3 text-lg"></i>
                    <span class="font-medium">الأدوار والصلاحيات</span>
                </a>

                <a href="{{ route('admin.advanced.security.advanced') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('admin.advanced.security.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt ml-3 text-lg"></i>
                    <span class="font-medium">الأمان المتقدم</span>
                </a>

                <a href="{{ route('admin.advanced.audit.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('admin.advanced.audit.*') ? 'active' : '' }}">
                    <i class="fas fa-search ml-3 text-lg"></i>
                    <span class="font-medium">سجل التدقيق</span>
                </a>

                <!-- Medical Representatives Section -->
                <div class="sidebar-section-header">
                    <h3>المندوبين الطبيين</h3>
                </div>

                <a href="{{ route('medical-reps.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('medical-reps.index') ? 'active' : '' }}">
                    <i class="fas fa-user-md ml-3 text-lg"></i>
                    <span class="font-medium">إدارة المندوبين</span>
                </a>

                <a href="{{ route('medical-reps.territories.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('medical-reps.territories.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt ml-3 text-lg"></i>
                    <span class="font-medium">المناطق الجغرافية</span>
                </a>

                <a href="{{ route('medical-reps.visits.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('medical-reps.visits.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check ml-3 text-lg"></i>
                    <span class="font-medium">الزيارات الميدانية</span>
                </a>

                <a href="{{ route('medical-reps.commissions.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('medical-reps.commissions.*') ? 'active' : '' }}">
                    <i class="fas fa-percentage ml-3 text-lg"></i>
                    <span class="font-medium">العمولات والحوافز</span>
                </a>

                <!-- Reports Section -->
                <div class="sidebar-section-header">
                    <h3>التقارير والإحصائيات</h3>
                </div>

                <a href="{{ route('reports.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar ml-3 text-lg"></i>
                    <span class="font-medium">جميع التقارير</span>
                </a>

                <a href="{{ route('reports.sales') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('reports.sales*') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie ml-3 text-lg"></i>
                    <span class="font-medium">تقارير المبيعات</span>
                </a>

                <a href="{{ route('reports.inventory') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('reports.inventory*') ? 'active' : '' }}">
                    <i class="fas fa-boxes ml-3 text-lg"></i>
                    <span class="font-medium">تقارير المخزون</span>
                </a>

                <a href="{{ route('reports.financial.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('reports.financial.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar ml-3 text-lg"></i>
                    <span class="font-medium">التقارير المالية</span>
                </a>

                <!-- System Administration Section -->
                @if(auth()->check() && (auth()->user()->hasRole('super_admin') || method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin()))
                <div class="sidebar-section-header">
                    <h3>إدارة النظام العامة</h3>
                </div>

                <a href="{{ route('master-admin.dashboard') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('master-admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-crown ml-3 text-lg"></i>
                    <span class="font-medium">لوحة المستأجرين</span>
                </a>

                <a href="{{ route('master-admin.tenants.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('master-admin.tenants.*') ? 'active' : '' }}">
                    <i class="fas fa-building ml-3 text-lg"></i>
                    <span class="font-medium">إدارة المستأجرين</span>
                </a>
                @endif

                <!-- Settings & Configuration Section -->
                <div class="sidebar-section-header">
                    <h3>الإعدادات والتكوين</h3>
                </div>

                <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cogs ml-3 text-lg"></i>
                    <span class="font-medium">إعدادات النظام</span>
                </a>

                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="fas fa-user-edit ml-3 text-lg"></i>
                    <span class="font-medium">الملف الشخصي</span>
                </a>

                <a href="{{ route('notifications.index') }}" class="flex items-center px-4 py-3 text-white {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <i class="fas fa-bell ml-3 text-lg"></i>
                    <span class="font-medium">الإشعارات</span>
                </a>

                <!-- Logout Section -->
                <div class="mt-8 px-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-4 py-3 text-white hover:bg-red-600 hover:bg-opacity-20 rounded-lg transition-all duration-300">
                            <i class="fas fa-sign-out-alt ml-3 text-lg"></i>
                            <span class="font-medium">تسجيل الخروج</span>
                        </button>
                    </form>
                </div>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation - Hidden on Mobile -->
            <header class="bg-white shadow-sm border-b border-gray-200 hidden lg:block">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'لوحة التحكم')</h2>
                    </div>
                    
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600">
                                <i class="fas fa-bell text-lg"></i>
                                <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400"></span>
                            </button>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="relative">
                            <div class="flex items-center space-x-3 space-x-reverse">
                                <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <div class="text-sm">
                                    <p class="text-gray-700 font-medium">{{ auth()->user()->name ?? 'مدير النظام' }}</p>
                                    <p class="text-gray-500">{{ auth()->user()->email ?? 'admin@maxcon.com' }}</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Mobile Interactions JS -->
    <script src="{{ asset('js/mobile-interactions.js') }}"></script>

    <!-- Searchable Select Inline JS -->
    <script>
    // Global Select2 initialization
    function initializeSearchableSelects(container = document) {
        const selects = $(container).find('.searchable-select');
        console.log('Found ' + selects.length + ' searchable selects');
        selects.each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                const $select = $(this);
                const isMultiple = $select.prop('multiple');
                const placeholder = $select.data('placeholder') || 'اختر من القائمة...';
                const allowClear = $select.data('allow-clear') !== false && !isMultiple;

                $select.select2({
                    placeholder: placeholder,
                    allowClear: allowClear,
                    dir: 'rtl',
                    language: {
                        noResults: function() {
                            return "لا توجد نتائج";
                        },
                        searching: function() {
                            return "جاري البحث...";
                        },
                        loadingMore: function() {
                            return "جاري تحميل المزيد...";
                        }
                    },
                    width: '100%'
                });
            }
        });
    }

    // Initialize on document ready
    $(document).ready(function() {
        console.log('Initializing searchable selects...');
        if (typeof $.fn.select2 !== 'undefined') {
            console.log('Select2 is loaded, initializing...');
            initializeSearchableSelects();

            // Re-initialize when new content is added
            $(document).on('DOMNodeInserted', function(e) {
                if ($(e.target).find('.searchable-select').length > 0) {
                    setTimeout(function() {
                        initializeSearchableSelects(e.target);
                    }, 100);
                }
            });
        }
    });

    // Global utility object
    window.SearchableSelect = {
        reinitialize: function(container) {
            initializeSearchableSelects(container);
        },
        refresh: function(selector) {
            const $select = $(selector);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            initializeSearchableSelects($select.parent());
        }
    };
    </script>

    @stack('scripts')

    <script>
    function showComingSoon(feature) {
        alert('ميزة "' + feature + '" ستكون متاحة قريباً!\n\nنحن نعمل على تطويرها لتقديم أفضل تجربة لك.');
    }
    </script>

    <!-- Mobile Menu JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            if (mobileMenuToggle && sidebar && sidebarOverlay) {
                // Toggle mobile menu
                mobileMenuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    sidebarOverlay.classList.toggle('active');
                    document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
                });

                // Close menu when clicking overlay
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                });

                // Close menu when clicking a link (mobile)
                const sidebarLinks = sidebar.querySelectorAll('a');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth < 1024) {
                            sidebar.classList.remove('open');
                            sidebarOverlay.classList.remove('active');
                            document.body.style.overflow = '';
                        }
                    });
                });

                // Handle window resize
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 1024) {
                        sidebar.classList.remove('open');
                        sidebarOverlay.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }
        });
    </script>
</body>
</html>
