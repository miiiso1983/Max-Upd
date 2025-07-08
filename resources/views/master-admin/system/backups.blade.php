@extends('layouts.master-admin')

@section('title', 'النسخ الاحتياطية')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">النسخ الاحتياطية</h1>
        <div>
            <button class="btn btn-primary" onclick="createBackup()">
                <i class="fas fa-plus"></i> إنشاء نسخة احتياطية
            </button>
        </div>
    </div>

    <!-- Backup Status Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">آخر نسخة احتياطية</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ now()->subHours(2)->format('H:i') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">إجمالي النسخ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">15</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">حجم النسخ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2.5 GB</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">النسخ الناجحة</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">98%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">قائمة النسخ الاحتياطية</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>اسم النسخة</th>
                            <th>التاريخ</th>
                            <th>الحجم</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>backup_{{ now()->format('Y_m_d_H_i') }}.sql</td>
                            <td>{{ now()->subHours(2)->format('Y-m-d H:i:s') }}</td>
                            <td>156 MB</td>
                            <td><span class="badge badge-primary">تلقائي</span></td>
                            <td><span class="badge badge-success">مكتمل</span></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="downloadBackup('backup_1')">
                                    <i class="fas fa-download"></i> تحميل
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="restoreBackup('backup_1')">
                                    <i class="fas fa-undo"></i> استعادة
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteBackup('backup_1')">
                                    <i class="fas fa-trash"></i> حذف
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>backup_{{ now()->subDay()->format('Y_m_d_H_i') }}.sql</td>
                            <td>{{ now()->subDay()->format('Y-m-d H:i:s') }}</td>
                            <td>152 MB</td>
                            <td><span class="badge badge-primary">تلقائي</span></td>
                            <td><span class="badge badge-success">مكتمل</span></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="downloadBackup('backup_2')">
                                    <i class="fas fa-download"></i> تحميل
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="restoreBackup('backup_2')">
                                    <i class="fas fa-undo"></i> استعادة
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteBackup('backup_2')">
                                    <i class="fas fa-trash"></i> حذف
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>manual_backup_{{ now()->subDays(2)->format('Y_m_d') }}.sql</td>
                            <td>{{ now()->subDays(2)->format('Y-m-d H:i:s') }}</td>
                            <td>148 MB</td>
                            <td><span class="badge badge-secondary">يدوي</span></td>
                            <td><span class="badge badge-success">مكتمل</span></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="downloadBackup('backup_3')">
                                    <i class="fas fa-download"></i> تحميل
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="restoreBackup('backup_3')">
                                    <i class="fas fa-undo"></i> استعادة
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteBackup('backup_3')">
                                    <i class="fas fa-trash"></i> حذف
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Backup Schedule -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">جدولة النسخ الاحتياطية</h6>
        </div>
        <div class="card-body">
            <form>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="scheduleType">نوع الجدولة</label>
                            <select class="form-control" id="scheduleType">
                                <option value="daily" selected>يومي</option>
                                <option value="weekly">أسبوعي</option>
                                <option value="monthly">شهري</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="scheduleTime">وقت التنفيذ</label>
                            <input type="time" class="form-control" id="scheduleTime" value="02:00">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="enableSchedule" checked>
                                <label class="custom-control-label" for="enableSchedule">تفعيل الجدولة التلقائية</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="emailNotification" checked>
                                <label class="custom-control-label" for="emailNotification">إشعار بالبريد الإلكتروني</label>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">حفظ الجدولة</button>
            </form>
        </div>
    </div>
</div>

<script>
function createBackup() {
    if (confirm('هل أنت متأكد من إنشاء نسخة احتياطية جديدة؟')) {
        // Simulate backup creation
        alert('تم بدء عملية إنشاء النسخة الاحتياطية...');
    }
}

function downloadBackup(backupId) {
    alert('تحميل النسخة الاحتياطية: ' + backupId);
}

function restoreBackup(backupId) {
    if (confirm('هل أنت متأكد من استعادة هذه النسخة الاحتياطية؟ سيتم استبدال البيانات الحالية.')) {
        alert('تم بدء عملية الاستعادة...');
    }
}

function deleteBackup(backupId) {
    if (confirm('هل أنت متأكد من حذف هذه النسخة الاحتياطية؟')) {
        alert('تم حذف النسخة الاحتياطية: ' + backupId);
    }
}
</script>
@endsection
