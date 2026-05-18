"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Link from "next/link";
import { CalendarRange, Users, Store, Building2, Clock, DollarSign } from "lucide-react";

export default function AdminDashboardPage() {
  const [data, setData] = useState<Record<string, number> | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get<Record<string, number>>("/admin/dashboard").then(setData).catch(() => {}).finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="h-48 animate-pulse rounded-xl bg-gray-200" />;

  const cards = [
    { label: "إجمالي الفعاليات", value: data?.total_events ?? 0, icon: CalendarRange, color: "text-blue-600", bg: "bg-blue-50" },
    { label: "المستخدمين", value: data?.total_users ?? 0, icon: Users, color: "text-green-600", bg: "bg-green-50" },
    { label: "المنظمين", value: data?.total_organizers ?? 0, icon: Store, color: "text-purple-600", bg: "bg-purple-50" },
    { label: "المواقع", value: data?.total_venues ?? 0, icon: Building2, color: "text-orange-600", bg: "bg-orange-50" },
    { label: "بانتظار الموافقة", value: data?.pending_approval ?? 0, icon: Clock, color: "text-amber-600", bg: "bg-amber-50" },
    { label: "الإيرادات", value: `EGP ${(data?.total_revenue ?? 0).toLocaleString()}`, icon: DollarSign, color: "text-emerald-600", bg: "bg-emerald-50" },
  ];

  return (
    <div>
      <h1 className="mb-6 text-2xl font-bold">لوحة التحكم</h1>
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {cards.map((c) => (
          <div key={c.label} className={`rounded-xl ${c.bg} p-6 shadow-sm`}>
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-500">{c.label}</p>
                <p className={`mt-1 text-2xl font-bold ${c.color}`}>{c.value}</p>
              </div>
              <c.icon className={`h-8 w-8 ${c.color}`} />
            </div>
          </div>
        ))}
      </div>

      {(data?.pending_approval ?? 0) > 0 && (
        <div className="mt-6">
          <Link href="/admin/events?status=pending_approval" className="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600">
            <Clock className="h-4 w-4" />
            {data?.pending_approval} فعالية بانتظار الموافقة
          </Link>
        </div>
      )}
    </div>
  );
}
