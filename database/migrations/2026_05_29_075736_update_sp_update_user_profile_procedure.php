<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_UpdateUserProfile");

        DB::unprepared("
            CREATE PROCEDURE sp_UpdateUserProfile (
                IN p_user_id BIGINT, 
                IN p_name VARCHAR(255), 
                IN p_email VARCHAR(255), 
                IN p_student_id VARCHAR(50), 
                IN p_course VARCHAR(100), 
                IN p_year_level VARCHAR(50), 
                IN p_section VARCHAR(50),
                IN p_photo VARCHAR(255)
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;

                START TRANSACTION;

                -- Update User record
                UPDATE users 
                SET name = p_name, 
                    email = p_email, 
                    photo = CASE WHEN p_photo IS NOT NULL AND p_photo != '' THEN p_photo ELSE photo END,
                    updated_at = NOW() 
                WHERE id = p_user_id;

                -- Update or Insert Voter Profile
                INSERT INTO voter_profiles (user_id, student_id, course, year_level, section, created_at, updated_at)
                VALUES (p_user_id, p_student_id, p_course, p_year_level, p_section, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                    student_id = p_student_id,
                    course = p_course,
                    year_level = p_year_level,
                    section = p_section,
                    updated_at = NOW();

                COMMIT;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_UpdateUserProfile");

        DB::unprepared("
            CREATE PROCEDURE sp_UpdateUserProfile (
                IN p_user_id BIGINT, 
                IN p_name VARCHAR(255), 
                IN p_email VARCHAR(255), 
                IN p_student_id VARCHAR(50), 
                IN p_course VARCHAR(100), 
                IN p_year_level VARCHAR(50), 
                IN p_section VARCHAR(50)
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;

                START TRANSACTION;

                -- Update User record
                UPDATE users 
                SET name = p_name, 
                    email = p_email, 
                    updated_at = NOW() 
                WHERE id = p_user_id;

                -- Update or Insert Voter Profile
                INSERT INTO voter_profiles (user_id, student_id, course, year_level, section, created_at, updated_at)
                VALUES (p_user_id, p_student_id, p_course, p_year_level, p_section, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                    student_id = p_student_id,
                    course = p_course,
                    year_level = p_year_level,
                    section = p_section,
                    updated_at = NOW();

                COMMIT;
            END
        ");
    }
};
