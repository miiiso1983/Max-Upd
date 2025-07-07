<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'MaxCon Master Admin')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Hover Effects -->
    <link rel="stylesheet" href="{{ asset('css/hover-effects.css') }}">
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
        
        .master-admin-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #581c87 100%);
        }

        /* Master Admin Sidebar Hover Effects */
        .master-admin-gradient nav a:hover {
            color: #6f42c1 !important;
            background: rgba(111, 66, 193, 0.1) !important;
            transition: all 0.3s ease;
            transform: translateX(-5px);
        }

        .master-admin-gradient nav a:hover i {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        .master-admin-gradient nav a:hover span {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        /* Section titles hover */
        .master-admin-gradient nav .text-blue-200:hover {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
            cursor: pointer;
        }

        /* Active state enhancement */
        .master-admin-gradient nav a.bg-white.bg-opacity-20:hover {
            background: rgba(111, 66, 193, 0.2) !important;
            border-right-color: #6f42c1 !important;
        }

        /* Logo area hover */
        .master-admin-gradient .flex.items-center.space-x-3:hover h3 {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        .master-admin-gradient .flex.items-center.space-x-3:hover p {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        /* Icon background hover */
        .master-admin-gradient .w-10.h-10.bg-white:hover {
            background: #6f42c1 !important;
            transition: background 0.3s ease;
        }

        .master-admin-gradient .w-10.h-10.bg-white:hover i {
            color: white !important;
            transition: color 0.3s ease;
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
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Master Admin Sidebar -->
        <div class="w-64 master-admin-gradient text-white flex-shrink-0 overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center space-x-3 space-x-reverse">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <i class="fas fa-crown text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">MaxCon Master</h2>
                        <p class="text-blue-200 text-sm">إدارة النظام الرئيسية</p>
                    </div>
                </div>
            </div>
            
            <nav class="mt-8">
                <div class="px-6 py-3">
                    <p class="text-blue-200 text-xs uppercase tracking-wider font-semibold">إدارة النظام</p>
                </div>
                
                <a href="{{ route('master-admin.dashboard') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('master-admin.dashboard') ? 'bg-white bg-opacity-20 border-r-4 border-white' : '' }}">
                    <i class="fas fa-tachometer-alt ml-3"></i>
                    <span>لوحة التحكم</span>
                </a>
                
                <!-- Tenants Management -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-blue-200 text-xs uppercase tracking-wider font-semibold">إدارة المستأجرين</p>
                    </div>
                    
                    <a href="{{ route('master-admin.tenants.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('master-admin.tenants.*') ? 'bg-white bg-opacity-20' : '' }}">
                        <i class="fas fa-building ml-3"></i>
                        <span>جميع المستأجرين</span>
                    </a>
                    
                    <a href="{{ route('master-admin.tenants.create') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-plus ml-3"></i>
                        <span>إضافة مستأجر جديد</span>
                    </a>
                    
                    <a href="{{ route('master-admin.tenants.pending') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-clock ml-3"></i>
                        <span>طلبات الانتظار</span>
                    </a>
                    
                    <a href="{{ route('master-admin.tenants.expired') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-exclamation-triangle ml-3"></i>
                        <span>التراخيص المنتهية</span>
                    </a>
                </div>
                
                <!-- System Management -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-blue-200 text-xs uppercase tracking-wider font-semibold">إدارة النظام</p>
                    </div>
                    
                    <a href="{{ route('master-admin.system.settings') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-cogs ml-3"></i>
                        <span>إعدادات النظام</span>
                    </a>
                    
                    <a href="{{ route('master-admin.system.monitoring') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-chart-line ml-3"></i>
                        <span>مراقبة النظام</span>
                    </a>
                    
                    <a href="{{ route('master-admin.system.backups') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-database ml-3"></i>
                        <span>النسخ الاحتياطية</span>
                    </a>
                    
                    <a href="{{ route('master-admin.system.logs') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-file-alt ml-3"></i>
                        <span>سجلات النظام</span>
                    </a>
                </div>
                
                <!-- Billing & Subscriptions -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-blue-200 text-xs uppercase tracking-wider font-semibold">الفوترة والاشتراكات</p>
                    </div>
                    
                    <a href="{{ route('master-admin.billing.plans') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-tags ml-3"></i>
                        <span>خطط الاشتراك</span>
                    </a>
                    
                    <a href="{{ route('master-admin.billing.invoices') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-file-invoice-dollar ml-3"></i>
                        <span>الفواتير</span>
                    </a>
                    
                    <a href="{{ route('master-admin.billing.payments') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-credit-card ml-3"></i>
                        <span>المدفوعات</span>
                    </a>
                </div>
                
                <!-- Reports & Analytics -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-blue-200 text-xs uppercase tracking-wider font-semibold">التقارير والتحليلات</p>
                    </div>
                    
                    <a href="{{ route('master-admin.reports.overview') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-chart-bar ml-3"></i>
                        <span>نظرة عامة</span>
                    </a>
                    
                    <a href="{{ route('master-admin.reports.revenue') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-dollar-sign ml-3"></i>
                        <span>تقارير الإيرادات</span>
                    </a>
                    
                    <a href="{{ route('master-admin.reports.usage') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors">
                        <i class="fas fa-chart-pie ml-3"></i>
                        <span>تقارير الاستخدام</span>
                    </a>
                </div>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Master Admin Dashboard')</h2>
                    </div>
                    
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="relative p-2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-bell text-lg"></i>
                                <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400"></span>
                            </button>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="relative">
                            <div class="flex items-center space-x-3 space-x-reverse">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                    <i class="fas fa-crown text-white text-sm"></i>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">Master Admin</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                </div>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                @yield('content')
            </main>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>
