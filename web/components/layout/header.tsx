"use client";

import Link from "next/link";
import { useAuth } from "./auth-provider";
import { Search, Ticket, User, LogOut, PlusCircle, LayoutDashboard } from "lucide-react";

export function Header() {
  const { user, logout } = useAuth();

  return (
    <header className="sticky top-0 z-50 border-b bg-white/95 backdrop-blur">
      <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
        <Link href="/" className="flex items-center gap-2 text-xl font-bold text-primary">
          <Ticket className="h-6 w-6" />
          EventHub
        </Link>

        <nav className="hidden items-center gap-6 md:flex">
          <Link href="/search" className="flex items-center gap-1 text-sm text-gray-600 hover:text-primary">
            <Search className="h-4 w-4" /> بحث
          </Link>
          {user ? (
            <>
              {user.roles.some((r) => r.role === "admin") && (
                <Link href="/admin" className="flex items-center gap-1 text-sm text-gray-600 hover:text-primary">
                  <LayoutDashboard className="h-4 w-4" /> لوحة التحكم
                </Link>
              )}
              {!user.roles.some((r) => r.role === "admin") && user.roles.some((r) => r.role === "organizer") && (
                <>
                  <Link href="/organizer/dashboard" className="flex items-center gap-1 text-sm text-gray-600 hover:text-primary">
                    <LayoutDashboard className="h-4 w-4" /> لوحة التحكم
                  </Link>
                  <Link href="/organizer/events/new" className="flex items-center gap-1 text-sm text-gray-600 hover:text-primary">
                    <PlusCircle className="h-4 w-4" /> فعالية جديدة
                  </Link>
                </>
              )}
              <Link href="/profile/tickets" className="flex items-center gap-1 text-sm text-gray-600 hover:text-primary">
                <Ticket className="h-4 w-4" /> تذاكري
              </Link>
              <div className="flex items-center gap-2">
                <Link href="/profile" className="flex items-center gap-1 text-sm text-gray-600 hover:text-primary">
                  <User className="h-4 w-4" /> {user.name}
                </Link>
                <button onClick={logout} className="flex items-center gap-1 text-sm text-red-500 hover:text-red-700">
                  <LogOut className="h-4 w-4" />
                </button>
              </div>
            </>
          ) : (
            <>
              <Link href="/login" className="text-sm text-gray-600 hover:text-primary">دخول</Link>
              <Link href="/register" className="rounded-lg bg-primary px-4 py-2 text-sm text-white hover:bg-primary-dark">
                إنشاء حساب
              </Link>
            </>
          )}
        </nav>
      </div>
    </header>
  );
}
