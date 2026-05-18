<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organizer;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin EventHub',
            'email' => 'admin@eventhub.eg',
            'password' => 'Admin@12345',
            'phone' => '01000000000',
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        $organizer = User::create([
            'name' => 'Ahmed Organizer',
            'email' => 'organizer@eventhub.eg',
            'password' => 'Org@12345',
            'phone' => '01011111111',
            'status' => 'active',
        ]);
        $organizer->assignRole('organizer');

        Organizer::create([
            'user_id' => $organizer->id,
            'name_en' => 'Ahmed Events Co.',
            'name_ar' => 'شركة أحمد للفعاليات',
            'slug' => 'ahmed-events-co',
            'bio_en' => 'Professional event organizer based in Cairo',
            'bio_ar' => 'منظم فعاليات محترف في القاهرة',
            'email' => 'ahmed@events.com',
            'phone' => '01011111111',
            'verified' => true,
            'verification_date' => now(),
            'status' => 'active',
        ]);

        $attendee = User::create([
            'name' => 'Mona Attendee',
            'email' => 'attendee@eventhub.eg',
            'password' => 'Att@12345',
            'phone' => '01022222222',
            'status' => 'active',
        ]);
        $attendee->assignRole('attendee');

        $venueOwner = User::create([
            'name' => 'Cairo Venue',
            'email' => 'venue@eventhub.eg',
            'password' => 'Ven@12345',
            'phone' => '01033333333',
            'status' => 'active',
        ]);
        $venueOwner->assignRole('venue_owner');
    }
}
