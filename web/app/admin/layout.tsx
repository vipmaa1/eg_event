"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useAuth } from "@/components/layout/auth-provider";
import {
  LayoutDashboard, CalendarRange, Users, Store, Settings, Building2,
} from "lucide-react";

const navItems = [
  { href: "/admin", label: "لوحة الإحصائيات", icon: LayoutDashboard },
  { href: "/admin/events", label: "الفعاليات", icon: CalendarRange },
  { href: "/admin/users", label: "المستخدمين", icon: Users },
  { href: "/admin/organizers", label: "المنظمين", icon: Store },
  { href: "/admin/settings", label: "الإعدادات", icon: Settings },
];

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const { user } = useAuth();
  const pathname = usePathname();

  if (!user || !user.roles.some((r) => r.role === "admin")) {
    return <div className="py-16 text-center text-red-500">غير مصرح بالوصول</div>;
  }

  return (
    <div className="flex gap-6">
      <aside className="hidden w-56 shrink-0 space-y-1 md:block">
        {navItems.map((item) => {
          const active = pathname === item.href;
          return (
            <Link
              key={item.href}
              href={item.href}
              className={`flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm transition ${
                active
                  ? "bg-primary font-medium text-white"
                  : "text-gray-600 hover:bg-gray-100"
              }`}
            >
              <item.icon className="h-4 w-4" />
              {item.label}
            </Link>
          );
        })}
      </aside>
      <main className="min-w-0 flex-1">{children}</main>
    </div>
  );
}
