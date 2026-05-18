"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { Ban, CheckCircle, Search } from "lucide-react";

export default function AdminUsersPage() {
  const [users, setUsers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [roleFilter, setRoleFilter] = useState("");

  function fetchUsers() {
    setLoading(true);
    const params = new URLSearchParams();
    if (search) params.set("search", search);
    if (roleFilter) params.set("role", roleFilter);
    api.get<any>(`/admin/users?${params}`).then((res) => setUsers(res.data ?? [])).catch(() => {}).finally(() => setLoading(false));
  }

  useEffect(() => { fetchUsers(); }, [roleFilter]);

  function toggleStatus(id: number) {
    api.post(`/admin/users/${id}/toggle-status`).then(fetchUsers);
  }

  return (
    <div>
      <h1 className="mb-6 text-2xl font-bold">إدارة المستخدمين</h1>

      <div className="mb-4 flex flex-wrap gap-3">
        <div className="relative flex-1">
          <Search className="absolute right-3 top-2.5 h-4 w-4 text-gray-400" />
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => e.key === "Enter" && fetchUsers()}
            placeholder="بحث بالاسم أو البريد..."
            className="w-full rounded-lg border px-4 py-2 pr-10 outline-none focus:border-primary"
          />
        </div>
        <select
          value={roleFilter}
          onChange={(e) => setRoleFilter(e.target.value)}
          className="rounded-lg border px-4 py-2 outline-none focus:border-primary"
        >
          <option value="">كل الأدوار</option>
          <option value="admin">مشرف</option>
          <option value="organizer">منظم</option>
          <option value="attendee">حاضر</option>
          <option value="venue_owner">صاحب موقع</option>
        </select>
        <button onClick={fetchUsers} className="rounded-lg bg-primary px-4 py-2 text-sm text-white hover:bg-primary-dark">بحث</button>
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
                <th className="px-4 py-3 font-medium">الأدوار</th>
                <th className="px-4 py-3 font-medium">الحالة</th>
                <th className="px-4 py-3 font-medium">الطلبات</th>
                <th className="px-4 py-3 font-medium">إجراءات</th>
              </tr>
            </thead>
            <tbody>
              {users.map((u: any) => (
                <tr key={u.id} className="border-b last:border-0 hover:bg-gray-50">
                  <td className="px-4 py-3 font-medium">{u.name}</td>
                  <td className="px-4 py-3 text-gray-500">{u.email}</td>
                  <td className="px-4 py-3">
                    {u.roles?.map((r: any) => (
                      <span key={r.role} className="ml-1 rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{r.role}</span>
                    ))}
                  </td>
                  <td className="px-4 py-3">
                    <span className={`rounded px-2 py-0.5 text-xs font-medium ${u.status === "active" ? "bg-green-100 text-green-600" : "bg-red-100 text-red-600"}`}>
                      {u.status}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-gray-500">{u.orders_count ?? 0}</td>
                  <td className="px-4 py-3">
                    <button onClick={() => toggleStatus(u.id)} className="flex items-center gap-1 rounded p-1.5 text-sm hover:bg-gray-100">
                      {u.status === "active" ? (
                        <><Ban className="h-4 w-4 text-red-500" /> حظر</>
                      ) : (
                        <><CheckCircle className="h-4 w-4 text-green-500" /> تفعيل</>
                      )}
                    </button>
                  </td>
                </tr>
              ))}
              {users.length === 0 && (
                <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-400">لا يوجد مستخدمين</td></tr>
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
