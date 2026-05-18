"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { CheckCircle, XCircle, Search } from "lucide-react";

export default function AdminOrganizersPage() {
  const [organizers, setOrganizers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [verifiedFilter, setVerifiedFilter] = useState("");

  function fetchOrganizers() {
    setLoading(true);
    const params = new URLSearchParams();
    if (search) params.set("search", search);
    if (verifiedFilter) params.set("verified", verifiedFilter);
    api.get<any>(`/admin/organizers?${params}`).then((res) => setOrganizers(res.data ?? [])).catch(() => {}).finally(() => setLoading(false));
  }

  useEffect(() => { fetchOrganizers(); }, [verifiedFilter]);

  function toggleVerify(id: number, current: boolean) {
    api.post(`/admin/organizers/${id}/verify`, { verified: !current }).then(fetchOrganizers);
  }

  return (
    <div>
      <h1 className="mb-6 text-2xl font-bold">إدارة المنظمين</h1>

      <div className="mb-4 flex flex-wrap gap-3">
        <div className="relative flex-1">
          <Search className="absolute right-3 top-2.5 h-4 w-4 text-gray-400" />
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => e.key === "Enter" && fetchOrganizers()}
            placeholder="بحث..."
            className="w-full rounded-lg border px-4 py-2 pr-10 outline-none focus:border-primary"
          />
        </div>
        <select
          value={verifiedFilter}
          onChange={(e) => setVerifiedFilter(e.target.value)}
          className="rounded-lg border px-4 py-2 outline-none focus:border-primary"
        >
          <option value="">الكل</option>
          <option value="1">موثق</option>
          <option value="0">غير موثق</option>
        </select>
        <button onClick={fetchOrganizers} className="rounded-lg bg-primary px-4 py-2 text-sm text-white hover:bg-primary-dark">بحث</button>
      </div>

      {loading ? (
        <div className="h-48 animate-pulse rounded-xl bg-gray-200" />
      ) : (
        <div className="overflow-x-auto rounded-xl bg-white shadow-sm">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b bg-gray-50 text-right">
                <th className="px-4 py-3 font-medium">الاسم</th>
                <th className="px-4 py-3 font-medium">البريد</th>
                <th className="px-4 py-3 font-medium">الحالة</th>
                <th className="px-4 py-3 font-medium">موثق</th>
                <th className="px-4 py-3 font-medium">الفعاليات</th>
                <th className="px-4 py-3 font-medium">إجراءات</th>
              </tr>
            </thead>
            <tbody>
              {organizers.map((o: any) => (
                <tr key={o.id} className="border-b last:border-0 hover:bg-gray-50">
                  <td className="px-4 py-3 font-medium">{o.name_en}</td>
                  <td className="px-4 py-3 text-gray-500">{o.user?.email ?? "--"}</td>
                  <td className="px-4 py-3">
                    <span className={`rounded px-2 py-0.5 text-xs font-medium ${o.status === "active" ? "bg-green-100 text-green-600" : "bg-red-100 text-red-600"}`}>
                      {o.status}
                    </span>
                  </td>
                  <td className="px-4 py-3">
                    {o.verified ? (
                      <span className="flex items-center gap-1 text-green-600"><CheckCircle className="h-4 w-4" /> موثق</span>
                    ) : (
                      <span className="text-gray-400">غير موثق</span>
                    )}
                  </td>
                  <td className="px-4 py-3 text-gray-500">{o.events_count ?? 0}</td>
                  <td className="px-4 py-3">
                    <button onClick={() => toggleVerify(o.id, o.verified)} className={`flex items-center gap-1 rounded px-2 py-1 text-xs ${o.verified ? "bg-red-50 text-red-600" : "bg-green-50 text-green-600"}`}>
                      {o.verified ? <><XCircle className="h-3 w-3" /> إلغاء التوثيق</> : <><CheckCircle className="h-3 w-3" /> توثيق</>}
                    </button>
                  </td>
                </tr>
              ))}
              {organizers.length === 0 && (
                <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">لا يوجد منظمين</td></tr>
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
