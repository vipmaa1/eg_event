import { EventGrid } from "@/components/events/event-grid";
import { CategoryBar } from "@/components/events/category-bar";

export default function HomePage() {
  return (
    <div className="space-y-8">
      <section className="rounded-2xl bg-gradient-to-br from-primary to-primary-dark p-8 text-white md:p-12">
        <h1 className="text-3xl font-bold md:text-4xl">اكتشف الفعاليات في مدينتك</h1>
        <p className="mt-2 text-lg text-white/80">
          تصفح آلاف الفعاليات - حفلات، مؤتمرات، رياضة، فن وأكثر
        </p>
      </section>

      <CategoryBar />

      <section>
        <div className="mb-4 flex items-center justify-between">
          <h2 className="text-xl font-bold">الفعاليات القادمة</h2>
        </div>
        <EventGrid upcoming />
      </section>
    </div>
  );
}
