@extends('layouts.master-admin')

@section('title', 'سجلات النظام')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">سجلات النظام</h1>
        <div>
            <button class="btn btn-warning" onclick="clearLogs()">
                <i class="fas fa-trash"></i> مسح السجلات
            </button>
            <button class="btn btn-info" onclick="downloadLogs()">
                <i class="fas fa-download"></i> تحميل السجلات
            </button>
        </div>
    </div>

    <!-- Log Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">أخطاء</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">3</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">تحذيرات</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">12</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">معلومات</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">156</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-info-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">نجح</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">1,247</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">فلاتر السجلات</h6>
        </div>
        <div class="card-body">
            <form class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="logLevel">مستوى السجل</label>
                        <select class="form-control" id="logLevel">
                            <option value="">جميع المستويات</option>
                            <option value="error">خطأ</option>
                            <option value="warning">تحذير</option>
                            <option value="info">معلومات</option>
                            <option value="debug">تصحيح</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="dateFrom">من تاريخ</label>
                        <input type="date" class="form-control" id="dateFrom" value="{{ now()->subDays(7)->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="dateTo">إلى تاريخ</label>
                        <input type="date" class="form-control" id="dateTo" value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="searchTerm">البحث</label>
                        <input type="text" class="form-control" id="searchTerm" placeholder="البحث في السجلات...">
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">تطبيق الفلاتر</button>
                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">إعادة تعيين</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Log Entries -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">إدخالات السجل</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>الوقت</th>
                            <th>المستوى</th>
                            <th>الرسالة</th>
                            <th>المستخدم</th>
                            <th>IP</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ now()->subMinutes(5)->format('Y-m-d H:i:s') }}</td>
                            <td><span class="badge badge-danger">خطأ</span></td>
                            <td>فشل في الاتصال بقاعدة البيانات</td>
                            <td>النظام</td>
                            <td>127.0.0.1</td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewLogDetails(1)">
                                    <i class="fas fa-eye"></i> عرض
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ now()->subMinutes(15)->format('Y-m-d H:i:s') }}</td>
                            <td><span class="badge badge-warning">تحذير</span></td>
                            <td>استخدام ذاكرة عالي: 85%</td>
                            <td>النظام</td>
                            <td>127.0.0.1</td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewLogDetails(2)">
                                    <i class="fas fa-eye"></i> عرض
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ now()->subMinutes(30)->format('Y-m-d H:i:s') }}</td>
                            <td><span class="badge badge-success">معلومات</span></td>
                            <td>تم تسجيل دخول المستخدم بنجاح</td>
                            <td>admin@maxcon-erp.com</td>
                            <td>192.168.1.100</td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewLogDetails(3)">
                                    <i class="fas fa-eye"></i> عرض
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ now()->subHour()->format('Y-m-d H:i:s') }}</td>
                            <td><span class="badge badge-info">تصحيح</span></td>
                            <td>تم تنفيذ مهمة النسخ الاحتياطي</td>
                            <td>النظام</td>
                            <td>127.0.0.1</td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewLogDetails(4)">
                                    <i class="fas fa-eye"></i> عرض
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ now()->subHours(2)->format('Y-m-d H:i:s') }}</td>
                            <td><span class="badge badge-success">معلومات</span></td>
                            <td>تم إنشاء مستأجر جديد: صيدلية الشفاء</td>
                            <td>admin@maxcon-erp.com</td>
                            <td>192.168.1.100</td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewLogDetails(5)">
                                    <i class="fas fa-eye"></i> عرض
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <nav aria-label="Log pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">السابق</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">التالي</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
function clearLogs() {
    if (confirm('هل أنت متأكد من مسح جميع السجلات؟ هذا الإجراء لا يمكن التراجع عنه.')) {
        alert('تم مسح السجلات بنجاح');
    }
}

function downloadLogs() {
    alert('تحميل ملف السجلات...');
}

function viewLogDetails(logId) {
    alert('عرض تفاصيل السجل رقم: ' + logId);
}

function resetFilters() {
    document.getElementById('logLevel').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('searchTerm').value = '';
}
</script>
@endsection
