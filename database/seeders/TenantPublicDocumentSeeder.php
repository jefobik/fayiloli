<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Folder;
use App\Models\Document;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Seeds all documents from public/documents into the tenant database,
 * making them publicly visible. For local development only.
 */
class TenantPublicDocumentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException(
                'TenantPublicDocumentSeeder must not run in production. '
                . 'Tenant workspaces are populated by users through the EDMS UI.'
            );
        }

        $folders = Folder::all();
        $tags = Tag::all();

        $directory = public_path('documents');

        if (!File::exists($directory)) {
            $this->command?->info("Directory {$directory} does not exist. Skipping public document seeding.");
            return;
        }

        $files = File::allFiles($directory);

        if ($folders->isEmpty()) {
            $this->command?->info('No folders found. Skipping public document seeding.');
            return;
        }

        if ($tags->isEmpty()) {
            $this->command?->info('No tags found. Skipping public document seeding.');
            return;
        }

        $this->command?->info("Seeding " . count($files) . " public documents...");

        foreach ($files as $file) {
            $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $extension = $file->getExtension();
            $relativePath = 'documents/' . $file->getRelativePathname();
            $size = $file->getSize();

            $folder = $folders->random();
            $tagsToAttach = $tags->random(min(rand(1, 3), $tags->count()));

            $visibility = 'public';
            $share = rand(0, 1);
            $download = rand(0, 1);
            // Use actual tenant details
            $tenant = tenant();
            $email = $tenant?->admin_email ?? 'admin@fcta.gov.local';
            $url = $tenant ? 'https://' . $tenant->domains()->first()?->domain : 'https://fcta.gov.local';
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

            $document->tags()->sync($tagsToAttach->pluck('id'));
        }

        $this->command?->info("Finished seeding public documents.");
    }
}
