<?php

namespace Database\Seeders;

use App\Models\Venue;
use App\Models\Event;
use App\Models\SystemSetting;
use App\Models\TicketType;
use App\Models\Organizer;
use App\Services\SystemConfig;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        SystemConfig::set('site_name', 'Egypt Events', 'string', 'general');
        SystemConfig::set('date_format', 'Y-m-d', 'string', 'general');
        SystemConfig::set('auto_approve_events', '0', 'boolean', 'events');

        $organizer = Organizer::first();

        $venue = Venue::create([
            'user_id' => 4,
            'name_en' => 'Cairo Opera House',
            'name_ar' => 'دار الأوبرا المصرية',
            'slug' => 'cairo-opera-house',
            'description_en' => 'Premier performing arts venue in Cairo',
            'description_ar' => 'أهم صالة عرض فني في القاهرة',
            'city' => 'Cairo',
            'district' => 'Zamalek',
            'address_en' => 'Gezira Island, Zamalek',
            'capacity' => 1200,
            'verified' => true,
            'status' => 'active',
        ]);

        $event = Event::create([
            'organizer_id' => $organizer->id,
            'venue_id' => $venue->id,
            'created_by_user_id' => $organizer->user_id,
            'title_en' => 'Cairo Jazz Festival 2026',
            'title_ar' => 'مهرجان القاهرة للجاز 2026',
            'slug' => 'cairo-jazz-festival-2026',
            'description_en' => 'A three-day celebration of jazz music featuring international and local artists. Food, art, and music throughout the venue.',
            'description_ar' => 'احتفال لمدة ثلاثة أيام بموسيقى الجاز يضم فنانين دوليين ومحليين',
            'cover_image' => 'https://picsum.photos/seed/cairo-jazz-festival-2026/800/400',
            'gallery' => [
                'https://picsum.photos/seed/cairo-jazz-1/600/400',
                'https://picsum.photos/seed/cairo-jazz-2/600/400',
            ],
            'start_date' => now()->addDays(30),
            'end_date' => now()->addDays(33),
            'category' => 'concerts',
            'status' => 'published',
            'has_tickets' => true,
            'is_free' => false,
        ]);

        TicketType::create([
            'event_id' => $event->id,
            'name_en' => 'General Admission',
            'name_ar' => 'دخول عام',
            'price' => 250.00,
            'quantity_total' => 500,
            'sales_start' => now(),
            'sales_end' => $event->start_date,
        ]);

        TicketType::create([
            'event_id' => $event->id,
            'name_en' => 'VIP Pass',
            'name_ar' => 'بطاقة VIP',
            'description_en' => 'Includes front row seating and backstage access',
            'price' => 750.00,
            'quantity_total' => 100,
            'sales_start' => now(),
            'sales_end' => $event->start_date,
        ]);

        $event2 = Event::create([
            'organizer_id' => $organizer->id,
            'venue_id' => $venue->id,
            'created_by_user_id' => $organizer->user_id,
            'title_en' => 'Tech Summit Cairo',
            'title_ar' => 'قمة التكنولوجيا في القاهرة',
            'slug' => 'tech-summit-cairo-2026',
            'description_en' => 'The largest technology conference in Egypt with speakers from Google, Microsoft, and local startups.',
            'cover_image' => 'https://picsum.photos/seed/tech-summit-cairo-2026/800/400',
            'gallery' => [
                'https://picsum.photos/seed/tech-summit-1/600/400',
                'https://picsum.photos/seed/tech-summit-2/600/400',
            ],
            'start_date' => now()->addDays(60),
            'end_date' => now()->addDays(62),
            'category' => 'business',
            'status' => 'pending_approval',
            'has_tickets' => true,
            'is_free' => false,
        ]);

        TicketType::create([
            'event_id' => $event2->id,
            'name_en' => 'Standard',
            'price' => 500.00,
            'quantity_total' => 1000,
            'sales_start' => now(),
            'sales_end' => $event2->start_date,
        ]);

        TicketType::create([
            'event_id' => $event2->id,
            'name_en' => 'Student',
            'price' => 200.00,
            'quantity_total' => 200,
            'sales_start' => now(),
            'sales_end' => $event2->start_date,
        ]);
    }
}
