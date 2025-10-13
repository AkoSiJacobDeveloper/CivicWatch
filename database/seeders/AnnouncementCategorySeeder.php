<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class AnnouncementCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'General Announcements',
            'Public Safety & Security',
            'Health Services',
            'Community Events',
            'Government & Administrative',
            'Utilities & Infrastructure',
            'Social Services',
            'Education & Youth',
            'Urgent Alerts',
            'Business & Livelihood',
            'Housing & Environment',
            'Election & Politics',
            'Senior Citizen Programs',
            'PWD Programs',
            'Sports Tournaments',
            'Training & Workshops'
        ];

        foreach ($categories as $category) {
            DB::table('announcement_categories')->updateOrInsert(
                ['name' => $category],
                []
            );
        }
    }
}
