"use client";

import Link from "next/link";
import { Music, Trophy, Briefcase, Palette, UtensilsCrossed, Film, Baby, Moon } from "lucide-react";

const categories = [
  { key: "concerts", label: "حفلات", icon: Music },
  { key: "sports", label: "رياضة", icon: Trophy },
  { key: "business", label: "أعمال", icon: Briefcase },
  { key: "cultural", label: "ثقافة", icon: Palette },
  { key: "food", label: "طعام", icon: UtensilsCrossed },
  { key: "arts", label: "فن", icon: Film },
  { key: "family", label: "عائلي", icon: Baby },
  { key: "nightlife", label: "حياة ليلية", icon: Moon },
];

export function CategoryBar() {
  return (
    <div className="flex gap-4 overflow-x-auto pb-2">
      {categories.map((cat) => (
        <Link
          key={cat.key}
          href={`/search?category=${cat.key}`}
          className="flex shrink-0 flex-col items-center gap-1 rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md"
        >
          <cat.icon className="h-6 w-6 text-primary" />
          <span className="text-xs text-gray-600">{cat.label}</span>
        </Link>
      ))}
    </div>
  );
}
