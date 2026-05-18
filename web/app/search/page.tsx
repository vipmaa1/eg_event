"use client";

import { useState, Suspense } from "react";
import { useSearchParams } from "next/navigation";
import { EventGrid } from "@/components/events/event-grid";
import { Search } from "lucide-react";

function SearchContent() {
  const params = useSearchParams();
  const [query, setQuery] = useState(params.get("search") || "");
  const category = params.get("category") || undefined;

  return (
    <div className="space-y-6">
      <form onSubmit={(e) => e.preventDefault()} className="relative">
        <Search className="absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
        <input
          type="text"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="ابحث عن فعاليات..."
          className="w-full rounded-xl border py-3 pr-10 outline-none focus:border-primary focus:ring-1 focus:ring-primary"
        />
      </form>
      <EventGrid search={query || undefined} category={category} />
    </div>
  );
}

export default function SearchPage() {
  return (
    <Suspense fallback={<div>جاري التحميل...</div>}>
      <SearchContent />
    </Suspense>
  );
}
