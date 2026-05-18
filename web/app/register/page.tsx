"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { useAuth } from "@/components/layout/auth-provider";
import { Ticket } from "lucide-react";

export default function RegisterPage() {
  const { register } = useAuth();
  const router = useRouter();
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [role, setRole] = useState("attendee");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    setLoading(true);
    try {
      await register(name, email, password, role);
      router.push("/");
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="mx-auto max-w-md pt-8">
      <div className="rounded-2xl bg-white p-8 shadow-sm">
        <div className="mb-6 text-center">
          <Ticket className="mx-auto h-10 w-10 text-primary" />
          <h1 className="mt-2 text-2xl font-bold">إنشاء حساب</h1>
          <p className="text-gray-500">انضم إلى EventHub الآن</p>
        </div>
        <form onSubmit={handleSubmit} className="space-y-4">
          {error && <p className="rounded-lg bg-red-50 p-3 text-sm text-red-600">{error}</p>}
          <div>
            <label className="mb-1 block text-sm font-medium">الاسم الكامل</label>
            <input type="text" value={name} onChange={(e) => setName(e.target.value)} required
              className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary focus:ring-1 focus:ring-primary" />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium">البريد الإلكتروني</label>
            <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required
              className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary focus:ring-1 focus:ring-primary" />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium">كلمة المرور</label>
            <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required minLength={8}
              className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary focus:ring-1 focus:ring-primary" />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium">نوع الحساب</label>
            <select value={role} onChange={(e) => setRole(e.target.value)}
              className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary focus:ring-1 focus:ring-primary">
              <option value="attendee">حاضر</option>
              <option value="organizer">منظم</option>
              <option value="venue_owner">صاحب مكان</option>
            </select>
          </div>
          <button type="submit" disabled={loading}
            className="w-full rounded-lg bg-primary py-2.5 font-medium text-white hover:bg-primary-dark disabled:opacity-50">
            {loading ? "جاري التحميل..." : "إنشاء حساب"}
          </button>
        </form>
        <p className="mt-4 text-center text-sm text-gray-500">
          لديك حساب؟ <Link href="/login" className="text-primary hover:underline">تسجيل الدخول</Link>
        </p>
      </div>
    </div>
  );
}
