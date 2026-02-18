<?php

namespace Database\Seeders;


use App\Models\Tag;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        $tags = [
            'Secretariat', 'Department', 'Agency', 'Division', 'Unit', 'Confidential', 'Public', 'Restricted', 'Urgent', 'Archived',
            'For Review', 'Approved', 'Pending', 'Rejected', 'Internal Memo', 'Circular', 'Policy', 'Report', 'Correspondence', 'Legal',
            'Financial', 'Procurement', 'Human Resources', 'IT', 'Security', 'Health', 'Education', 'Infrastructure', 'Environment',
            'Project', 'Meeting', 'Contract', 'License', 'Permit', 'Application', 'Complaint', 'Feedback', 'Audit', 'Budget',
            'Plan', 'Strategy', 'Regulation', 'Notice', 'Announcement', 'Press Release', 'Record', 'Registry', 'Archive', 'Digital', 'Physical'
        ];

        DB::transaction(function () use ($tags, $categories) {
            $usedCodes = [];
            foreach ($tags as $tagName) {
                // Smart code: first 3 uppercase letters with underscores between (e.g., I_N_T), append number if needed, max 6 chars
                // Code: first 3 uppercase letters, each followed by an underscore (e.g., I_N_), append number if needed, max 6 chars
                $letters = array_slice(
                    array_filter(str_split(strtoupper(preg_replace('/[^A-Z]/', '', $tagName)))),
                    0, 3
                );
                $base = '';
                foreach ($letters as $l) {
                    $base .= $l . '_';
                }
                $base = substr($base, 0, 5); // e.g. I_N_ or P_U_
                    // EDMS 3-3 best practice: first 3 letters of first word, underscore, first 3 of second word (e.g., INT_MEM), or first 6 of one word
                    $words = preg_split('/[\s-]+/', strtoupper($tagName));
                    $first = isset($words[0]) ? substr($words[0], 0, 3) : '';
                    $second = isset($words[1]) ? substr($words[1], 0, 3) : '';
                    if ($second !== '') {
                        $base = $first . '_' . $second;
                    } else {
                        $base = substr($first, 0, 6);
                    }
                    $base = substr($base, 0, 7); // ensure max 7 chars
                    $uniqueCode = $base;
                    $suffix = 0;
                    // Ensure code is unique and <= 10 chars
                    while (in_array($uniqueCode, $usedCodes) || Tag::where('code', $uniqueCode)->exists()) {
                        $suffix++;
                        $uniqueCode = substr($base, 0, 10 - strlen((string)$suffix)) . $suffix;
                    }
                    $uniqueCode = substr($uniqueCode, 0, 10);
                    $usedCodes[] = $uniqueCode;

                // Generate random background color (pastel for readability)
                $h = rand(0, 360);
                $s = rand(30, 60);
                $l = rand(70, 90);
                $background_color = "hsl($h, $s%, $l%)";

                // Choose black or white for best contrast
                $foreground_color = ($l > 80) ? '#222222' : '#ffffff';

                $tag = Tag::firstOrCreate([
                    'name' => $tagName,
                ], [
                    'code' => $uniqueCode,
                    'background_color' => $background_color,
                    'foreground_color' => $foreground_color,
                ]);

                // Attach tag to all categories (or customize as needed)
                foreach ($categories as $category) {
                    $tag->category_id = $category->id;
                    $tag->save();
                    // Or use pivot if many-to-many: $category->tags()->syncWithoutDetaching([$tag->id]);
                }
            }
        });
    }
}
