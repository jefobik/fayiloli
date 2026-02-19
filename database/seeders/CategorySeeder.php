<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Only models using the Searchable trait (Tag, Document, Folder) have
        // withoutSyncingToSearch(). Category does not. We disable sync only on
        // Tag here; rebuild indexes afterwards with `php artisan scout:import`.
        // Seeders must never fail because MeiliSearch is unavailable.
        Tag::withoutSyncingToSearch(fn () => $this->seed());
    }

    private function seed(): void
    {
        $categoriesData = [
            ['name' => 'Birth Records'],
            ['name' => 'Death Records'],
            ['name' => 'Marriage Certificates'],
            ['name' => 'Land/Property Titles'],
            ['name' => 'Business Registrations'],
            ['name' => 'Court Judgments'],
            ['name' => 'Legislative Documents'],
            ['name' => 'Tax Records'],
            ['name' => 'Licenses & Permits'],
            ['name' => 'Immigration/Passport Records'],
            ['name' => 'Vehicle Registrations'],
            ['name' => 'Procurement & Contracts'],
            ['name' => 'Public Notices'],
            ['name' => 'Health Records'],
            ['name' => 'Education Certificates'],
            ['name' => 'Environmental Reports'],
            ['name' => 'Financial Disclosures'],
            ['name' => 'Census Data'],
            ['name' => 'Government Budgets'],
            ['name' => 'Policy Documents'],
            ['name' => 'Meeting Minutes'],
            ['name' => 'Press Releases'],
            ['name' => 'Audits & Investigations'],
            ['name' => 'Public Works Projects'],
            ['name' => 'Social Services Records'],
            ['name' => 'Cultural Heritage Records'],
            ['name' => 'Emergency Management Plans'],
            ['name' => 'Public Safety Reports'],
            ['name' => 'Transportation Records'],
            ['name' => 'Energy & Utilities Records'],
            ['name' => 'Technology & Innovation Records'],
            ['name' => 'International Relations Records'],
            ['name' => 'Public Feedback & Complaints'],
            ['name' => 'Historical Archives'],
            ['name' => 'Other Public Records'],
            ['name' => 'Miscellaneous Records'],
            ['name' => 'General Records'],
            ['name' => 'Uncategorized Records'],
            ['name' => 'Administrative Records'],
            ['name' => 'Legal Records'],
            ['name' => 'Financial Records'],
            ['name' => 'Operational Records'],
            ['name' => 'Strategic Records'],
            ['name' => 'Compliance Records'],
            ['name' => 'Audit Records'],
            ['name' => 'Policy & Procedure Records'],
            ['name' => 'Project Management Records'],
            ['name' => 'Human Resources Records'],
            ['name' => 'Information Technology Records'],
            ['name' => 'Customer Service Records'],
            ['name' => 'Marketing & Communications Records'],
            ['name' => 'Research & Development Records'],
            ['name' => 'Sales & Revenue Records'],
            ['name' => 'Supply Chain & Logistics Records'],
            ['name' => 'Risk Management Records'],
            ['name' => 'Corporate Governance Records'],
            ['name' => 'Sustainability & Environmental Records'],
            ['name' => 'Community Engagement Records'],
            ['name' => 'Diversity & Inclusion Records'],
            ['name' => 'Innovation & Technology Records'],
            ['name' => 'Global Operations Records'],
            ['name' => 'Crisis Management Records'],
            ['name' => 'Ethics & Compliance Records'],
            ['name' => 'Investor Relations Records'],
            ['name' => 'Public Relations Records'],
            ['name' => 'Corporate Social Responsibility Records'],
            ['name' => 'Other Corporate Records'],
            ['name' => 'Miscellaneous Corporate Records'],
            ['name' => 'General Corporate Records'],
            ['name' => 'Uncategorized Corporate Records'],
            ['name' => 'Administrative Corporate Records'],
            ['name' => 'Legal Corporate Records'],
            ['name' => 'Financial Corporate Records'],
            ['name' => 'Operational Corporate Records'],
            ['name' => 'Strategic Corporate Records'],
            ['name' => 'Compliance Corporate Records'],
            ['name' => 'Audit Corporate Records'],
            ['name' => 'Policy & Procedure Corporate Records'],
            ['name' => 'Project Management Corporate Records'],
            ['name' => 'Human Resources Corporate Records'],
            ['name' => 'Information Technology Corporate Records'],
            ['name' => 'Customer Service Corporate Records'],
            ['name' => 'Marketing & Communications Corporate Records'],
            ['name' => 'Research & Development Corporate Records'],
            ['name' => 'Sales & Revenue Corporate Records'],
            ['name' => 'Supply Chain & Logistics Corporate Records'],
            ['name' => 'Risk Management Corporate Records'],
            ['name' => 'Corporate Governance Corporate Records'],
            ['name' => 'Sustainability & Environmental Corporate Records'],
            ['name' => 'Community Engagement Corporate Records'],
            ['name' => 'Diversity & Inclusion Corporate Records'],
            ['name' => 'Innovation & Technology Corporate Records'],
            ['name' => 'Global Operations Corporate Records'],
            ['name' => 'Crisis Management Corporate Records'],
            ['name' => 'Ethics & Compliance Corporate Records'],
            ['name' => 'Investor Relations Corporate Records'],
            ['name' => 'Public Relations Corporate Records'],
            ['name' => 'Corporate Social Responsibility Corporate Records'],
        ];

        DB::transaction(function () use ($categoriesData) {
            $usedCodes = [];

            foreach ($categoriesData as $categoryData) {
                $name = $categoryData['name'];
                $slug = \Illuminate\Support\Str::slug($name);

                // Skip if this category already exists (idempotent re-runs).
                $existing = Category::where('slug', $slug)->first();
                if ($existing) {
                    // Ensure its tags also exist before moving on.
                    $this->seedTagsForCategory($existing);
                    continue;
                }

                $base        = $this->buildCode($name, 10);
                $code        = $this->uniquifyCode($base, $usedCodes, 10, 'categories', 'code');
                $usedCodes[] = $code;

                $category = Category::create([
                    'name' => $name,
                    'code' => $code,
                ]);

                $this->seedTagsForCategory($category);
            }
        });
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function seedTagsForCategory(Category $category): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $tagName = "{$category->name} - Tag $i";
            Tag::firstOrCreate(
                ['name' => $tagName, 'category_id' => $category->id],
            );
        }
    }

    private function buildCode(string $name, int $maxLen): string
    {
        $words  = preg_split('/[\s\-&\/]+/', strtoupper($name), -1, PREG_SPLIT_NO_EMPTY);
        $first  = isset($words[0]) ? substr($words[0], 0, 3) : '';
        $second = isset($words[1]) ? substr($words[1], 0, 3) : '';
        $base   = $second !== '' ? "{$first}_{$second}" : $first;

        return substr($base, 0, $maxLen);
    }

    private function uniquifyCode(
        string $base,
        array  $usedCodes,
        int    $maxLen,
        string $table,
        string $column
    ): string {
        $code   = $base;
        $suffix = 0;

        while (in_array($code, $usedCodes) || DB::table($table)->where($column, $code)->exists()) {
            $suffix++;
            $sfx  = (string) $suffix;
            $code = substr($base, 0, $maxLen - strlen($sfx)) . $sfx;
        }

        return substr($code, 0, $maxLen);
    }
}
