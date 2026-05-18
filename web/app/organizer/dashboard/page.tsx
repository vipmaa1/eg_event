"use client";

import { useEffect, useState, useCallback } from "react";
import { api } from "@/lib/api";
import { useAuth } from "@/components/layout/auth-provider";
import Link from "next/link";
import { PlusCircle, QrCode, Eye, Pencil, Send, XCircle } from "lucide-react";

interface OrgEvent {
  id: number;
  title_en: string;
  status: string;
  category: string;
  start_date: string;
  tickets_count: number;
  checked_in_count?: number;
  ticket_types?: { id: number; name_en: string; price: number; quantity_sold: number }[];
}

export default function OrganizerDashboardPage() {
  const { user } = useAuth();
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<number | null>(null);

  const fetch = useCallback(() => {
    api.get<any>("/organizers/mine").then(setData).catch(() => {}).finally(() => setLoading(false));
  }, []);

  useEffect(() => { fetch(); }, [fetch]);

  async function doAction(id: number, action: string) {
    setActionLoading(id);
    try {
      await api.post(`/events/${id}/${action}`);
      fetch();
    } catch {}
    setActionLoading(null);
  }

  if (!user || !user.roles.some((r) => r.role === "organizer")) {
    return <div className="py-16 text-center"><p>يجب أن تكون منظم</p><Link href="/register">سجل كمنظم</Link></div>;
  }

  if (loading) return <div className="h-48 animate-pulse rounded-xl bg-gray-200" />;

  const pending = data?.pending_events ?? 0;
  const events: OrgEvent[] = data?.events ?? [];

  const statusBadge = (s: string) => {
    const colors: Record<string, string> = {
      draft: "bg-gray-100 text-gray-600",
      pending_approval: "bg-amber-100 text-amber-600",
      published: "bg-green-100 text-green-600",
      cancelled: "bg-red-100 text-red-600",
      completed: "bg-blue-100 text-blue-600",
    };
    return <span className={`rounded px-2 py-0.5 text-xs font-medium ${colors[s] ?? "bg-gray-100"}`}>{s}</span>;
  };

  return (
    <div className="mx-auto max-w-5xl">
      <h1 className="mb-6 text-2xl font-bold">لوحة التحكم</h1>

      <div className="mb-6 grid gap-4 md:grid-cols-4">
        {[
          { label: "الفعاليات", value: data?.total_events || 0 },
          { label: "تذاكر مباعة", value: data?.total_tickets_sold || 0 },
          { label: "بانتظار الموافقة", value: pending },
          { label: "الإيرادات", value: `EGP ${data?.total_revenue || 0}` },
        ].map((s) => (
          <div key={s.label} className="rounded-xl bg-white p-5 text-center shadow-sm">
            <p className={`text-2xl font-bold ${s.label === "بانتظار الموافقة" && pending > 0 ? "text-amber-500" : "text-primary"}`}>{s.value}</p>
            <p className="text-sm text-gray-500">{s.label}</p>
          </div>
        ))}
      </div>

      {pending > 0 && (
        <div className="mb-6 rounded-xl bg-amber-50 p-4 text-center text-amber-700">
          {pending} فعالية بانتظار مراجعة المشرف
        </div>
      )}

      <div className="mb-6 grid gap-4 md:grid-cols-3">
        <Link href="/organizer/events/new" className="flex flex-col items-center gap-2 rounded-xl bg-white p-6 shadow-sm transition hover:shadow-md">
          <PlusCircle className="h-8 w-8 text-primary" />
          <span className="font-medium">فعالية جديدة</span>
        </Link>
        <Link href="/organizer/events/new" className="flex flex-col items-center gap-2 rounded-xl bg-white p-6 shadow-sm transition hover:shadow-md">
          <QrCode className="h-8 w-8 text-primary" />
          <span className="font-medium">مسح QR</span>
        </Link>
        <Link href="/profile" className="flex flex-col items-center gap-2 rounded-xl bg-white p-6 shadow-sm transition hover:shadow-md">
          <Pencil className="h-8 w-8 text-primary" />
          <span className="font-medium">الملف الشخصي</span>
        </Link>
      </div>

      {events.length > 0 && (
        <div className="overflow-x-auto rounded-xl bg-white shadow-sm">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b bg-gray-50 text-right">
                <th className="px-4 py-3 font-medium">الفعالية</th>
                <th className="px-4 py-3 font-medium">الحالة</th>
                <th className="px-4 py-3 font-medium">تذاكر</th>
                <th className="px-4 py-3 font-medium">تم الدخول</th>
                <th className="px-4 py-3 font-medium">التاريخ</th>
                <th className="px-4 py-3 font-medium">إجراءات</th>
              </tr>
            </thead>
            <tbody>
              {events.map((e) => (
                <tr key={e.id} className="border-b last:border-0 hover:bg-gray-50">
                  <td className="px-4 py-3 font-medium">{e.title_en}</td>
                  <td className="px-4 py-3">{statusBadge(e.status)}</td>
                  <td className="px-4 py-3 text-gray-500">{e.tickets_count ?? 0}</td>
                  <td className="px-4 py-3 text-gray-500">{e.checked_in_count ?? 0}</td>
                  <td className="px-4 py-3 text-gray-500">{new Date(e.start_date).toLocaleDateString("ar-EG")}</td>
                  <td className="px-4 py-3">
                    <div className="flex gap-1">
                      <Link href={`/events/${e.id}`} className="rounded p-1.5 text-gray-500 hover:bg-gray-100" title="عرض">
                        <Eye className="h-4 w-4" />
                      </Link>
                      {e.status === "draft" && (
                        <Link href={`/organizer/events/${e.id}/edit`} className="rounded p-1.5 text-blue-500 hover:bg-blue-50" title="تعديل">
                          <Pencil className="h-4 w-4" />
                        </Link>
                      )}
                      {e.status === "draft" && (
                        <button onClick={() => doAction(e.id, "publish")} disabled={actionLoading === e.id} className="rounded p-1.5 text-green-500 hover:bg-green-50" title="نشر">
                          <Send className="h-4 w-4" />
                        </button>
                      )}
                      {e.status === "published" && (
                        <button onClick={() => doAction(e.id, "cancel")} disabled={actionLoading === e.id} className="rounded p-1.5 text-red-500 hover:bg-red-50" title="إلغاء">
                          <XCircle className="h-4 w-4" />
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
