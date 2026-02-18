<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use App\Models\Folder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $folderNames = [
            "Minister's Registry",
            'PS CSS Registry',
            'HOS Registry',
            'Budget & Treasury Registry',
        ];

        $allCategories = Category::all();
        $allTags = Tag::all();

        foreach ($folderNames as $folderName) {
            $folder = Folder::create([
                'name' => $folderName,
                'parent_id' => null,
                'visibility' => 'public',
                'background_color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
                'foreground_color' => '#ffffff',
            ]);

            // Pick 4 random categories for this folder
            $categories = $allCategories->random(min(4, $allCategories->count()));
            foreach ($categories as $category) {
                $folder->categories()->attach($category);

                // Pick 4 random tags for this category
                $tags = $allTags->random(min(4, $allTags->count()));
                foreach ($tags as $tag) {
                    $category->tags()->syncWithoutDetaching([$tag->id]);
                }
            }
        }
    }
}
