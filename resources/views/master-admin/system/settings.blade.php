@extends('layouts.master-admin')

@section('title', 'إعدادات النظام')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">إعدادات النظام</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('master-admin.dashboard') }}">لوحة التحكم</a></li>
                <li class="breadcrumb-item active">إعدادات النظام</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <!-- General Settings -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">الإعدادات العامة</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label for="systemName">اسم النظام</label>
                            <input type="text" class="form-control" id="systemName" value="MaxCon ERP">
                        </div>
                        <div class="form-group">
                            <label for="systemEmail">البريد الإلكتروني للنظام</label>
                            <input type="email" class="form-control" id="systemEmail" value="admin@maxcon-erp.com">
                        </div>
                        <div class="form-group">
                            <label for="timezone">المنطقة الزمنية</label>
                            <select class="form-control" id="timezone">
                                <option value="Asia/Baghdad" selected>بغداد (GMT+3)</option>
                                <option value="UTC">UTC</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="language">اللغة الافتراضية</label>
                            <select class="form-control" id="language">
                                <option value="ar" selected>العربية</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">إعدادات الأمان</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="twoFactorAuth" checked>
                                <label class="custom-control-label" for="twoFactorAuth">المصادقة الثنائية</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="sessionTimeout" checked>
                                <label class="custom-control-label" for="sessionTimeout">انتهاء صلاحية الجلسة</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sessionDuration">مدة الجلسة (بالدقائق)</label>
                            <input type="number" class="form-control" id="sessionDuration" value="120">
                        </div>
                        <div class="form-group">
                            <label for="maxLoginAttempts">الحد الأقصى لمحاولات تسجيل الدخول</label>
                            <input type="number" class="form-control" id="maxLoginAttempts" value="5">
                        </div>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Email Settings -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">إعدادات البريد الإلكتروني</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label for="mailDriver">نوع البريد</label>
                            <select class="form-control" id="mailDriver">
                                <option value="smtp" selected>SMTP</option>
                                <option value="sendmail">Sendmail</option>
                                <option value="mailgun">Mailgun</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mailHost">خادم البريد</label>
                            <input type="text" class="form-control" id="mailHost" value="smtp.gmail.com">
                        </div>
                        <div class="form-group">
                            <label for="mailPort">منفذ البريد</label>
                            <input type="number" class="form-control" id="mailPort" value="587">
                        </div>
                        <div class="form-group">
                            <label for="mailUsername">اسم المستخدم</label>
                            <input type="text" class="form-control" id="mailUsername">
                        </div>
                        <div class="form-group">
                            <label for="mailPassword">كلمة المرور</label>
                            <input type="password" class="form-control" id="mailPassword">
                        </div>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Backup Settings -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">إعدادات النسخ الاحتياطي</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="autoBackup" checked>
                                <label class="custom-control-label" for="autoBackup">النسخ الاحتياطي التلقائي</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="backupFrequency">تكرار النسخ الاحتياطي</label>
                            <select class="form-control" id="backupFrequency">
                                <option value="daily" selected>يومي</option>
                                <option value="weekly">أسبوعي</option>
                                <option value="monthly">شهري</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="backupRetention">مدة الاحتفاظ (بالأيام)</label>
                            <input type="number" class="form-control" id="backupRetention" value="30">
                        </div>
                        <div class="form-group">
                            <label for="backupLocation">موقع النسخ الاحتياطي</label>
                            <select class="form-control" id="backupLocation">
                                <option value="local" selected>محلي</option>
                                <option value="s3">Amazon S3</option>
                                <option value="google">Google Drive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
