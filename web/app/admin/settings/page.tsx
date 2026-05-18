"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { Save } from "lucide-react";

interface Setting {
  id: number;
  key: string;
  value: string;
  type: string;
  group: string;
  description: string | null;
}

export default function AdminSettingsPage() {
  const [settings, setSettings] = useState<Setting[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState("");

  useEffect(() => {
    api.get<Setting[]>("/admin/settings").then(setSettings).catch(() => {}).finally(() => setLoading(false));
  }, []);

  function updateSetting(key: string, value: string) {
    setSettings((prev) => prev.map((s) => (s.key === key ? { ...s, value } : s)));
  }

  async function handleSave(s: Setting) {
    setSaving(true);
    setMessage("");
    try {
      await api.post("/admin/settings", { key: s.key, value: s.value, type: s.type, group: s.group, description: s.description });
      setMessage("تم الحفظ");
    } catch {
      setMessage("فشل الحفظ");
    } finally {
      setSaving(false);
    }
  }

  const grouped = settings.reduce<Record<string, Setting[]>>((acc, s) => {
    const g = s.group || "general";
    if (!acc[g]) acc[g] = [];
    acc[g].push(s);
    return acc;
  }, {});

  if (loading) return <div className="h-48 animate-pulse rounded-xl bg-gray-200" />;

  return (
    <div>
      <h1 className="mb-6 text-2xl font-bold">إعدادات الموقع</h1>

      {message && <p className="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-600">{message}</p>}

      {Object.entries(grouped).map(([group, items]) => (
        <div key={group} className="mb-6 rounded-xl bg-white p-6 shadow-sm">
          <h2 className="mb-4 text-lg font-bold capitalize">{group}</h2>
          <div className="space-y-4">
            {items.map((s) => (
              <div key={s.key} className="flex items-center justify-between gap-4 border-b pb-4 last:border-0">
                <div className="flex-1">
                  <label className="block text-sm font-medium">{s.key}</label>
                  {s.description && <p className="text-xs text-gray-400">{s.description}</p>}
                </div>
                <div className="flex items-center gap-2">
                  {s.type === "boolean" ? (
                    <select
                      value={s.value}
                      onChange={(e) => updateSetting(s.key, e.target.value)}
                      className="rounded-lg border px-3 py-1.5 text-sm outline-none focus:border-primary"
                    >
                      <option value="1">فعال</option>
                      <option value="0">معطل</option>
                    </select>
                  ) : (
                    <input
                      value={s.value}
                      onChange={(e) => updateSetting(s.key, e.target.value)}
                      className="rounded-lg border px-3 py-1.5 text-sm outline-none focus:border-primary"
                    />
                  )}
                  <button onClick={() => handleSave(s)} disabled={saving} className="rounded-lg bg-primary p-2 text-white hover:bg-primary-dark disabled:opacity-50">
                    <Save className="h-4 w-4" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}
