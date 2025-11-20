<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ApprovedStudent;
class ApprovedStudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApprovedStudent::create([
            'student_id' => '2021-00123',
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'course' => 'BSIT',
            'year_level' => 3,
        ]);

        ApprovedStudent::create([
            'student_id' => '2021-00456',
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'course' => 'BSIS',
            'year_level' => 2,
        ]);
    }
}
