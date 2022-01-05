<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('books')->count() > 0) {
            return;
        }

        foreach ($this->records() as $record) {
            $categoryIds = Arr::pull($record, 'category_ids');

            $bookId = DB::table('books')->insertGetId(array_merge([
                'isbn' => uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ], $record));

            foreach ($categoryIds as $categoryId) {
                DB::table('book_has_category')->insert([
                    'book_id' => $bookId,
                    'category_id' => $categoryId,
                ]);
            }
        }
    }

    protected function records(): array
    {
        return [
            [
                'title' => 'Space Expansion',
                'description' => 'Space Expansion',
                'author' => 'James Cory',
                'category_ids' => [1],
            ],
            [
                'title' => 'Young Captain',
                'description' => 'Young Captain',
                'author' => 'John Vern',
                'category_ids' => [2],
            ],
            [
                'title' => 'Learning Physics',
                'description' => 'Learning Physics',
                'author' => 'Nick Tyson',
                'category_ids' => [3],
            ],
            [
                'title' => 'Scarlett',
                'description' => 'Scarlett',
                'author' => 'Linda Mitchel',
                'category_ids' => [4],
            ],
        ];
    }
}
