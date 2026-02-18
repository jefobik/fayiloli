<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Folder;
use App\Models\Document;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Kept for potential future use

class DocumentSeeder extends Seeder
{
    public function run()
    {
        // Get folders and tags
        $folders = Folder::all();
        $tags = Tag::all();

        // Path to the directory containing document files
        $directory = public_path('documents');

        // Ensure directory exists
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Get all files from the directory
        $files = File::allFiles($directory);

        // Shuffle the file array
        shuffle($files);

        // Limit to 30 files
        $files = array_slice($files, 0, 30);

        if ($folders->isEmpty()) {
            $this->command->info('No folders found. Skipping document seeding.');
            return;
        }

        if ($tags->isEmpty()) {
            $this->command->info('No tags found. Skipping document seeding.');
            return;
        }

        // Iterate through each file and create a corresponding record in the database
        foreach ($files as $file) {
            // Get file information
            $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $extension = $file->getExtension();
            $relativePath = 'documents/' . $file->getRelativePathname();
            $size = $file->getSize();

            // Assign random folder and tags
            $folder = $folders->random();
            // Attach 1 to 3 random tags from the production-ready set
            $tagsToAttach = $tags->random(min(rand(1, 3), $tags->count()));

            $visibility = rand(0, 1) ? 'public' : 'private';
            $share = rand(0, 1);
            $download = rand(0, 1);
            $email = 'admin@nectarmetrics.com.ng';
            $url = 'https://nectarmetrics.com.ng';
            $contact = '+2348034567890';
            $owner = 'admin';

            $date = Carbon::now();
            $emojies = '😊😍🎉';

            // Ensure name is unique and not empty
            $i = 1;
            $originalName = $name;
            while (Document::where('name', $name)->exists() || empty($name)) {
                $name = $originalName . '_' . $i;
                $i++;
            }

            // Create document record in the database
            $document = Document::create([
                'name' => $name,
                'file_path' => $relativePath,
                'size' => $size,
                'extension' => $extension,
                'folder_id' => $folder->id,
                'visibility' => $visibility,
                'share' => $share,
                'download' => $download,
                'email' => $email,
                'url' => $url,
                'contact' => $contact,
                'owner' => $owner,
                'date' => $date,
                'emojies' => $emojies,
            ]);

            // Attach tags using the relationship
            $document->tags()->sync($tagsToAttach->pluck('id'));
        }
    }
}
