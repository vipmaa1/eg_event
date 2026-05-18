"use client";

import { useState, useEffect } from "react";
import { useParams, useRouter } from "next/navigation";
import { api } from "@/lib/api";
import type { Event } from "@/lib/types";
import { useAuth } from "@/components/layout/auth-provider";
import Link from "next/link";

export default function CheckoutPage() {
  const { id } = useParams<{ id: string }>();
  const { user } = useAuth();
  const router = useRouter();
  const [event, setEvent] = useState<Event | null>(null);
  const [quantity, setQuantity] = useState(1);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    api.get<Event>(`/events/${id}`).then(setEvent).catch(() => {}).finally(() => setLoading(false));
  }, [id]);

  if (!user) return <div className="py-16 text-center"><p>يجب تسجيل الدخول أولاً</p><Link href="/login" className="text-primary">تسجيل الدخول</Link></div>;
  if (loading) return <div className="h-64 animate-pulse rounded-2xl bg-gray-200" />;
  if (!event) return <p>غير موجود</p>;

  const ticketType = event.ticket_types?.[0];
  const unitPrice = ticketType?.price || 0;
  const subtotal = unitPrice * quantity;
  const fees = subtotal * 0.03;
  const total = subtotal + fees;

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    setSubmitting(true);
    try {
      const form = e.target as HTMLFormElement;
      const data = new FormData(form);
      await api.post("/tickets/purchase", {
        event_id: Number(id),
        ticket_type_id: ticketType!.id,
        quantity,
        customer_name: data.get("name"),
        customer_email: data.get("email"),
        customer_phone: data.get("phone"),
      });
      router.push("/profile/tickets");
    } catch (err: any) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="mx-auto max-w-2xl">
      <h1 className="mb-6 text-2xl font-bold">إتمام الحجز</h1>
      <div className="space-y-4">
        <div className="rounded-xl bg-white p-4 shadow-sm">
          <p className="font-bold">{event.title_ar || event.title_en}</p>
          <p className="text-sm text-gray-500">{unitPrice} EGP × {quantity}</p>
        </div>

        <div className="flex items-center gap-2">
          <span>العدد:</span>
          <button onClick={() => setQuantity(Math.max(1, quantity - 1))} className="rounded-lg border px-3 py-1">-</button>
          <span className="w-8 text-center font-bold">{quantity}</span>
          <button onClick={() => setQuantity(Math.min(10, quantity + 1))} className="rounded-lg border px-3 py-1">+</button>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4 rounded-xl bg-white p-6 shadow-sm">
          <input name="name" defaultValue={user.name} required placeholder="الاسم الكامل" className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
          <input name="email" type="email" defaultValue={user.email} required placeholder="البريد الإلكتروني" className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
          <input name="phone" defaultValue={user.phone || ""} placeholder="رقم الهاتف" className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />
          <input name="promo_code" placeholder="كود الخصم (اختياري)" className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary" />

          <div className="border-t pt-4">
            {[
              ["سعر التذكرة", `${subtotal.toFixed(2)} EGP`],
              ["رسوم الخدمة", `${fees.toFixed(2)} EGP`],
              ["المجموع", `${total.toFixed(2)} EGP`],
            ].map(([label, val], i) => (
              <div key={label} className={`flex justify-between ${i === 2 ? "mt-2 pt-2 font-bold" : ""}`}>
                <span>{label}</span><span>{val}</span>
              </div>
            ))}
          </div>

          {error && <p className="text-sm text-red-600">{error}</p>}
          <button type="submit" disabled={submitting}
            className="w-full rounded-xl bg-primary py-3 font-medium text-white hover:bg-primary-dark disabled:opacity-50">
            {submitting ? "جاري الدفع..." : `تأكيد الحجز - ${total.toFixed(2)} EGP`}
          </button>
        </form>
      </div>
    </div>
  );
}
