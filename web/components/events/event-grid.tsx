"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import type { Event } from "@/lib/types";
import { EventCard } from "./event-card";

export function EventGrid({ upcoming, category, search, limit }: { upcoming?: boolean; category?: string; search?: string; limit?: number }) {
  const [events, setEvents] = useState<Event[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const params = new URLSearchParams();
    if (upcoming) params.set("upcoming", "true");
    if (category) params.set("category", category);
    if (search) params.set("search", search);
    api.get<{ data: Event[] }>(`/events?${params}`).then((res) => {
      setEvents(limit ? res.data.slice(0, limit) : res.data);
    }).catch(() => {}).finally(() => setLoading(false));
  }, [upcoming, category, search, limit]);

  if (loading) return <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">{Array.from({ length: 6 }).map((_, i) => <div key={i} className="h-64 animate-pulse rounded-xl bg-gray-200" />)}</div>;
  if (events.length === 0) return <p className="py-8 text-center text-gray-500">لا توجد فعاليات</p>;

  return (
    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      {events.map((e) => <EventCard key={e.id} event={e} />)}
    </div>
  );
}
