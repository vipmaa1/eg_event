"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { useAuth } from "@/components/layout/auth-provider";
import { Ticket } from "lucide-react";

export default function LoginPage() {
  const { login } = useAuth();
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    setLoading(true);
    try {
      await login(email, password);
      router.push("/");
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="mx-auto max-w-md pt-12">
      <div className="rounded-2xl bg-white p-8 shadow-sm">
        <div className="mb-6 text-center">
          <Ticket className="mx-auto h-10 w-10 text-primary" />
          <h1 className="mt-2 text-2xl font-bold">مرحباً بعودتك</h1>
          <p className="text-gray-500">سجل دخولك لاكتشاف الفعاليات</p>
        </div>
        <form onSubmit={handleSubmit} className="space-y-4">
          {error && <p className="rounded-lg bg-red-50 p-3 text-sm text-red-600">{error}</p>}
          <div>
            <label className="mb-1 block text-sm font-medium">البريد الإلكتروني</label>
            <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required
              className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary focus:ring-1 focus:ring-primary" />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium">كلمة المرور</label>
            <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required
              className="w-full rounded-lg border px-4 py-2.5 outline-none focus:border-primary focus:ring-1 focus:ring-primary" />
          </div>
          <button type="submit" disabled={loading}
            className="w-full rounded-lg bg-primary py-2.5 font-medium text-white hover:bg-primary-dark disabled:opacity-50">
            {loading ? "جاري التحميل..." : "تسجيل الدخول"}
          </button>
        </form>
        <p className="mt-4 text-center text-sm text-gray-500">
          ليس لديك حساب؟ <Link href="/register" className="text-primary hover:underline">سجل الآن</Link>
        </p>
      </div>
    </div>
  );
}
