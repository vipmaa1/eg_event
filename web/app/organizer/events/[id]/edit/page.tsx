"use client";

import { useEffect, useState } from "react";
import { useRouter, useParams } from "next/navigation";
import { api } from "@/lib/api";

interface VenueOption {
  id: number;
  name_en: string;
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

export default function EditEventPage() {
  const router = useRouter();
  const { id } = useParams<{ id: string }>();
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(true);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState(false);
  const [venues, setVenues] = useState<VenueOption[]>([]);
  const [event, setEvent] = useState<any>(null);

  useEffect(() => {
    Promise.all([
      api.get<{ data: VenueOption[] }>("/venues").then((res) => setVenues(res.data ?? [])),
      api.get<any>(`/events/${id}`).then(setEvent),
    ]).catch(() => router.push("/organizer/dashboard")).finally(() => setFetching(false));
  }, [id, router]);

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
        start_date: (form.get("start_date") as string) + ":00",
        end_date: (form.get("end_date") as string) + ":00",
        category: form.get("category"),
        is_free: form.get("is_free") === "on",
      };
      const venueId = form.get("venue_id");
      if (venueId) payload.venue_id = Number(venueId);
      const cover = form.get("cover_image");
      if (cover) payload.cover_image = cover;

      await api.put(`/events/${id}`, payload);
      setSuccess(true);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  if (fetching) return <div className="h-48 animate-pulse rounded-xl bg-gray-200" />;
  if (!event) return <div className="py-16 text-center text-red-500">الفعالية غير موجودة</div>;
  if (!["draft", "cancelled"].includes(event.status)) {
    return <div className="py-16 text-center text-red-500">لا يمكن تعديل الفعالية بعد النشر</div>;
  }

  if (success) return <div className="py-16 text-center"><p className="text-lg font-bold text-green-600">تم التحديث بنجاح!</p><button onClick={() => router.push("/organizer/dashboard")} className="mt-4 text-primary hover:underline">العودة للوحة التحكم</button></div>;

  const toLocal = (d: string) => d ? d.slice(0, 16) : "";

  return (
    <div className="mx-auto max-w-2xl">
      <h1 className="mb-6 text-2xl font-bold">تعديل الفعالية</h1>
      <form onSubmit={handleSubmit} className="space-y-4 rounded-xl bg-white p-6 shadow-sm">
        {error && <p className="rounded-lg bg-red-50 p-3 text-sm text-red-600">{error}</p>}

        <div>
          <label className="mb-1 block text-sm font-medium">العنوان (إنجليزي)</label>
          <input name="title_en" defaultValue={event.title_en} required className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">العنوان (عربي)</label>
          <input name="title_ar" defaultValue={event.title_ar ?? ""} className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">الوصف (إنجليزي)</label>
          <textarea name="description_en" rows={4} defaultValue={event.description_en} required className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">الوصف (عربي)</label>
          <textarea name="description_ar" rows={4} defaultValue={event.description_ar ?? ""} className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
        </div>

        <div className="grid gap-4 md:grid-cols-2">
          <div>
            <label className="mb-1 block text-sm font-medium">تاريخ البداية</label>
            <input name="start_date" type="datetime-local" defaultValue={toLocal(event.start_date)} required className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium">تاريخ النهاية</label>
            <input name="end_date" type="datetime-local" defaultValue={toLocal(event.end_date)} required className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
          </div>
        </div>

        <div>
          <label className="mb-1 block text-sm font-medium">التصنيف</label>
          <select name="category" defaultValue={event.category} required className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary">
            {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
          </select>
        </div>

        <div>
          <label className="mb-1 block text-sm font-medium">الموقع</label>
          <select name="venue_id" defaultValue={event.venue_id ?? ""} className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary">
            <option value="">اختر موقع (اختياري)</option>
            {venues.map((v) => <option key={v.id} value={v.id}>{v.name_en} - {v.city}</option>)}
          </select>
        </div>

        <div>
          <label className="mb-1 block text-sm font-medium">رابط صورة الغلاف</label>
          <input name="cover_image" defaultValue={event.cover_image ?? ""} placeholder="https://..." className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
        </div>

        <label className="flex items-center gap-2 text-sm">
          <input name="is_free" type="checkbox" defaultChecked={event.is_free} className="h-4 w-4 rounded border-gray-300" />
          فعالية مجانية
        </label>

        <button type="submit" disabled={loading}
          className="w-full rounded-lg bg-primary py-2.5 font-medium text-white hover:bg-primary-dark disabled:opacity-50">
          {loading ? "جاري الحفظ..." : "حفظ التغييرات"}
        </button>
      </form>
    </div>
  );
}
