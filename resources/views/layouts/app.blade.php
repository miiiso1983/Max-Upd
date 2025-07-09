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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Sidebar Hover Effects */
        .sidebar-gradient nav a:hover {
            color: #6f42c1 !important;
            background: rgba(111, 66, 193, 0.1) !important;
            transition: all 0.3s ease;
            transform: translateX(-5px);
        }

        .sidebar-gradient nav a:hover i {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        .sidebar-gradient nav a:hover span {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        /* Section titles hover */
        .sidebar-gradient nav .text-purple-200:hover {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
            cursor: pointer;
        }

        /* Active state enhancement */
        .sidebar-gradient nav a.bg-white.bg-opacity-20:hover {
            background: rgba(111, 66, 193, 0.2) !important;
            border-right-color: #6f42c1 !important;
        }

        /* Logo area hover */
        .sidebar-gradient .flex.items-center.space-x-3:hover h3 {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        .sidebar-gradient .flex.items-center.space-x-3:hover p {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        /* Icon background hover */
        .sidebar-gradient .w-10.h-10.bg-white:hover {
            background: #6f42c1 !important;
            transition: background 0.3s ease;
        }

        .sidebar-gradient .w-10.h-10.bg-white:hover i {
            color: white !important;
            transition: color 0.3s ease;
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
            <div class="p-6">
                <div class="flex items-center space-x-3 space-x-reverse">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600 text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">MaxCon ERP</h1>
                        <p class="text-purple-200 text-sm">نظام إدارة الموارد المتكامل</p>
                    </div>
                </div>
            </div>
            
            <nav class="mt-8">
                <div class="px-6 py-3">
                    <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">القائمة الرئيسية</p>
                </div>
                
                <a href="{{ route('dashboard') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('dashboard') ? 'bg-white bg-opacity-20 border-r-4 border-white' : '' }}">
                    <i class="fas fa-tachometer-alt ml-3"></i>
                    <span>لوحة التحكم</span>
                </a>
                
                <!-- Sales Menu -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">المبيعات</p>
                    </div>
                    
                    <a href="{{ route('sales.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('sales.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-chart-line ml-3"></i>
                        <span>لوحة المبيعات</span>
                    </a>
                    
                    <a href="{{ route('sales.customers.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('sales.customers.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-users ml-3"></i>
                        <span>العملاء</span>
                    </a>

                    <a href="{{ route('sales-reps.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('sales-reps.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-user-tie ml-3"></i>
                        <span>مندوبي المبيعات</span>
                    </a>

                    <a href="{{ route('sales.orders.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('sales.orders.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-shopping-cart ml-3"></i>
                        <span>طلبات المبيعات</span>
                    </a>
                    
                    <a href="{{ route('sales.invoices.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('sales.invoices.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-file-invoice ml-3"></i>
                        <span>الفواتير</span>
                    </a>
                    
                    <a href="{{ route('sales.payments.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('sales.payments.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-credit-card ml-3"></i>
                        <span>المدفوعات</span>
                    </a>
                </div>
                
                <!-- Inventory Menu -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">المخزون</p>
                    </div>
                    
                    <a href="{{ route('inventory.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('inventory.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-boxes ml-3"></i>
                        <span>لوحة المخزون</span>
                    </a>
                    
                    <a href="{{ route('inventory.products.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('inventory.products.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-box ml-3"></i>
                        <span>المنتجات</span>
                    </a>
                </div>

                <!-- Suppliers Menu -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">الموردين</p>
                    </div>

                    <a href="{{ route('suppliers.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('suppliers.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-truck ml-3"></i>
                        <span>الموردين</span>
                    </a>

                    <a href="{{ route('purchase-orders.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('purchase-orders.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-shopping-cart ml-3"></i>
                        <span>طلبات الشراء</span>
                    </a>
                </div>

                <!-- Accounting Menu -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">المحاسبة</p>
                    </div>

                    <a href="{{ route('accounting.dashboard') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('accounting.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-calculator ml-3"></i>
                        <span>لوحة المحاسبة</span>
                    </a>

                    <a href="{{ route('accounting.chart-of-accounts.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('accounting.chart-of-accounts.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-sitemap ml-3"></i>
                        <span>دليل الحسابات</span>
                    </a>

                    <a href="{{ route('accounting.journal-entries.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('accounting.journal-entries.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-book ml-3"></i>
                        <span>القيود اليومية</span>
                    </a>

                    <a href="{{ route('accounting.financial-reports.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('accounting.financial-reports.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-file-invoice-dollar ml-3"></i>
                        <span>التقارير المالية</span>
                    </a>
                </div>

                <!-- HR Menu -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">الموارد البشرية</p>
                    </div>

                    <a href="{{ route('hr.employees.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('hr.employees.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-users ml-3"></i>
                        <span>الموظفين</span>
                    </a>

                    <a href="{{ route('hr.payroll.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('hr.payroll.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-money-check-alt ml-3"></i>
                        <span>الرواتب</span>
                    </a>

                    <a href="{{ route('hr.attendance.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('hr.attendance.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-clock ml-3"></i>
                        <span>الحضور والانصراف</span>
                    </a>
                </div>

                <!-- Medical Reps Menu -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">المندوبين الطبيين</p>
                    </div>

                    <a href="{{ route('medical-reps.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('medical-reps.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-user-md ml-3"></i>
                        <span>المندوبين</span>
                    </a>

                    <a href="{{ route('medical-reps.territories.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('medical-reps.territories.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-map-marked-alt ml-3"></i>
                        <span>المناطق</span>
                    </a>

                    <a href="{{ route('medical-reps.visits.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('medical-reps.visits.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-calendar-check ml-3"></i>
                        <span>الزيارات</span>
                    </a>

                    <a href="{{ route('medical-reps.commissions.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('medical-reps.commissions.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-percentage ml-3"></i>
                        <span>العمولات</span>
                    </a>
                </div>

                <!-- Regulatory Affairs Menu -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">الشؤون التنظيمية</p>
                    </div>

                    <a href="{{ route('regulatory-affairs.dashboard') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('regulatory-affairs.dashboard') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-shield-alt ml-3"></i>
                        <span>لوحة التحكم</span>
                    </a>

                    <a href="{{ route('regulatory-affairs.companies.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('regulatory-affairs.companies.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-building ml-3"></i>
                        <span>الشركات الدوائية</span>
                    </a>

                    <a href="{{ route('regulatory-affairs.products.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('regulatory-affairs.products.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-pills ml-3"></i>
                        <span>الأصناف الدوائية</span>
                    </a>

                    <a href="{{ route('regulatory-affairs.batches.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('regulatory-affairs.batches.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-vials ml-3"></i>
                        <span>الدفعات</span>
                    </a>

                    <a href="{{ route('regulatory-affairs.tests.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('regulatory-affairs.tests.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-flask ml-3"></i>
                        <span>الفحوصات</span>
                    </a>

                    <a href="{{ route('regulatory-affairs.inspections.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('regulatory-affairs.inspections.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-search ml-3"></i>
                        <span>التفتيشات</span>
                    </a>
                </div>

                <!-- Analytics Menu -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">التحليلات والذكاء الاصطناعي</p>
                    </div>

                    <a href="{{ route('analytics.dashboard') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('analytics.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-brain ml-3"></i>
                        <span>لوحة التحليلات</span>
                    </a>

                    <a href="{{ route('analytics.sales-prediction') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('analytics.sales-prediction') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-chart-line ml-3"></i>
                        <span>توقعات المبيعات</span>
                    </a>

                    <a href="{{ route('analytics.business-intelligence') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('analytics.business-intelligence') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-lightbulb ml-3"></i>
                        <span>ذكاء الأعمال</span>
                    </a>
                </div>

                <!-- Reports Menu -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">التقارير</p>
                    </div>

                    <a href="{{ route('reports.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('reports.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-chart-bar ml-3"></i>
                        <span>جميع التقارير</span>
                    </a>

                    <a href="{{ route('reports.sales') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('reports.sales') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-chart-pie ml-3"></i>
                        <span>تقارير المبيعات</span>
                    </a>

                    <a href="{{ route('reports.inventory') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('reports.inventory') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-boxes ml-3"></i>
                        <span>تقارير المخزون</span>
                    </a>

                    <a href="{{ route('reports.financial.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('reports.financial.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-file-invoice-dollar ml-3"></i>
                        <span>التقارير المالية</span>
                    </a>
                </div>

                @if(auth()->user()->hasRole('super_admin') || auth()->user()->isSuperAdmin())
                <!-- Super Admin Menu -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">إدارة النظام</p>
                    </div>

                    <a href="{{ route('super-admin.dashboard') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('super-admin.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-crown ml-3"></i>
                        <span>لوحة المستأجرين</span>
                    </a>

                    <a href="{{ route('super-admin.tenants.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('super-admin.tenants.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-building ml-3"></i>
                        <span>إدارة المستأجرين</span>
                    </a>
                </div>
                @endif

                @if(auth()->user()->hasRole(['admin', 'tenant-admin']))
                <!-- Admin Menu -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">الإدارة</p>
                    </div>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-users-cog ml-3"></i>
                        <span>إدارة المستخدمين</span>
                    </a>

                    <a href="{{ route('admin.roles.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('admin.roles.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-user-tag ml-3"></i>
                        <span>إدارة الأدوار</span>
                    </a>

                    <a href="{{ route('admin.permissions.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('admin.permissions.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-key ml-3"></i>
                        <span>إدارة الصلاحيات</span>
                    </a>

                    <a href="{{ route('admin.settings.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('admin.settings.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-cogs ml-3"></i>
                        <span>إعدادات النظام</span>
                    </a>
                </div>
                @endif
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
