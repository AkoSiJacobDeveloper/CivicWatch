<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Infrastructure', 'slug' => 'infrastructure', 'description' => 'Roads, buildings, and public facilities'],
            ['name' => 'Health Services', 'slug' => 'health-services', 'description' => 'Health programs and medical missions'],
            ['name' => 'Clean & Green', 'slug' => 'clean-green', 'description' => 'Environmental projects and clean-up drives'],
            ['name' => 'Education', 'slug' => 'education', 'description' => 'Educational programs and school projects'],
            ['name' => 'Livelihood', 'slug' => 'livelihood', 'description' => 'Livelihood and entrepreneurship programs'],
            ['name' => 'Peace & Order', 'slug' => 'peace-order', 'description' => 'Public safety and disaster preparedness'],
            ['name' => 'Social Services', 'slug' => 'social-services', 'description' => 'Social welfare and assistance programs'],
            ['name' => 'Sports & Recreation', 'slug' => 'sports-recreation', 'description' => 'Sports activities and recreational facilities'],
            ['name' => 'Youth & Development', 'slug' => 'youth-development', 'description' => 'Youth programs and activities'],
            ['name' => 'Senior Citizens', 'slug' => 'senior-citizens', 'description' => 'Programs for elderly citizens'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}