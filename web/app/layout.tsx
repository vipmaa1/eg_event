import type { Metadata } from "next";
import "./globals.css";
import { AuthProvider } from "@/components/layout/auth-provider";
import { Header } from "@/components/layout/header";

export const metadata: Metadata = {
  title: "EventHub - اكتشف الفعاليات",
  description: "منصة اكتشاف وحجز تذاكر الفعاليات في مصر",
};

export default function RootLayout({
  children,
}: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="ar" dir="rtl">
      <body className="min-h-screen">
        <AuthProvider>
          <Header />
          <main className="mx-auto max-w-7xl px-4 py-6">{children}</main>
        </AuthProvider>
      </body>
    </html>
  );
}
