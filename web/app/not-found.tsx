import Link from "next/link";

export default function NotFound() {
  return (
    <div className="flex flex-col items-center justify-center py-24">
      <h1 className="text-6xl font-bold text-gray-300">404</h1>
      <p className="mt-4 text-lg text-gray-500">الصفحة غير موجودة</p>
      <Link href="/" className="mt-6 text-primary hover:underline">العودة للرئيسية</Link>
    </div>
  );
}
