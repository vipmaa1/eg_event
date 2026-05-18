"use client";

import Link from "next/link";
import { MapPin, Eye } from "lucide-react";
import type { Event } from "@/lib/types";

export function EventCard({ event }: { event: Event }) {
  return (
    <Link href={`/events/${event.id}`} className="group overflow-hidden rounded-xl bg-white shadow-sm transition hover:shadow-md">
      <div className="relative aspect-video bg-gray-100">
        {event.cover_image ? (
          <img src={event.cover_image} alt={event.title_en} className="h-full w-full object-cover" />
        ) : (
          <div className="flex h-full items-center justify-center text-gray-400">EventHub</div>
        )}
        <span className="absolute right-2 top-2 rounded bg-black/60 px-2 py-1 text-xs text-white">
          {new Date(event.start_date).toLocaleDateString("ar-EG", { day: "numeric", month: "short" })}
        </span>
      </div>
      <div className="p-4">
        <h3 className="font-bold group-hover:text-primary">{event.title_ar || event.title_en}</h3>
        <div className="mt-1 flex items-center gap-1 text-sm text-gray-500">
          <MapPin className="h-3 w-3" /> {event.venue?.city || "غير محدد"}
        </div>
        <div className="mt-2 flex items-center justify-between">
          {event.min_price != null ? (
            <span className="font-bold text-primary">{event.min_price} EGP</span>
          ) : event.is_free ? (
            <span className="font-bold text-green-600">مجاني</span>
          ) : null}
          <span className="flex items-center gap-1 text-xs text-gray-400"><Eye className="h-3 w-3" />{event.view_count}</span>
        </div>
      </div>
    </Link>
  );
}
