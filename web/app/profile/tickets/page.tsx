"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import type { Ticket } from "@/lib/types";
import Link from "next/link";
import { useAuth } from "@/components/layout/auth-provider";

export default function TicketsPage() {
  const { user } = useAuth();
  const [tickets, setTickets] = useState<Ticket[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get<{ data: Ticket[] }>("/profile/tickets").then((r) => setTickets(r.data)).catch(() => {}).finally(() => setLoading(false));
  }, []);

  if (!user) return <div className="py-16 text-center"><Link href="/login" className="text-primary">تسجيل الدخول</Link></div>;

  if (loading) return <div className="h-48 animate-pulse rounded-xl bg-gray-200" />;

  return (
    <div className="mx-auto max-w-2xl">
      <h1 className="mb-6 text-2xl font-bold">تذاكري</h1>
      {tickets.length === 0 ? (
        <div className="py-12 text-center text-gray-500">
          <p>لا توجد تذاكر</p>
          <Link href="/" className="text-primary hover:underline">اكتشف الفعاليات</Link>
        </div>
      ) : (
        <div className="space-y-4">
          {tickets.map((t) => (
            <div key={t.id} className="rounded-xl bg-white p-4 shadow-sm">
              <div className="flex items-start justify-between">
                <div>
                  <p className="font-bold">{t.event?.title_en || "فعالية"}</p>
                  <p className="text-sm text-gray-500">{t.holder_name}</p>
                  <p className="mt-1 text-primary font-bold">{t.currency} {t.price_paid}</p>
                </div>
                <span className={`rounded px-2 py-1 text-xs ${t.checked_in ? "bg-red-100 text-red-600" : "bg-green-100 text-green-600"}`}>
                  {t.checked_in ? "مستخدم" : "صالح"}
                </span>
              </div>
              <p className="mt-2 font-mono text-xs text-gray-400">{t.ticket_code}</p>
              {t.qr_code_url && <div className="mt-2 inline-block rounded bg-gray-100 p-2"><span className="text-xs">رمز QR متاح في التطبيق</span></div>}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
