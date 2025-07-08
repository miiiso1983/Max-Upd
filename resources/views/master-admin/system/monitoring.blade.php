@extends('layouts.master-admin')

@section('title', 'مراقبة النظام')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">مراقبة النظام</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('master-admin.dashboard') }}">لوحة التحكم</a></li>
                <li class="breadcrumb-item active">مراقبة النظام</li>
            </ol>
        </nav>
    </div>

    <!-- System Status Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">حالة النظام</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">متصل</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-server fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">وقت التشغيل</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">99.9%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">استخدام المعالج</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">45%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 45%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-microchip fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">استخدام الذاكرة</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">62%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 62%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-memory fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Metrics -->
    <div class="row">
        <!-- Server Performance Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">أداء الخادم</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">معلومات النظام</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-gray-500">نظام التشغيل</div>
                        <div class="font-weight-bold">{{ PHP_OS }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-gray-500">إصدار PHP</div>
                        <div class="font-weight-bold">{{ PHP_VERSION }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-gray-500">إصدار Laravel</div>
                        <div class="font-weight-bold">{{ app()->version() }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-gray-500">قاعدة البيانات</div>
                        <div class="font-weight-bold">SQLite</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-gray-500">مساحة التخزين المستخدمة</div>
                        <div class="font-weight-bold">45%</div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 45%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Processes -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">العمليات النشطة</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>العملية</th>
                                    <th>الحالة</th>
                                    <th>استخدام المعالج</th>
                                    <th>استخدام الذاكرة</th>
                                    <th>وقت البدء</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Laravel Queue Worker</td>
                                    <td><span class="badge badge-success">نشط</span></td>
                                    <td>2.5%</td>
                                    <td>45 MB</td>
                                    <td>{{ now()->subHours(2)->format('H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td>Laravel Scheduler</td>
                                    <td><span class="badge badge-success">نشط</span></td>
                                    <td>1.2%</td>
                                    <td>32 MB</td>
                                    <td>{{ now()->subHours(1)->format('H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td>Database Connection Pool</td>
                                    <td><span class="badge badge-success">نشط</span></td>
                                    <td>0.8%</td>
                                    <td>28 MB</td>
                                    <td>{{ now()->subMinutes(30)->format('H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Performance Chart
var ctx = document.getElementById('performanceChart').getContext('2d');
var performanceChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
        datasets: [{
            label: 'استخدام المعالج %',
            data: [30, 45, 60, 55, 70, 45, 40],
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }, {
            label: 'استخدام الذاكرة %',
            data: [40, 50, 65, 60, 75, 55, 50],
            borderColor: 'rgb(255, 99, 132)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>
@endsection
