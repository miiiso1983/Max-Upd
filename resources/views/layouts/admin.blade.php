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

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
        
        /* Select2 RTL Support */
        .select2-container {
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

        header .text-gray-600:hover {
            color: #6f42c1 !important;
            transition: color 0.3s ease;
        }

        /* Button Hover Effects */
        .btn-hover {
            transition: all 0.3s ease;
        }
        
        .btn-hover:hover {
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
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 sidebar-gradient text-white flex-shrink-0 overflow-y-auto">
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
                
                <!-- Admin Menu -->
                <div class="mt-4">
                    <div class="px-6 py-3">
                        <p class="text-purple-200 text-xs uppercase tracking-wider font-semibold">الإدارة</p>
                    </div>
                    
                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-white bg-opacity-20 border-r-4 border-white' : '' }}">
                        <i class="fas fa-users ml-3"></i>
                        <span>إدارة المستخدمين</span>
                    </a>
                    
                    <a href="{{ route('admin.roles.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('admin.roles.*') ? 'bg-white bg-opacity-20 border-r-4 border-white' : '' }}">
                        <i class="fas fa-user-tag ml-3"></i>
                        <span>إدارة الأدوار</span>
                    </a>
                    
                    <a href="{{ route('admin.permissions.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('admin.permissions.*') ? 'bg-white bg-opacity-20 border-r-4 border-white' : '' }}">
                        <i class="fas fa-key ml-3"></i>
                        <span>إدارة الصلاحيات</span>
                    </a>
                    
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-white hover:bg-opacity-10 transition-colors {{ request()->routeIs('admin.settings.*') ? 'bg-white bg-opacity-20 border-r-4 border-white' : '' }}">
                        <i class="fas fa-cogs ml-3"></i>
                        <span>إعدادات النظام</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center py-4">
                        <div class="flex items-center">
                            <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'الإدارة')</h2>
                        </div>
                        
                        <div class="flex items-center space-x-4 space-x-reverse">
                            <!-- User Menu -->
                            <div class="relative">
                                <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-medium">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                    <span class="mr-2 text-gray-700">{{ auth()->user()->name }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    @stack('scripts')
</body>
</html>
