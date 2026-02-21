<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix sessions.user_id: was bigint (foreignId), must be uuid to match users.id
        DB::statement('TRUNCATE TABLE sessions');
        DB::statement('ALTER TABLE sessions DROP COLUMN user_id');
        DB::statement('ALTER TABLE sessions ADD COLUMN user_id uuid NULL');
        DB::statement('CREATE INDEX sessions_user_id_index ON sessions (user_id)');

        // Fix tags.category_id: was bigint (foreignId), must be uuid to match categories.id
        DB::statement('ALTER TABLE tags DROP CONSTRAINT IF EXISTS tags_category_id_foreign');
        DB::statement('ALTER TABLE tags DROP COLUMN category_id');
        DB::statement('ALTER TABLE tags ADD COLUMN category_id uuid NULL');
        DB::statement('ALTER TABLE tags ADD CONSTRAINT tags_category_id_foreign FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE tags DROP CONSTRAINT IF EXISTS tags_category_id_foreign');
        DB::statement('ALTER TABLE tags DROP COLUMN category_id');
        DB::statement('ALTER TABLE tags ADD COLUMN category_id bigint NULL');

        DB::statement('TRUNCATE TABLE sessions');
        DB::statement('ALTER TABLE sessions DROP COLUMN user_id');
        DB::statement('ALTER TABLE sessions ADD COLUMN user_id bigint NULL');
        DB::statement('CREATE INDEX sessions_user_id_index ON sessions (user_id)');
    }
};
