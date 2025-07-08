# MaxCon ERP - Login Credentials & System Information

## 🔑 **Login Credentials**

### **Super Admin (Master Admin)**
- **Email**: `admin@maxcon-erp.com`
- **Password**: `MaxCon@2025`
- **Role**: Super Admin
- **Access**: Master Admin Dashboard
- **Permissions**: Full system access, tenant management, system monitoring

### **Tenant Admin (Demo Pharmacy)**
- **Email**: `admin@demo-pharmacy.com`
- **Password**: `Demo@2025`
- **Role**: Tenant Admin
- **Company**: Demo Pharmacy
- **Access**: Tenant Dashboard
- **Permissions**: Full tenant access, user management, business operations

## 🌐 **Access URLs**

### **Login Page**
- **Simple Login**: http://localhost:8000/simple-login

### **Master Admin Dashboard**
- **Dashboard**: http://localhost:8000/master-admin/dashboard
- **Tenants Management**: http://localhost:8000/master-admin/tenants
- **System Monitoring**: http://localhost:8000/master-admin/system/monitoring
- **System Settings**: http://localhost:8000/master-admin/system/settings
- **System Backups**: http://localhost:8000/master-admin/system/backups
- **System Logs**: http://localhost:8000/master-admin/system/logs

### **Tenant Dashboard**
- **Dashboard**: http://localhost:8000/dashboard (redirects based on user role)

## 🏢 **Tenant Information**

### **Demo Pharmacy Details**
- **Tenant ID**: 1
- **Name**: Demo Pharmacy
- **Domain**: demo
- **Company Type**: Pharmacy
- **Contact Person**: Ahmed Al-Baghdadi
- **Phone**: +964-770-123-4567
- **Address**: Baghdad, Al-Karrada Street, Iraq
- **City**: Baghdad
- **Governorate**: Baghdad
- **License Status**: Active
- **License Expires**: 2026-07-09
- **Max Users**: 10
- **Created By**: Super Admin

## 🛠️ **System Configuration**

### **Database**
- **Type**: SQLite
- **File**: database/database.sqlite
- **Landlord Tables**: tenants, tenant_backups, backup_schedules, backup_restore_logs
- **Tenant Tables**: All business tables (users, products, customers, etc.)

### **Environment**
- **Framework**: Laravel 11
- **PHP Version**: {{ PHP_VERSION }}
- **Environment**: Local Development
- **Debug Mode**: Enabled
- **Locale**: Arabic (ar) - RTL
- **Timezone**: Asia/Baghdad (GMT+3)

### **Features Enabled**
- ✅ Multi-tenancy (Spatie Laravel Multitenancy)
- ✅ Role-based permissions (Spatie Laravel Permission)
- ✅ Arabic RTL interface
- ✅ Master Admin dashboard
- ✅ Tenant management
- ✅ System monitoring
- ✅ Backup management
- ✅ Security logging
- ✅ Two-factor authentication ready
- ✅ QR code generation
- ✅ PDF generation with RTL support
- ✅ Excel import/export

## 📋 **Available Modules**

### **Core Modules**
- **Sales Management**: Orders, invoices, customers, payments
- **Inventory Management**: Products, stock, warehouses, movements
- **HR Management**: Employees, departments, payroll, attendance
- **Financial Management**: Accounts, transactions, journal entries
- **CRM**: Leads, opportunities, communications
- **Document Management**: Files, folders, permissions, workflows
- **Reports & Analytics**: Business intelligence, dashboards, KPIs

### **Specialized Modules**
- **Pharmaceutical Regulatory Affairs**: Drug registration, compliance, testing
- **Advanced Inventory**: Multi-warehouse, batch tracking, barcodes
- **Backup Management**: Automated backups, restore capabilities

## 🔐 **Security Features**

### **Authentication**
- Multi-factor authentication support
- Session management
- Password policies
- Login attempt limiting

### **Authorization**
- Role-based access control
- Permission-based restrictions
- Tenant isolation
- Super admin privileges

### **Auditing**
- Security event logging
- User activity tracking
- System monitoring
- Access logs

## 🚀 **Quick Start Guide**

1. **Start the server**: `php artisan serve`
2. **Access login page**: http://localhost:8000/simple-login
3. **Login as Super Admin**: Use `admin@maxcon-erp.com` / `MaxCon@2025`
4. **Manage tenants**: Create, edit, monitor tenant accounts
5. **Login as Tenant**: Use `admin@demo-pharmacy.com` / `Demo@2025`
6. **Explore features**: Sales, inventory, HR, reports, etc.

## 📞 **Support Information**

### **System Administrator**
- **Name**: MaxCon Super Admin
- **Email**: admin@maxcon-erp.com
- **System**: MaxCon ERP v1.0
- **Documentation**: Available in Arabic
- **Support**: Iraq market focused

---

**Note**: This is a development environment. For production deployment, ensure proper security configurations, SSL certificates, and environment-specific settings.

**Last Updated**: {{ now()->format('Y-m-d H:i:s') }}
