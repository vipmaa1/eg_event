"use client";

import { useAuth } from "@/components/layout/auth-provider";
import Link from "next/link";

export default function SettingsPage() {
  const { user } = useAuth();
  if (!user) return <Link href="/login">تسجيل الدخول</Link>;

  return (
    <div className="mx-auto max-w-lg">
      <h1 className="mb-6 text-2xl font-bold">الإعدادات</h1>
      <div className="space-y-4 rounded-xl bg-white p-6 shadow-sm">
        <div className="flex items-center justify-between">
          <span>اللغة العربية</span>
          <span className="text-primary">مفعلة</span>
        </div>
        <div className="flex items-center justify-between">
          <span>إشعارات الفعاليات</span>
          <span className="text-primary">مفعلة</span>
        </div>
        <div className="flex items-center justify-between">
          <span>إشعارات التذاكر</span>
          <span className="text-primary">مفعلة</span>
        </div>
        <div className="border-t pt-4 text-sm text-gray-500">
          EventHub v1.0.0
        </div>
      </div>
    </div>
  );
}
