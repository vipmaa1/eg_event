"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { api } from "@/lib/api";

interface VenueOption {
  id: number;
  name_en: string;
  name_ar?: string;
  city: string;
}

const categories = [
  { value: "concerts", label: "حفلات" },
  { value: "sports", label: "رياضة" },
  { value: "business", label: "أعمال" },
  { value: "cultural", label: "ثقافة" },
  { value: "food", label: "طعام" },
  { value: "arts", label: "فن" },
  { value: "family", label: "عائلي" },
  { value: "nightlife", label: "حياة ليلية" },
  { value: "other", label: "أخرى" },
];

export default function CreateEventPage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState(false);
  const [venues, setVenues] = useState<VenueOption[]>([]);

  useEffect(() => {
    api.get<{ data: VenueOption[] }>("/venues").then((res) => setVenues(res.data ?? [])).catch(() => {});
  }, []);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError("");
    setLoading(true);
    const form = new FormData(e.currentTarget);
    try {
      const payload: Record<string, any> = {
        title_en: form.get("title_en"),
        title_ar: form.get("title_ar"),
        description_en: form.get("description_en"),
        description_ar: form.get("description_ar"),
        start_date: form.get("start_date") + ":00",
        end_date: form.get("end_date") + ":00",
        category: form.get("category"),
        is_free: form.get("is_free") === "on",
      };
      const venueId = form.get("venue_id");
      if (venueId) payload.venue_id = Number(venueId);
      const cover = form.get("cover_image");
      if (cover) payload.cover_image = cover;

      await api.post("/events", payload);
      setSuccess(true);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  if (success) return <div className="py-16 text-center"><p className="text-lg font-bold text-green-600">تم إنشاء الفعالية بنجاح!</p><p className="mt-2 text-sm text-gray-500">بإنتظار مراجعة المشرف</p><button onClick={() => router.push("/organizer/dashboard")} className="mt-4 text-primary hover:underline">العودة للوحة التحكم</button></div>;

  return (
    <div className="mx-auto max-w-2xl">
      <h1 className="mb-6 text-2xl font-bold">إنشاء فعالية جديدة</h1>
      <form onSubmit={handleSubmit} className="space-y-4 rounded-xl bg-white p-6 shadow-sm">
        {error && <p className="rounded-lg bg-red-50 p-3 text-sm text-red-600">{error}</p>}

        <div>
          <label className="mb-1 block text-sm font-medium">العنوان (إنجليزي)</label>
          <input name="title_en" required className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">العنوان (عربي)</label>
          <input name="title_ar" className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">الوصف (إنجليزي)</label>
          <textarea name="description_en" rows={4} required className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">الوصف (عربي)</label>
          <textarea name="description_ar" rows={4} className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
        </div>

        <div className="grid gap-4 md:grid-cols-2">
          <div>
            <label className="mb-1 block text-sm font-medium">تاريخ البداية</label>
            <input name="start_date" type="datetime-local" required className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium">تاريخ النهاية</label>
            <input name="end_date" type="datetime-local" required className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
          </div>
        </div>

        <div>
          <label className="mb-1 block text-sm font-medium">التصنيف</label>
          <select name="category" required className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary">
            {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
          </select>
        </div>

        <div>
          <label className="mb-1 block text-sm font-medium">الموقع</label>
          <select name="venue_id" className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary">
            <option value="">اختر موقع (اختياري)</option>
            {venues.map((v) => <option key={v.id} value={v.id}>{v.name_en} - {v.city}</option>)}
          </select>
        </div>

        <div>
          <label className="mb-1 block text-sm font-medium">رابط صورة الغلاف</label>
          <input name="cover_image" placeholder="https://..." className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
        </div>

        <label className="flex items-center gap-2 text-sm">
          <input name="is_free" type="checkbox" className="h-4 w-4 rounded border-gray-300" />
          فعالية مجانية
        </label>

        <button type="submit" disabled={loading}
          className="w-full rounded-lg bg-primary py-2.5 font-medium text-white hover:bg-primary-dark disabled:opacity-50">
          {loading ? "جاري الإنشاء..." : "إنشاء الفعالية"}
        </button>
      </form>
    </div>
  );
}
