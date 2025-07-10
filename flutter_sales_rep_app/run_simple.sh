#!/bin/bash

echo "🚀 تشغيل النسخة المبسطة من تطبيق مندوبي المبيعات..."

# نسخ الملفات المبسطة
cp pubspec_simple.yaml pubspec.yaml
cp lib/main_simple.dart lib/main.dart

echo "📦 تحديث الحزم..."
flutter pub get

echo "🌐 تشغيل التطبيق على Chrome..."
flutter run -d chrome --web-port=8080

echo "✅ تم تشغيل التطبيق على: http://localhost:8080"
