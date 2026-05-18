"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { CheckCircle, XCircle, Trash2, Search } from "lucide-react";

export default function AdminEventsPage() {
  const [events, setEvents] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("");

  function fetchEvents() {
    setLoading(true);
    const params = new URLSearchParams();
    if (search) params.set("search", search);
    if (statusFilter) params.set("status", statusFilter);
    api.get<any>(`/admin/events?${params}`).then((res) => setEvents(res.data ?? [])).catch(() => {}).finally(() => setLoading(false));
  }

  useEffect(() => { fetchEvents(); }, [statusFilter]);

  function handleApprove(id: number) {
    api.post(`/admin/events/${id}/approve`).then(fetchEvents);
  }

  function handleReject(id: number) {
    api.post(`/admin/events/${id}/reject`, { reason: "مرفوض من المشرف" }).then(fetchEvents);
  }

  function handleDelete(id: number) {
    if (!confirm("هل أنت متأكد؟")) return;
    api.delete(`/admin/events/${id}`).then(fetchEvents);
  }

  const badges: Record<string, string> = {
    draft: "bg-gray-100 text-gray-600",
    pending_approval: "bg-amber-100 text-amber-600",
    published: "bg-green-100 text-green-600",
    cancelled: "bg-red-100 text-red-600",
    completed: "bg-blue-100 text-blue-600",
  };

  return (
    <div>
      <h1 className="mb-6 text-2xl font-bold">إدارة الفعاليات</h1>

      <div className="mb-4 flex flex-wrap gap-3">
        <div className="relative flex-1">
          <Search className="absolute right-3 top-2.5 h-4 w-4 text-gray-400" />
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => e.key === "Enter" && fetchEvents()}
            placeholder="بحث..."
            className="w-full rounded-lg border px-4 py-2 pr-10 outline-none focus:border-primary"
          />
        </div>
        <select
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
          className="rounded-lg border px-4 py-2 outline-none focus:border-primary"
        >
          <option value="">كل الحالات</option>
          <option value="draft">مسودة</option>
          <option value="pending_approval">بانتظار الموافقة</option>
          <option value="published">منشور</option>
          <option value="cancelled">ملغي</option>
          <option value="completed">مكتمل</option>
        </select>
        <button onClick={fetchEvents} className="rounded-lg bg-primary px-4 py-2 text-sm text-white hover:bg-primary-dark">بحث</button>
      </div>

      {loading ? (
        <div className="h-48 animate-pulse rounded-xl bg-gray-200" />
      ) : (
        <div className="overflow-x-auto rounded-xl bg-white shadow-sm">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b bg-gray-50 text-right">
                <th className="px-4 py-3 font-medium">العنوان</th>
                <th className="px-4 py-3 font-medium">الحالة</th>
                <th className="px-4 py-3 font-medium">التصنيف</th>
                <th className="px-4 py-3 font-medium">التاريخ</th>
                <th className="px-4 py-3 font-medium">إجراءات</th>
              </tr>
            </thead>
            <tbody>
              {events.map((e: any) => (
                <tr key={e.id} className="border-b last:border-0 hover:bg-gray-50">
                  <td className="px-4 py-3 font-medium">{e.title_en}</td>
                  <td className="px-4 py-3">
                    <span className={`rounded px-2 py-0.5 text-xs font-medium ${badges[e.status] ?? "bg-gray-100"}`}>
                      {e.status}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-gray-500">{e.category}</td>
                  <td className="px-4 py-3 text-gray-500">
                    {new Date(e.start_date).toLocaleDateString("ar-EG")}
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex gap-1">
                      {e.status === "pending_approval" && (
                        <>
                          <button onClick={() => handleApprove(e.id)} className="rounded p-1.5 text-green-600 hover:bg-green-50" title="موافقة">
                            <CheckCircle className="h-4 w-4" />
                          </button>
                          <button onClick={() => handleReject(e.id)} className="rounded p-1.5 text-red-600 hover:bg-red-50" title="رفض">
                            <XCircle className="h-4 w-4" />
                          </button>
                        </>
                      )}
                      <button onClick={() => handleDelete(e.id)} className="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600" title="حذف">
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {events.length === 0 && (
                <tr><td colSpan={5} className="px-4 py-8 text-center text-gray-400">لا توجد فعاليات</td></tr>
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
