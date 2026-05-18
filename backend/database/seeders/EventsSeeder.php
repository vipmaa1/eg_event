<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventsSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = Organizer::first();
        if (!$organizer) {
            $this->command->warn('No organizer found. Run AdminUserSeeder first.');

            return;
        }

        $venues = $this->createVenues();
        $events = $this->createEvents($organizer, $venues);
        $this->createTicketTypes($events);
    }

    private function createVenues(): array
    {
        $venuesData = [
            [
                'name_en' => 'The GrEEK Campus',
                'name_ar' => 'حرم جريك',
                'city' => 'Cairo',
                'district' => 'Downtown',
                'address_en' => '151 El Tahrir St, Downtown',
                'capacity' => 800,
            ],
            [
                'name_en' => 'Bibliotheca Alexandrina',
                'name_ar' => 'مكتبة الإسكندرية',
                'city' => 'Alexandria',
                'district' => 'Shatby',
                'address_en' => 'El Shatby, Alexandria',
                'capacity' => 1500,
            ],
            [
                'name_en' => 'Hurghada Marina',
                'name_ar' => 'مارينا الغردقة',
                'city' => 'Hurghada',
                'district' => 'Marina',
                'address_en' => 'Marina Blvd, Hurghada',
                'capacity' => 2000,
            ],
            [
                'name_en' => 'Luxor Sound & Light Theater',
                'name_ar' => 'مسرح الصوت والضوء بالأقصر',
                'city' => 'Luxor',
                'district' => 'West Bank',
                'address_en' => 'Karnak Temple Road, West Bank',
                'capacity' => 1000,
            ],
            [
                'name_en' => 'Sharm El Sheikh International Congress Center',
                'name_ar' => 'مركز شرم الشيخ الدولي للمؤتمرات',
                'city' => 'Sharm El Sheikh',
                'district' => 'Naama Bay',
                'address_en' => 'Naama Bay, Sharm El Sheikh',
                'capacity' => 3000,
            ],
            [
                'name_en' => 'Al Azhar Park Conference Hall',
                'name_ar' => 'قاعة مؤتمرات حديقة الأزهر',
                'city' => 'Cairo',
                'district' => 'Darb al-Ahmar',
                'address_en' => 'Salah Salem St, Darb al-Ahmar',
                'capacity' => 500,
            ],
            [
                'name_en' => 'Zamalek Sporting Club',
                'name_ar' => 'نادي الزمالك الرياضي',
                'city' => 'Cairo',
                'district' => 'Zamalek',
                'address_en' => '26 July St, Zamalek',
                'capacity' => 5000,
            ],
            [
                'name_en' => 'Sahl Hasheesh Beach Resort',
                'name_ar' => 'منتجع سهل حشيش',
                'city' => 'Hurghada',
                'district' => 'Sahl Hasheesh',
                'address_en' => 'Sahl Hasheesh Bay, Hurghada',
                'capacity' => 1200,
            ],
        ];

        $venues = [];
        foreach ($venuesData as $v) {
            $slug = Str::slug($v['name_en']);
            $venues[] = Venue::create([
                'user_id' => $organizer->user_id ?? 4,
                'name_en' => $v['name_en'],
                'name_ar' => $v['name_ar'],
                'slug' => $slug,
                'city' => $v['city'],
                'district' => $v['district'],
                'address_en' => $v['address_en'],
                'capacity' => $v['capacity'],
                'verified' => true,
                'status' => 'active',
            ]);
        }

        return $venues;
    }

    private function createEvents(Organizer $organizer, array $venues): array
    {
        $now = now();

        $eventsData = [
            // 0 — Published: upcoming
            [
                'title_en' => 'Cairo International Film Festival',
                'title_ar' => 'مهرجان القاهرة السينمائي الدولي',
                'description_en' => 'The oldest and most prestigious film festival in the Arab world. Featuring screenings from over 50 countries.',
                'description_ar' => 'أقدم وأعرق مهرجان سينمائي في العالم العربي. يعرض أفلام من أكثر من 50 دولة.',
                'start_date' => $now->copy()->addDays(10),
                'end_date' => $now->copy()->addDays(17),
                'category' => 'arts',
                'status' => 'published',
                'venue' => $venues[0],
                'cover_id' => 1,
            ],
            // 1 — Published: upcoming
            [
                'title_en' => 'Alexandria Book Fair',
                'title_ar' => 'معرض الإسكندرية للكتاب',
                'description_en' => 'Annual book fair at Bibliotheca Alexandrina with publishers from across the Middle East.',
                'description_ar' => 'معرض الكتاب السنوي في مكتبة الإسكندرية بمشاركة دور نشر من جميع أنحاء الشرق الأوسط.',
                'start_date' => $now->copy()->addDays(20),
                'end_date' => $now->copy()->addDays(28),
                'category' => 'cultural',
                'status' => 'published',
                'venue' => $venues[1],
                'cover_id' => 2,
            ],
            // 2 — Published: upcoming
            [
                'title_en' => 'Red Sea Half Marathon',
                'title_ar' => 'نصف ماراثون البحر الأحمر',
                'description_en' => 'Scenic coastal run along the Hurghada Marina. Categories: 5K, 10K, and 21K.',
                'description_ar' => 'جري ساحلي خلاب على طول مارينا الغردقة. فئات: 5 كم، 10 كم، 21 كم.',
                'start_date' => $now->copy()->addDays(45),
                'end_date' => $now->copy()->addDays(45),
                'category' => 'sports',
                'status' => 'published',
                'venue' => $venues[2],
                'cover_id' => 3,
            ],
            // 3 — Published: upcoming
            [
                'title_en' => 'Sharm El Sheikh Food Festival',
                'title_ar' => 'مهرجان شرم الشيخ للطعام',
                'description_en' => 'A week-long culinary celebration with top Egyptian and international chefs. Cooking workshops and tasting sessions.',
                'description_ar' => 'احتفال طهوي لمدة أسبوع مع أمهر الطهاة المصريين والعالميين. ورش طبخ وجلسات تذوق.',
                'start_date' => $now->copy()->addDays(70),
                'end_date' => $now->copy()->addDays(77),
                'category' => 'food',
                'status' => 'published',
                'venue' => $venues[4],
                'cover_id' => 4,
            ],
            // 4 — Pending approval
            [
                'title_en' => 'Startup Pitch Night Cairo',
                'title_ar' => 'ليلة عرض الشركات الناشئة القاهرة',
                'description_en' => 'Ten startups pitch to a panel of venture capitalists. Network with investors and founders.',
                'description_ar' => 'عشرة شركات ناشئة تقدم عروضها للجنة من أصحاب رؤوس الأموال. تواصل مع المستثمرين والمؤسسين.',
                'start_date' => $now->copy()->addDays(50),
                'end_date' => $now->copy()->addDays(50),
                'category' => 'business',
                'status' => 'pending_approval',
                'venue' => $venues[5],
                'cover_id' => 5,
            ],
            // 5 — Pending approval
            [
                'title_en' => 'Luxor Classical Music Night',
                'title_ar' => 'ليلة الموسيقى الكلاسيكية في الأقصر',
                'description_en' => 'A magical evening of classical music performed against the backdrop of the ancient Karnak Temple.',
                'description_ar' => 'أمسية ساحرة من الموسيقى الكلاسيكية على خلفية معبد الكرنك القديم.',
                'start_date' => $now->copy()->addDays(80),
                'end_date' => $now->copy()->addDays(80),
                'category' => 'concerts',
                'status' => 'pending_approval',
                'venue' => $venues[3],
                'cover_id' => 6,
            ],
            // 6 — Draft
            [
                'title_en' => 'Kids Science Fair',
                'title_ar' => 'معرض العلوم للأطفال',
                'description_en' => 'Interactive science experiments and workshops for children aged 6-14.',
                'description_ar' => 'تجارب علمية تفاعلية وورش عمل للأطفال من 6 إلى 14 سنة.',
                'start_date' => $now->copy()->addDays(90),
                'end_date' => $now->copy()->addDays(92),
                'category' => 'family',
                'status' => 'draft',
                'venue' => $venues[5],
                'cover_id' => 7,
            ],
            // 7 — Draft
            [
                'title_en' => 'Sunset Yoga on the Beach',
                'title_ar' => 'يوغا الغروب على الشاطئ',
                'description_en' => 'Relaxing yoga sessions every evening at Sahl Hasheesh beach. All levels welcome.',
                'description_ar' => 'جلسات يوجا مريحة كل مساء على شاطئ سهل حشيش. جميع المستويات مرحب بها.',
                'start_date' => $now->copy()->addDays(100),
                'end_date' => $now->copy()->addDays(107),
                'category' => 'sports',
                'status' => 'draft',
                'venue' => $venues[7],
                'cover_id' => 8,
            ],
            // 8 — Completed (past event)
            [
                'title_en' => 'Cairo Fashion Week 2025',
                'title_ar' => 'أسبوع القاهرة للموضة 2025',
                'description_en' => 'Egypt\'s premier fashion event showcasing the latest collections from top designers.',
                'description_ar' => 'حدث الموضة الأول في مصر يعرض أحدث المجموعات من أهم المصممين.',
                'start_date' => $now->copy()->subDays(30),
                'end_date' => $now->copy()->subDays(27),
                'category' => 'arts',
                'status' => 'completed',
                'venue' => $venues[6],
                'cover_id' => 9,
            ],
            // 9 — Completed (past event)
            [
                'title_en' => 'Ramadan Nights Bazaar',
                'title_ar' => 'بازار ليالي رمضان',
                'description_en' => 'Traditional Ramadan bazaar with local artisans, food stalls, and cultural performances.',
                'description_ar' => 'بازار رمضاني تقليدي مع الحرفيين المحليين وأكشاك الطعام والعروض الثقافية.',
                'start_date' => $now->copy()->subDays(60),
                'end_date' => $now->copy()->subDays(55),
                'category' => 'cultural',
                'status' => 'completed',
                'venue' => $venues[5],
                'cover_id' => 10,
            ],
        ];

        $events = [];
        foreach ($eventsData as $i => $e) {
            $slug = Str::slug($e['title_en']);

            $event = Event::create([
                'organizer_id' => $organizer->id,
                'venue_id' => $e['venue']->id,
                'created_by_user_id' => $organizer->user_id,
                'title_en' => $e['title_en'],
                'title_ar' => $e['title_ar'],
                'slug' => $slug,
                'description_en' => $e['description_en'],
                'description_ar' => $e['description_ar'],
                'start_date' => $e['start_date'],
                'end_date' => $e['end_date'],
                'category' => $e['category'],
                'status' => $e['status'],
                'cover_image' => "https://picsum.photos/seed/{$slug}/800/400",
                'gallery' => [
                    "https://picsum.photos/seed/{$slug}-1/600/400",
                    "https://picsum.photos/seed/{$slug}-2/600/400",
                    "https://picsum.photos/seed/{$slug}-3/600/400",
                ],
                'has_tickets' => true,
                'is_free' => in_array($e['category'], ['sports', 'family']),
            ]);

            $events[] = $event;
        }

        return $events;
    }

    private function createTicketTypes(array $events): void
    {
        $ticketConfigs = [
            ['index' => 0, 'tiers' => [
                ['name_en' => 'Standard', 'name_ar' => 'قياسي', 'price' => 150.00, 'qty' => 500],
                ['name_en' => 'VIP', 'name_ar' => 'VIP', 'price' => 400.00, 'qty' => 100],
                ['name_en' => 'Opening Night Gala', 'name_ar' => 'حفل الليلة الافتتاحية', 'price' => 600.00, 'qty' => 50],
            ]],
            ['index' => 1, 'tiers' => [
                ['name_en' => 'General Entry', 'name_ar' => 'دخول عام', 'price' => 50.00, 'qty' => 2000],
                ['name_en' => 'Exclusive Pass', 'name_ar' => 'بطاقة حصرية', 'price' => 200.00, 'qty' => 200],
            ]],
            ['index' => 2, 'tiers' => [
                ['name_en' => '5K Run', 'name_ar' => '5 كم', 'price' => 0, 'qty' => 500],
                ['name_en' => '10K Run', 'name_ar' => '10 كم', 'price' => 0, 'qty' => 300],
                ['name_en' => '21K Half Marathon', 'name_ar' => '21 كم نصف ماراثون', 'price' => 0, 'qty' => 200],
            ]],
            ['index' => 3, 'tiers' => [
                ['name_en' => 'Day Pass', 'name_ar' => 'تذكرة يوم', 'price' => 200.00, 'qty' => 1000],
                ['name_en' => 'Full Week Pass', 'name_ar' => 'تذكرة الأسبوع كاملاً', 'price' => 600.00, 'qty' => 300],
                ['name_en' => 'Cooking Workshop', 'name_ar' => 'ورشة طبخ', 'price' => 350.00, 'qty' => 100],
            ]],
            ['index' => 4, 'tiers' => [
                ['name_en' => 'General Admission', 'name_ar' => 'دخول عام', 'price' => 100.00, 'qty' => 300],
                ['name_en' => 'Investor Pass', 'name_ar' => 'بطاقة مستثمر', 'price' => 500.00, 'qty' => 50],
            ]],
            ['index' => 5, 'tiers' => [
                ['name_en' => 'Standard Seating', 'name_ar' => 'مقعد قياسي', 'price' => 300.00, 'qty' => 400],
                ['name_en' => 'Premium Seating', 'name_ar' => 'مقعد ممتاز', 'price' => 600.00, 'qty' => 100],
            ]],
            ['index' => 6, 'tiers' => [
                ['name_en' => 'Child Ticket', 'name_ar' => 'تذكرة طفل', 'price' => 0, 'qty' => 200],
                ['name_en' => 'Adult Ticket', 'name_ar' => 'تذكرة بالغ', 'price' => 0, 'qty' => 100],
            ]],
            ['index' => 7, 'tiers' => [
                ['name_en' => 'Single Session', 'name_ar' => 'جلسة واحدة', 'price' => 0, 'qty' => 100],
                ['name_en' => 'Full Week Pass', 'name_ar' => 'تصريح الأسبوع', 'price' => 0, 'qty' => 50],
            ]],
        ];

        foreach ($ticketConfigs as $config) {
            $event = $events[$config['index']] ?? null;
            if (!$event) {
                continue;
            }

            foreach ($config['tiers'] as $tier) {
                TicketType::create([
                    'event_id' => $event->id,
                    'name_en' => $tier['name_en'],
                    'name_ar' => $tier['name_ar'],
                    'price' => $tier['price'],
                    'quantity_total' => $tier['qty'],
                    'sales_start' => now(),
                    'sales_end' => $event->start_date,
                ]);
            }
        }
    }
}
