"use client";

import Link from "next/link";
import { useAuth } from "@/components/layout/auth-provider";
import { Ticket, Settings, LogOut } from "lucide-react";

export default function ProfilePage() {
  const { user, logout } = useAuth();

  if (!user) return <div className="py-16 text-center"><p>يجب تسجيل الدخول</p><Link href="/login" className="text-primary">تسجيل الدخول</Link></div>;

  return (
    <div className="mx-auto max-w-lg pt-4">
      <div className="mb-6 text-center">
        <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-primary-light text-3xl font-bold text-primary">
          {user.name[0]}
        </div>
        <h1 className="mt-3 text-xl font-bold">{user.name}</h1>
        <p className="text-gray-500">{user.email}</p>
      </div>
      <div className="space-y-2 rounded-xl bg-white p-2 shadow-sm">
        <Link href="/profile/tickets" className="flex items-center gap-3 rounded-lg p-3 hover:bg-gray-50">
          <Ticket className="h-5 w-5 text-primary" /> <span>تذاكري</span>
        </Link>
        <Link href="/profile/settings" className="flex items-center gap-3 rounded-lg p-3 hover:bg-gray-50">
          <Settings className="h-5 w-5 text-gray-500" /> <span>الإعدادات</span>
        </Link>
        <button onClick={logout} className="flex w-full items-center gap-3 rounded-lg p-3 text-red-600 hover:bg-red-50">
          <LogOut className="h-5 w-5" /> <span>تسجيل الخروج</span>
        </button>
      </div>
    </div>
  );
}
