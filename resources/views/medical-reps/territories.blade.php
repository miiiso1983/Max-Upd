@extends('layouts.app')

@section('page-title', 'المناطق')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">إدارة المناطق</h1>
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-plus ml-2"></i>
            إضافة منطقة جديدة
        </button>
    </div>

    <!-- Territories List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($territories as $territory)
                <div class="border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $territory->name }}</h3>
                        <div class="flex space-x-2 space-x-reverse">
                            <button class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">المندوب:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $territory->rep_name }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">العملاء:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $territory->customers_count }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">الهدف الشهري:</span>
                            <span class="text-sm font-medium text-gray-900">{{ number_format($territory->monthly_target) }} د.ع</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">المحقق:</span>
                            <span class="text-sm font-medium text-gray-900">{{ number_format($territory->achieved) }} د.ع</span>
                        </div>
                        
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-600">نسبة الإنجاز:</span>
                                <span class="text-sm font-medium text-gray-900">{{ number_format($territory->achievement_rate, 1) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min($territory->achievement_rate, 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
