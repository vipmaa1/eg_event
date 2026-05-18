"use client";

import { useEffect, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { api } from "@/lib/api";
import type { Event } from "@/lib/types";
import { MapPin, Calendar, Clock, Eye, User } from "lucide-react";

export default function EventDetailPage() {
  const { id } = useParams<{ id: string }>();
  const router = useRouter();
  const [event, setEvent] = useState<Event | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get<Event>(`/events/${id}`).then(setEvent).catch(() => {}).finally(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="h-96 animate-pulse rounded-2xl bg-gray-200" />;
  if (!event) return <p className="text-center text-gray-500">الفعالية غير موجودة</p>;

  const minPrice = event.ticket_types?.length ? Math.min(...event.ticket_types.map((t) => t.price)) : null;

  return (
    <div className="mx-auto max-w-3xl">
      <div className="relative aspect-video overflow-hidden rounded-2xl bg-gray-100">
        {event.cover_image && <img src={event.cover_image} alt={event.title_en} className="h-full w-full object-cover" />}
        <span className="absolute right-3 top-3 rounded bg-black/60 px-3 py-1 text-sm text-white">{event.category}</span>
      </div>

      <div className="mt-6">
        <h1 className="text-2xl font-bold md:text-3xl">{event.title_ar || event.title_en}</h1>
        <p className="mt-1 text-gray-500">{event.title_en !== event.title_ar ? event.title_en : ""}</p>

        <div className="mt-4 flex flex-wrap gap-4 text-sm text-gray-600">
          <span className="flex items-center gap-1"><Calendar className="h-4 w-4" />{new Date(event.start_date).toLocaleDateString("ar-EG", { weekday: "long", year: "numeric", month: "long", day: "numeric" })}</span>
          <span className="flex items-center gap-1"><Clock className="h-4 w-4" />{new Date(event.start_date).toLocaleTimeString("ar-EG", { hour: "2-digit", minute: "2-digit" })}</span>
          {event.venue && <span className="flex items-center gap-1"><MapPin className="h-4 w-4" />{event.venue.name_ar || event.venue.name_en}، {event.venue.city}</span>}
          {event.organizer && <span className="flex items-center gap-1"><User className="h-4 w-4" />{event.organizer.name_ar || event.organizer.name_en}</span>}
          <span className="flex items-center gap-1"><Eye className="h-4 w-4" />{event.view_count}</span>
        </div>

        <div className="mt-6">
          <h2 className="text-lg font-bold">عن الفعالية</h2>
          <p className="mt-2 leading-relaxed text-gray-700">{event.description_ar || event.description_en}</p>
        </div>

        {event.ticket_types && event.ticket_types.length > 0 && (
          <div className="mt-6">
            <h2 className="text-lg font-bold">التذاكر</h2>
            <div className="mt-3 space-y-3">
              {event.ticket_types.map((t) => (
                <div key={t.id} className="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm">
                  <div>
                    <p className="font-medium">{t.name_ar || t.name_en}</p>
                    {t.description_en && <p className="text-sm text-gray-500">{t.description_ar || t.description_en}</p>}
                    <p className="text-xs text-gray-400">متبقي: {t.quantity_total - t.quantity_sold}</p>
                  </div>
                  <div className="text-left">
                    <p className="text-lg font-bold text-primary">{t.price} {t.currency}</p>
                  </div>
                </div>
              ))}
            </div>
            <button
              onClick={() => router.push(`/checkout/${event.id}`)}
              className="mt-4 w-full rounded-xl bg-primary py-3 font-medium text-white hover:bg-primary-dark"
            >
              احجز الآن {minPrice ? `- EGP ${minPrice}` : ""}
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
