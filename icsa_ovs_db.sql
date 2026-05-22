-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 03:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `icsa_ovs_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ActivateElection` (IN `p_id` BIGINT)   BEGIN
         DECLARE EXIT HANDLER FOR SQLEXCEPTION
         BEGIN
             ROLLBACK;
            RESIGNAL;
        END;
   
        START TRANSACTION;
   
        -- 1. Deactivate all elections first
        UPDATE elections SET is_active = 0;
   
        -- 2. Activate the specific target election
        UPDATE elections SET is_active = 1 WHERE id = p_id;
   
        COMMIT;
    	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CastBallot` (IN `p_user_id` BIGINT, IN `p_election_id` BIGINT, IN `p_ballot_json` JSON)   BEGIN
    DECLARE v_is_active BOOLEAN;
    DECLARE v_has_voted INT;
    DECLARE i INT DEFAULT 0;
    DECLARE j INT DEFAULT 0;
    DECLARE v_pos_count INT;
    DECLARE v_cand_count INT;
    DECLARE v_position_id BIGINT;
    DECLARE v_candidate_id BIGINT;
    DECLARE v_max_selection INT;

    -- Exit on any SQL error
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- 1. Validation: Election must be active
    SELECT is_active INTO v_is_active FROM elections WHERE id = p_election_id;
    IF v_is_active IS NULL OR v_is_active = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Election is not active';
    END IF;

    -- 2. Validation: User must not have voted yet
    SELECT COUNT(*) INTO v_has_voted FROM votes WHERE user_id = p_user_id AND election_id = p_election_id;
    IF v_has_voted > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User has already voted in this election';
    END IF;

    -- 3. Process the JSON Ballot
    SET v_pos_count = JSON_LENGTH(p_ballot_json);
    WHILE i < v_pos_count DO
        SET v_position_id = JSON_EXTRACT(p_ballot_json, CONCAT('$[', i, '].position_id'));
        
        -- Check max selection for this position
        SELECT max_selection INTO v_max_selection FROM positions WHERE id = v_position_id;
        
        SET v_cand_count = JSON_LENGTH(JSON_EXTRACT(p_ballot_json, CONCAT('$[', i, '].candidate_ids')));
        
        IF v_cand_count > v_max_selection THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Exceeded maximum selections for a position';
        END IF;

        -- Loop through candidates in the position
        SET j = 0;
        WHILE j < v_cand_count DO
            SET v_candidate_id = JSON_EXTRACT(p_ballot_json, CONCAT('$[', i, '].candidate_ids[', j, ']'));
            
            INSERT INTO votes (user_id, election_id, position_id, candidate_id, created_at, updated_at)
            VALUES (p_user_id, p_election_id, v_position_id, v_candidate_id, NOW(), NOW());
            
            SET j = j + 1;
        END WHILE;
        
        SET i = i + 1;
    END WHILE;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreateAnnouncement` (IN `p_title` VARCHAR(255), IN `p_content` TEXT, IN `p_audience` VARCHAR(50), IN `p_user_id` BIGINT)   BEGIN
        INSERT INTO announcements (title, content, audience, is_published,
      published_at, created_by, created_at, updated_at)
        VALUES (p_title, p_content, p_audience, 1, NOW(), p_user_id, NOW(),
      NOW());
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreateCandidate` (IN `p_name` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_password` VARCHAR(255), IN `p_election_id` BIGINT, IN `p_position_id` BIGINT, IN `p_partylist` VARCHAR(255), IN `p_platform` TEXT, IN `p_photo` VARCHAR(255), IN `p_course` VARCHAR(255), IN `p_year_level` VARCHAR(255), IN `p_section` VARCHAR(255))   BEGIN
    DECLARE v_user_id BIGINT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK;
      RESIGNAL; END;
    START TRANSACTION;
    INSERT INTO users (name, email, password, role,
      created_at, updated_at)
    VALUES (p_name, p_email, p_password, 'candidate',
      NOW(), NOW());
    SET v_user_id = LAST_INSERT_ID();
    INSERT INTO candidates (user_id, election_id,
      position_id, partylist, platform, photo, course,
      year_level, section, votes_count, created_at, updated_at)
    VALUES (v_user_id, p_election_id, p_position_id,
      p_partylist, p_platform, p_photo, p_course, p_year_level,
      p_section, 0, NOW(), NOW());
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreateElection` (IN `p_title` VARCHAR(255), IN `p_description` TEXT, IN `p_start_datetime` DATETIME, IN `p_end_datetime` DATETIME, IN `p_is_active` BOOLEAN)   BEGIN
    -- Exit on any SQL error and rollback changes
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    INSERT INTO elections (
        title, 
        description, 
        start_datetime, 
        end_datetime, 
        is_active, 
        created_at, 
        updated_at
    )
    VALUES (
        p_title, 
        p_description, 
        p_start_datetime, 
        p_end_datetime, 
        p_is_active, 
        NOW(), 
        NOW()
    );

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreatePosition` (IN `p_election_id` BIGINT, IN `p_name` VARCHAR(255), IN `p_max_selection` INT)   BEGIN
         INSERT INTO positions (election_id, name, max_selection, created_at,
      updated_at)
         VALUES (p_election_id, p_name, p_max_selection, NOW(), NOW());
     END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_DeactivateElection` (IN `p_id` BIGINT)   BEGIN
        DECLARE EXIT HANDLER FOR SQLEXCEPTION
        BEGIN
            ROLLBACK;
            RESIGNAL;
        END;
   
        START TRANSACTION;
   
        UPDATE elections SET is_active = 0 WHERE id =
      p_id;
   
        COMMIT;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_DeleteCandidate` (IN `p_candidate_id` BIGINT)   BEGIN
DECLARE v_user_id BIGINT;

-- Exit on any SQL error and rollback changes
DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
	ROLLBACK;
	RESIGNAL;
	END;

    START TRANSACTION;

     -- 1. Get the user_id before we delete the candidate record
    SELECT user_id INTO v_user_id FROM candidates WHERE id = p_candidate_id;

    -- 2. Delete the candidate record
   	DELETE FROM candidates WHERE id = p_candidate_id;

     -- 3. Delete the user record
    IF v_user_id IS NOT NULL THEN
        DELETE FROM users WHERE id = v_user_id;
	END IF;

	COMMIT;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_DeleteElection` (IN `p_id` BIGINT)   BEGIN
    -- Exit on any SQL error and rollback changes
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- Delete the election record
    DELETE FROM elections WHERE id = p_id;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetAllPositions` ()   BEGIN
        -- Returns all positions for the admin filter dropdowns
        SELECT id, name, election_id FROM positions ORDER BY name ASC;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetElectionStatistics` ()   BEGIN
    SELECT * FROM view_election_statistics ORDER BY created_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_RegisterVoter` (IN `p_name` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_password` VARCHAR(255), IN `p_student_id` VARCHAR(255))   BEGIN
    DECLARE v_user_id BIGINT;
    DECLARE v_course VARCHAR(255) DEFAULT NULL;
    DECLARE v_year_level VARCHAR(255) DEFAULT NULL;
    DECLARE v_section VARCHAR(255) DEFAULT NULL;

    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    
    SELECT course, year_level, section 
    INTO v_course, v_year_level, v_section
    FROM approved_students 
    WHERE student_id = p_student_id COLLATE utf8mb4_unicode_ci;

	IF v_course IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Student ID not recognized in whitelist';
    END IF;
    
    START TRANSACTION;

    
    INSERT INTO users (name, email, password, role, created_at, updated_at)
    VALUES (p_name, p_email, p_password, 'voter', NOW(), NOW());
    
    SET v_user_id = LAST_INSERT_ID();
	
    
    INSERT INTO voter_profiles (user_id, student_id, course, year_level, section, has_voted, created_at, updated_at)
    VALUES (v_user_id, p_student_id, v_course, v_year_level, v_section, 0, NOW(), NOW());

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpdateCandidate` (IN `p_candidate_id` BIGINT, IN `p_name` VARCHAR(255), IN `p_election_id` BIGINT, IN `p_position_id` BIGINT, IN `p_partylist` VARCHAR(255), IN `p_platform` TEXT, IN `p_photo` VARCHAR(255), IN `p_course` VARCHAR(50), IN `p_year_level` VARCHAR(10), IN `p_section` VARCHAR(10))   BEGIN
                DECLARE v_user_id BIGINT;
                
                -- Get the user_id for the given candidate
                SELECT user_id INTO v_user_id FROM candidates WHERE id = p_candidate_id;

                -- Update users table
                UPDATE users 
                SET name = p_name, 
                    updated_at = NOW() 
                WHERE id = v_user_id;

                -- Update candidates table
                UPDATE candidates 
                SET election_id = p_election_id,
                    position_id = p_position_id,
                    partylist = p_partylist,
                    platform = p_platform,
                    photo = p_photo,
                    course = p_course,
                    year_level = p_year_level,
                    section = p_section,
                    updated_at = NOW()
                WHERE id = p_candidate_id;
            END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpdateElection` (IN `p_id` BIGINT, IN `p_title` VARCHAR(255), IN `p_description` TEXT, IN `p_start_datetime` DATETIME, IN `p_end_datetime` DATETIME)   BEGIN
    -- Exit on any SQL error and rollback changes
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    UPDATE elections 
    SET 
        title = p_title,
        description = p_description,
        start_datetime = p_start_datetime,
        end_datetime = p_end_datetime,
        updated_at = NOW()
    WHERE id = p_id;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpdatePosition` (IN `p_id` BIGINT, IN `p_name` VARCHAR(255), IN `p_max_selection` INT)   BEGIN
        UPDATE positions SET name = p_name, max_selection = p_max_selection,
      updated_at = NOW() WHERE id = p_id;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpdateUserProfile` (IN `p_user_id` BIGINT, IN `p_name` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_student_id` VARCHAR(50), IN `p_course` VARCHAR(100), IN `p_year_level` VARCHAR(50), IN `p_section` VARCHAR(50))   BEGIN
        DECLARE EXIT HANDLER FOR SQLEXCEPTION
        BEGIN
            ROLLBACK;
            RESIGNAL;
        END;
   
        START TRANSACTION;
   
        -- Update User record
        UPDATE users 
        SET name = p_name, email = p_email, updated_at = NOW() 
        WHERE id = p_user_id;
   
        -- Update or Insert Voter Profile
        INSERT INTO voter_profiles (user_id, student_id, course, year_level,
      section, created_at, updated_at)
        VALUES (p_user_id, p_student_id, p_course, p_year_level, p_section,
      NOW(), NOW())
        ON DUPLICATE KEY UPDATE 
            student_id = p_student_id,
            course = p_course,
            year_level = p_year_level,
            section = p_section,
            updated_at = NOW();
   
        COMMIT;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpdateAnnouncement` (IN `p_id` BIGINT, IN `p_title` VARCHAR(255), IN `p_content` TEXT, IN `p_audience` VARCHAR(50))   BEGIN
        UPDATE announcements 
        SET title = p_title, 
            content = p_content, 
            audience = p_audience, 
            updated_at = NOW() 
        WHERE id = p_id;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_DeleteAnnouncement` (IN `p_id` BIGINT)   BEGIN
        DELETE FROM announcements WHERE id = p_id;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_PublishAnnouncement` (IN `p_id` BIGINT)   BEGIN
        UPDATE announcements 
        SET is_published = 1, 
            published_at = NOW(), 
            updated_at = NOW() 
        WHERE id = p_id;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UnpublishAnnouncement` (IN `p_id` BIGINT)   BEGIN
        UPDATE announcements 
        SET is_published = 0, 
            published_at = NULL, 
            updated_at = NOW() 
        WHERE id = p_id;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_DeletePosition` (IN `p_id` BIGINT)   BEGIN
        DELETE FROM positions WHERE id = p_id;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpdateCandidatePhoto` (IN `p_user_id` BIGINT, IN `p_photo` VARCHAR(255))   BEGIN
        UPDATE candidates SET photo = p_photo, updated_at = NOW() WHERE user_id = p_user_id;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpdateCandidatePlatform` (IN `p_user_id` BIGINT, IN `p_platform` TEXT)   BEGIN
        UPDATE candidates SET platform = p_platform, updated_at = NOW() WHERE user_id = p_user_id;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpdateCandidateProfile` (IN `p_user_id` BIGINT, IN `p_name` VARCHAR(255), IN `p_email` VARCHAR(255))   BEGIN
        UPDATE users SET name = p_name, email = p_email, updated_at = NOW() WHERE id = p_user_id;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpdateUserPassword` (IN `p_user_id` BIGINT, IN `p_password` VARCHAR(255))   BEGIN
        UPDATE users SET password = p_password, updated_at = NOW() WHERE id = p_user_id;
    END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `audience` enum('all','voters','candidates') NOT NULL DEFAULT 'all',
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `approved_students`
--

CREATE TABLE `approved_students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `course` enum('BSIT','BSIS') NOT NULL,
  `year_level` tinyint(3) UNSIGNED NOT NULL,
  `section` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `approved_students`
--

INSERT INTO `approved_students` (`id`, `student_id`, `name`, `email`, `course`, `year_level`, `section`, `created_at`, `updated_at`) VALUES
(1, '2024-00819', 'ABUTON, CARL JUDI LABAO', 'abuton.carl@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(2, '2024-00802', 'ACEDO, TRIZTAN MARCOS', 'acedo.triztan@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(3, '2024-00637', 'AGAG, RUZEL JHON YGONIA', 'agag.ruzel@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(4, '2024-00813', 'AGMOHOL, MELVIN JR. BASCO', 'agmohol.melvin@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(5, '2024-00767', 'AMANDOG, KIRBY GIAN COMIDA', 'amandog.kirby@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(6, '2024-00579', 'AMOGUIS, CLEMM BATUCAN', 'amoguis.clemm@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(7, '2024-00393', 'BALBERONA, NERELIE', 'balberona.nerelie@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(8, '2024-00530', 'BALDON, JOHN LORENS ABUDA', 'baldon.john@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(9, '2024-00714', 'BASILLOTE, JONARD DIVINO', 'basillote.jonard@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(10, '2024-00558', 'BATILONG, KIMBIE JARO', 'batilong.kimbie@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(11, '2024-00684', 'BAYACAG, GIAN CEDRICK', 'bayacag.gian@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(12, '2024-00724', 'CAPUNO, CHRISTIAN LACSON', 'capuno.christian@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(13, '2024-00633', 'CARBAJOSA, FROYD DUGOY', 'carbajosa.froyd@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(14, '2024-02234', 'DESIERTO, HENDREY CLARIDAD', 'desierto.hendrey@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(15, '2024-00776', 'EYAS, CHRISTIAN JAMES OGONG', 'eyas.christian@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(16, '2024-00758', 'FUENTES, KHINT JOSEPH OMPAD', 'fuentes.khint@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(17, '2024-00678', 'GABAYE, JON ARCELLE GEMOTA', 'gabaye.jon@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(18, '2024-00731', 'GALVE, ZAIRANICOLE SOMBRIO', 'galve.zairanicole@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(19, '2024-00703', 'GEQUILAN, ELVIE NARIZ', 'gequilan.elvie@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(20, '2024-00388', 'GOLOSINO, FELAURA VIVIEN', 'golosino.felaura@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(21, '2024-00647', 'MANTE, CYRIL MARK FLORES', 'mante.cyril@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(22, '2024-00591', 'MENDOZA, JOHN PAUL REDORME', 'mendoza.john@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(23, '2024-00785', 'OSAHITA, JOY ANNE LITERAL', 'osahita.joy@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(24, '2024-00545', 'PAGALAN, BRYL JAMES', 'pagalan.bryl@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(25, '2024-00585', 'PANGAN, TJ LANADA', 'pangan.tj@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(26, '2024-00849', 'PARSAN, RUEL CLIANT', 'parsan.ruel@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(27, '2024-01903', 'PEREZ, SEVERINO PAMA', 'perez.severino@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(28, '2024-00551', 'QUINES, MONCH WALTER', 'quines.monch@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(29, '2024-00797', 'RABOR, FELIX VICTOR DAROCA', 'rabor.felix@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(30, '2024-02510', 'RAMIREZ, EARL VINCENT REYES', 'ramirez.earl@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(31, '2024-00832', 'RODRIGUEZ, KARYL ROSE RAMA', 'rodriguez.karyl@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(32, '2023-00535', 'ROTA, JHON CARLO VILLARANTE', 'rota.jhon@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(33, '2024-00654', 'SARIO, KRISHIA MAE', 'sario.krishia@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(34, '2024-00840', 'TULISANA, DIXTER BAJENTING', 'tulisana.dixter@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(35, '2024-00665', 'TUMAGAN, LIEZEL NERI', 'tumagan.liezel@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08'),
(36, '2024-00699', 'VILLAR, RAFAEL MAR LAMINTAC', 'villar.rafael@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2026-05-04 06:01:08', '2026-05-04 06:01:08');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('icsa-ovs-cache-3a6817bfc4bfa69574963f255a5e107f', 'i:1;', 1779196909),
('icsa-ovs-cache-3a6817bfc4bfa69574963f255a5e107f:timer', 'i:1779196909;', 1779196909),
('icsa-ovs-cache-6888bf4fdebdad3275b8bfe5d74574d4', 'i:2;', 1778632763),
('icsa-ovs-cache-6888bf4fdebdad3275b8bfe5d74574d4:timer', 'i:1778632763;', 1778632763),
('icsa-ovs-cache-8147dc367efa7b02395072bfff107c66', 'i:1;', 1778335399),
('icsa-ovs-cache-8147dc367efa7b02395072bfff107c66:timer', 'i:1778335399;', 1778335399),
('icsa-ovs-cache-93ff06804ba6530c214a1c86907c1a4c', 'i:1;', 1779196856),
('icsa-ovs-cache-93ff06804ba6530c214a1c86907c1a4c:timer', 'i:1779196856;', 1779196856),
('icsa-ovs-cache-dc6a92740e02a15e5bf1b6d8242d70e5', 'i:1;', 1778632808),
('icsa-ovs-cache-dc6a92740e02a15e5bf1b6d8242d70e5:timer', 'i:1778632808;', 1778632808),
('icsa-ovs-cache-ff61d3abb2b9b3768101289d86d830be', 'i:1;', 1778632950),
('icsa-ovs-cache-ff61d3abb2b9b3768101289d86d830be:timer', 'i:1778632950;', 1778632950),
('icsa-ovs-cache-maestrotiptop@gmail.com|127.0.0.1', 'i:1;', 1779196862),
('icsa-ovs-cache-maestrotiptop@gmail.com|127.0.0.1:timer', 'i:1779196862;', 1779196862),
('icsa-ovs-cache-quines.monchwalter@dnscedu.onmicrosoft.com|127.0.0.1', 'i:2;', 1778632765),
('icsa-ovs-cache-quines.monchwalter@dnscedu.onmicrosoft.com|127.0.0.1:timer', 'i:1778632765;', 1778632765),
('icsa-ovs-cache-quinesmonch@gmail.com|127.0.0.1', 'i:1;', 1778335399),
('icsa-ovs-cache-quinesmonch@gmail.com|127.0.0.1:timer', 'i:1778335399;', 1778335399);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `election_id` bigint(20) UNSIGNED NOT NULL,
  `position_id` bigint(20) UNSIGNED DEFAULT NULL,
  `partylist` varchar(255) DEFAULT NULL,
  `votes_count` int(11) NOT NULL DEFAULT 0,
  `platform` text DEFAULT NULL,
  `photo` varchar(255) NOT NULL,
  `course` varchar(255) NOT NULL,
  `year_level` varchar(255) NOT NULL,
  `section` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `user_id`, `election_id`, `position_id`, `partylist`, `votes_count`, `platform`, `photo`, `course`, `year_level`, `section`, `created_at`, `updated_at`) VALUES
(13, 19, 8, 5, 'WOWERS', 0, 'diubwduwdiwbdkbakljsndlkjbalskjbdouqgysahvbljzbxc,mxb,mbiorugqwoiueyqouiweyqoiugdjbhflkjshbfljhbtejgauoygsdsa', '1778474254_icsa_logo.png', 'BSIT', '2', 'B', '2026-05-11 04:37:34', '2026-05-12 10:18:57'),
(14, 20, 8, 5, 'dwa', 1, 'dwdawdasdawsddfdcs awfegfsdwdmnbwnmdb as,nbd daiowdhiwajhbsljkndbwn a d w dwaidhwaipjdbwadwdasdwawd', '1778524659_passport size.png', 'BSIT', '2', 'B', '2026-05-11 18:37:40', '2026-05-11 18:37:40');

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_datetime` timestamp NULL DEFAULT NULL,
  `end_datetime` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`id`, `title`, `description`, `start_datetime`, `end_datetime`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'ICSA Election 2026', NULL, '2026-05-09 07:07:00', '2026-05-15 07:07:00', 0, '2026-05-08 23:07:58', '2026-05-09 06:07:28'),
(8, 'Waer', NULL, '2026-05-11 18:36:00', '2026-05-14 18:36:00', 0, '2026-05-11 18:36:22', '2026-05-11 18:36:22');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_08_14_170933_add_two_factor_columns_to_users_table', 1),
(5, '2025_11_18_082302_create_voter_profiles_table', 1),
(6, '2025_11_18_083036_create_elections_table', 1),
(7, '2025_11_18_083650_create_candidates_table', 1),
(8, '2025_11_18_085000_create_positions_table', 1),
(9, '2025_11_18_085145_create_votes_table', 1),
(10, '2025_11_20_065951_create_approved_students_table', 1),
(11, '2025_11_21_224900_add_section_to_approved_students_table', 1),
(12, '2025_11_24_074031_add_position_id_to_candidates_table', 1),
(13, '2025_12_03_172303_add_required_fields_to_candidates_table', 1),
(14, '2025_12_03_182821_remove_position_column_from_candidates_table', 1),
(15, '2025_12_04_045052_create_announcements_table', 1),
(16, '2025_12_05_110448_add_photo_to_users_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('quinesmonch@gmail.com', '$2y$12$nxqA.XKk5hClCL3mJbHn6.un0m9vMB9q85GSKwwDk5kOsZQbCNmBW', '2026-05-08 21:35:05');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `election_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `max_selection` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `election_id`, `name`, `max_selection`, `created_at`, `updated_at`) VALUES
(1, 2, 'President', 1, '2026-05-04 06:09:37', '2026-05-08 23:08:16'),
(2, 2, 'Vice President', 1, '2026-05-10 05:35:44', '2026-05-10 05:35:44'),
(5, 8, 'Pres', 1, '2026-05-11 10:36:34', '2026-05-11 10:36:34'),
(6, 8, 'VP', 1, '2026-05-11 10:36:40', '2026-05-11 10:36:40');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('7FPhuMzArozdaotVr49QMNLVEQprxwCRd5cSrNKV', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.119.1 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUFdzRGczS1BVZmx0eVlZVlFCRGhENGpjSWhkTTVDMktMRGhpMDIxSCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM2OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWRtaW4vcG9zaXRpb24iO3M6NToicm91dGUiO3M6MTQ6ImFkbWluLnBvc2l0aW9uIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1778640556),
('amJFN9k6rfHjY5KegoDjuduBaqSMSJ9G9waueamx', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiT0l5QkdNOUhrbnhLWlphN0FkMmVEUVJnYjJwQWNxY0RycDJ6dkE2QyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9lbGVjdGlvbiI7czo1OiJyb3V0ZSI7czoxNDoiYWRtaW4uZWxlY3Rpb24iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1779196974),
('fOplR8fCS3mKkfX0z9H0Q2yM322MkaSyGMrstJOg', 20, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieHFRT2FkcXpRb0VhRmlLWVJ0UEpQN2xkeklhdTJlZm95S1BlUUEwcSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9jYW5kaWRhdGUvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjE5OiJjYW5kaWRhdGUuZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjA7fQ==', 1778640451),
('KO6RENSR4WOAdIi3CjKH6FpsQenAl4dF38a0Cvra', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWWpKb3BpVzkwN1BTVDZJQ2xXcGVTVDlUcU82R0VxQ0M0YmZBakc1YSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1779196712),
('u3j7h19IJYo7Pww176tALi9j5MgJIElbuFPF79jV', 7, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZGh0MHN4c2k3VzBhZWtBRFJ5WUpETGhjbGZMNDFlTFVVWjVXY1hJbSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvdm90ZXIvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjE1OiJ2b3Rlci5kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo3O30=', 1778640450),
('v90DhRL9pNJikaahykNJXcnFBeoQIKkSU81G0Bem', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNTA5amNFRWNEWjlZQUZWSjQ4Q0lvZ3RlemZPWHNjNDIzdFdramc3VCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6Mzg6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9jYW5kaWRhdGVzIjt9fQ==', 1779196785),
('yRgtWlyZhAiXLlE7NQOogM1ApLIugPhWtbBTrfQ8', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNzNYbjBTY0ZxRGN2WHFhc2FBTmlvVWh0QVdWQ0w3V2ZFZ2Vab3hESSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1779196713);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','voter','candidate') NOT NULL DEFAULT 'voter',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `role`, `remember_token`, `created_at`, `updated_at`, `photo`) VALUES
(1, 'Admin User', 'admin@icsa.com', NULL, '$2y$12$QSI9d/93BnOQgfq5EK0mZurajsldEZmwnHw.mnHGW5MO5Basd4LtO', NULL, NULL, NULL, 'admin', NULL, '2026-05-04 06:01:08', '2026-05-04 06:01:08', NULL),
(2, 'Test Voter', 'voter@icsa.com', NULL, '$2y$12$KZXX1r2miLorrJnOHj3QGOZWK5U1KCKMt9mUJW.g6OsPAbDSiC5Ie', NULL, NULL, NULL, 'voter', NULL, '2026-05-04 06:01:08', '2026-05-04 06:01:08', NULL),
(3, 'Test Candidate', 'candidate@icsa.com', NULL, '$2y$12$O4DOf0COoTcyi5godnsVDepRK/CMbs4Jfw6KW9iLGNvzqsrivR2cq', NULL, NULL, NULL, 'candidate', NULL, '2026-05-04 06:01:08', '2026-05-04 06:01:08', NULL),
(7, 'Monch Walter Quines', 'quines.monch@dnscedu.onmicrosoft.com', NULL, '$2y$12$IRnG2Ywx5cNu13bI1ZfT5.Ew7YrJ5bqvaxRxSE8fSnZgFXRpR2I2W', NULL, NULL, NULL, 'voter', NULL, '2026-05-09 05:54:12', '2026-05-09 05:54:12', NULL),
(19, 'TipTop Maestro', 'tiptopmaestro12@gmail.com', NULL, '$2y$12$fop84KsRN2Ne8yo5zYQ0G.a/1Qown8eTiu0eC4iuJv/iNFAcF4bZq', NULL, NULL, NULL, 'candidate', NULL, '2026-05-11 04:37:34', '2026-05-12 10:18:57', NULL),
(20, 'MonMaon', 'maestrotiptop@gmail.com', NULL, '$2y$12$ZmkCL3Ch0TnhIwPGzeu1sOQJct10QfJ1/X8h0jOvPF4aNpRkJWsNi', NULL, NULL, NULL, 'candidate', NULL, '2026-05-11 18:37:40', '2026-05-11 18:37:40', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_auth_users`
-- (See below for the actual view)
--
CREATE TABLE `view_auth_users` (
`id` bigint(20) unsigned
,`email` varchar(255)
,`password` varchar(255)
,`role` enum('admin','voter','candidate')
,`name` varchar(255)
,`photo` varchar(255)
,`student_id` varchar(255)
,`course` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_candidates_details`
-- (See below for the actual view)
--
CREATE TABLE `view_candidates_details` (
`id` bigint(20) unsigned
,`user_id` bigint(20) unsigned
,`election_id` bigint(20) unsigned
,`position_id` bigint(20) unsigned
,`partylist` varchar(255)
,`platform` text
,`photo` varchar(255)
,`course` varchar(255)
,`year_level` varchar(255)
,`section` varchar(255)
,`votes_count` int(11)
,`created_at` timestamp
,`updated_at` timestamp
,`user_name` varchar(255)
,`user_email` varchar(255)
,`election_title` varchar(255)
,`position_name` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_candidate_dashboard`
-- (See below for the actual view)
--
CREATE TABLE `view_candidate_dashboard` (
`candidate_id` bigint(20) unsigned
,`user_id` bigint(20) unsigned
,`candidate_name` varchar(255)
,`candidate_email` varchar(255)
,`candidate_photo` varchar(255)
,`partylist` varchar(255)
,`platform` text
,`course` varchar(255)
,`year_level` varchar(255)
,`section` varchar(255)
,`position_id` bigint(20) unsigned
,`position_name` varchar(255)
,`election_id` bigint(20) unsigned
,`election_title` varchar(255)
,`election_description` text
,`start_datetime` timestamp
,`end_datetime` timestamp
,`election_status` varchar(9)
,`votes_count` int(11)
,`vote_percentage` decimal(16,2)
,`ranking` bigint(21)
,`total_candidates_in_position` bigint(21)
,`total_system_voters` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_election_results`
-- (See below for the actual view)
--
CREATE TABLE `view_election_results` (
`candidate_id` bigint(20) unsigned
,`candidate_name` varchar(255)
,`candidate_photo` varchar(255)
,`partylist` varchar(255)
,`course` varchar(255)
,`year_level` varchar(255)
,`section` varchar(255)
,`position_name` varchar(255)
,`election_title` varchar(255)
,`election_id` bigint(20) unsigned
,`position_id` bigint(20) unsigned
,`votes_count` int(11)
,`vote_percentage` decimal(16,2)
,`current_rank` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_election_statistics`
-- (See below for the actual view)
--
CREATE TABLE `view_election_statistics` (
`id` bigint(20) unsigned
,`title` varchar(255)
,`description` text
,`start_datetime` timestamp
,`end_datetime` timestamp
,`is_active` tinyint(1)
,`positions_count` bigint(21)
,`candidates_count` bigint(21)
,`votes_count` bigint(21)
,`voted_count` bigint(21)
,`total_voters` bigint(21)
,`status` varchar(9)
,`created_at` timestamp
,`updated_at` timestamp
);

CREATE TABLE `view_positions_details` (
`id` bigint(20) unsigned
,`name` varchar(255)
,`max_selection` int(11)
,`election_id` bigint(20) unsigned
,`election_title` varchar(255)
,`election_description` text
,`election_is_active` tinyint(1)
,`candidates_count` bigint(21)
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_recent_votes`
-- (See below for the actual view)
--
CREATE TABLE `view_recent_votes` (
`id` bigint(20) unsigned
,`voter_name` varchar(255)
,`voter_photo` varchar(255)
,`candidate_name` varchar(255)
,`position_name` varchar(255)
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_voter_details`
-- (See below for the actual view)
--
CREATE TABLE `view_voter_details` (
`user_id` bigint(20) unsigned
,`name` varchar(255)
,`email` varchar(255)
,`role` enum('admin','voter','candidate')
,`photo` varchar(255)
,`profile_id` bigint(20) unsigned
,`student_id` varchar(255)
,`course` varchar(255)
,`year_level` varchar(255)
,`section` varchar(255)
,`has_voted_active` int(1)
,`created_at` timestamp
,`updated_at` timestamp
);

CREATE TABLE `view_voting_receipt` (
`id` bigint(20) unsigned
,`user_id` bigint(20) unsigned
,`election_id` bigint(20) unsigned
,`candidate_id` bigint(20) unsigned
,`position_id` bigint(20) unsigned
,`created_at` timestamp
,`candidate_name` varchar(255)
,`candidate_photo` varchar(255)
,`partylist` varchar(255)
,`position_name` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `voter_profiles`
--

CREATE TABLE `voter_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `course` varchar(255) NOT NULL,
  `year_level` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `has_voted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `voter_profiles`
--

INSERT INTO `voter_profiles` (`id`, `user_id`, `student_id`, `course`, `year_level`, `section`, `has_voted`, `created_at`, `updated_at`) VALUES
(3, 7, '2024-00551', 'BSIT', '2', 'B', 0, '2026-05-09 05:54:12', '2026-05-09 05:54:12');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `election_id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `position_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `user_id`, `election_id`, `candidate_id`, `position_id`, `created_at`, `updated_at`) VALUES
(2, 7, 8, 14, 5, '2026-05-12 00:20:33', '2026-05-12 00:20:33');

--
-- Triggers `votes`
--
DELIMITER $$
CREATE TRIGGER `trg_UpdateVoteCount` AFTER INSERT ON `votes` FOR EACH ROW BEGIN
    UPDATE candidates 
    SET votes_count = votes_count + 1 
    WHERE id = NEW.candidate_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure for view `view_auth_users`
--
DROP TABLE IF EXISTS `view_auth_users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_auth_users`  AS SELECT `u`.`id` AS `id`, `u`.`email` AS `email`, `u`.`password` AS `password`, `u`.`role` AS `role`, `u`.`name` AS `name`, `u`.`photo` AS `photo`, `vp`.`student_id` AS `student_id`, `vp`.`course` AS `course` FROM (`users` `u` left join `voter_profiles` `vp` on(`u`.`id` = `vp`.`user_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_candidates_details`
--
DROP TABLE IF EXISTS `view_candidates_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_candidates_details`  AS SELECT `c`.`id` AS `id`, `c`.`user_id` AS `user_id`, `c`.`election_id` AS `election_id`, `c`.`position_id` AS `position_id`, `c`.`partylist` AS `partylist`, `c`.`platform` AS `platform`, `c`.`photo` AS `photo`, `c`.`course` AS `course`, `c`.`year_level` AS `year_level`, `c`.`section` AS `section`, `c`.`votes_count` AS `votes_count`, `c`.`created_at` AS `created_at`, `c`.`updated_at` AS `updated_at`, `u`.`name` AS `user_name`, `u`.`email` AS `user_email`, `e`.`title` AS `election_title`, `p`.`name` AS `position_name` FROM (((`candidates` `c` join `users` `u` on(`c`.`user_id` = `u`.`id`)) join `elections` `e` on(`c`.`election_id` = `e`.`id`)) join `positions` `p` on(`c`.`position_id` = `p`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_candidate_dashboard`
--
DROP TABLE IF EXISTS `view_candidate_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_candidate_dashboard`  AS SELECT `c`.`id` AS `candidate_id`, `c`.`user_id` AS `user_id`, `u`.`name` AS `candidate_name`, `u`.`email` AS `candidate_email`, `c`.`photo` AS `candidate_photo`, `c`.`partylist` AS `partylist`, `c`.`platform` AS `platform`, `c`.`course` AS `course`, `c`.`year_level` AS `year_level`, `c`.`section` AS `section`, `p`.`id` AS `position_id`, `p`.`name` AS `position_name`, `e`.`id` AS `election_id`, `e`.`title` AS `election_title`, `e`.`description` AS `election_description`, `e`.`start_datetime` AS `start_datetime`, `e`.`end_datetime` AS `end_datetime`, `es`.`status` AS `election_status`, `er`.`votes_count` AS `votes_count`, `er`.`vote_percentage` AS `vote_percentage`, `er`.`current_rank` AS `ranking`, (select count(0) from `candidates` `c2` where `c2`.`position_id` = `c`.`position_id`) AS `total_candidates_in_position`, (select count(0) from `voter_profiles`) AS `total_system_voters` FROM (((((`candidates` `c` join `users` `u` on(`c`.`user_id` = `u`.`id`)) join `positions` `p` on(`c`.`position_id` = `p`.`id`)) join `elections` `e` on(`c`.`election_id` = `e`.`id`)) join `view_election_statistics` `es` on(`e`.`id` = `es`.`id`)) join `view_election_results` `er` on(`c`.`id` = `er`.`candidate_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_election_results`
--
DROP TABLE IF EXISTS `view_election_results`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_election_results`  AS SELECT `c`.`id` AS `candidate_id`, `u`.`name` AS `candidate_name`, `c`.`photo` AS `candidate_photo`, `c`.`partylist` AS `partylist`, `c`.`course` AS `course`, `c`.`year_level` AS `year_level`, `c`.`section` AS `section`, `p`.`name` AS `position_name`, `e`.`title` AS `election_title`, `c`.`election_id` AS `election_id`, `c`.`position_id` AS `position_id`, `c`.`votes_count` AS `votes_count`, round(`c`.`votes_count` / nullif(sum(`c`.`votes_count`) over ( partition by `c`.`position_id`), 0) * 100,2) AS `vote_percentage`, rank() over ( partition by `c`.`position_id` order by `c`.`votes_count` desc) AS `current_rank` FROM (((`candidates` `c` join `users` `u` on(`c`.`user_id` = `u`.`id`)) join `positions` `p` on(`c`.`position_id` = `p`.`id`)) join `elections` `e` on(`c`.`election_id` = `e`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_election_statistics`
--
DROP TABLE IF EXISTS `view_election_statistics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_election_statistics`  AS SELECT `e`.`id` AS `id`, `e`.`title` AS `title`, `e`.`description` AS `description`, `e`.`start_datetime` AS `start_datetime`, `e`.`end_datetime` AS `end_datetime`, `e`.`is_active` AS `is_active`, (select count(0) from `positions` `p` where `p`.`election_id` = `e`.`id`) AS `positions_count`, (select count(0) from `candidates` `c` where `c`.`election_id` = `e`.`id`) AS `candidates_count`, (select count(0) from `votes` `v` where `v`.`election_id` = `e`.`id`) AS `votes_count`, (select count(distinct `v`.`user_id`) from `votes` `v` where `v`.`election_id` = `e`.`id`) AS `voted_count`, (select count(0) from `voter_profiles`) AS `total_voters`, CASE WHEN `e`.`is_active` = 1 THEN 'active' COLLATE utf8mb4_unicode_ci WHEN current_timestamp() > `e`.`end_datetime` THEN 'ended' COLLATE utf8mb4_unicode_ci WHEN current_timestamp() < `e`.`start_datetime` THEN 'scheduled' COLLATE utf8mb4_unicode_ci ELSE 'scheduled' COLLATE utf8mb4_unicode_ci END AS `status`, `e`.`created_at` AS `created_at`, `e`.`updated_at` AS `updated_at` FROM `elections` AS `e` ;

-- --------------------------------------------------------

--
-- Structure for view `view_positions_details`
--
DROP TABLE IF EXISTS `view_positions_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_positions_details`  AS SELECT `p`.`id` AS `id`, `p`.`name` AS `name`, `p`.`max_selection` AS `max_selection`, `p`.`election_id` AS `election_id`, `e`.`title` AS `election_title`, `e`.`description` AS `election_description`, `e`.`is_active` AS `election_is_active`, (select count(0) from `candidates` `c` where `c`.`position_id` = `p`.`id`) AS `candidates_count`, `p`.`created_at` AS `created_at` FROM (`positions` `p` join `elections` `e` on(`p`.`election_id` = `e`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_recent_votes`
--
DROP TABLE IF EXISTS `view_recent_votes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_recent_votes`  AS SELECT `v`.`id` AS `id`, `u`.`name` AS `voter_name`, `u`.`photo` AS `voter_photo`, `c_u`.`name` AS `candidate_name`, `p`.`name` AS `position_name`, `v`.`created_at` AS `created_at` FROM ((((`votes` `v` join `users` `u` on(`v`.`user_id` = `u`.`id`)) join `candidates` `c` on(`v`.`candidate_id` = `c`.`id`)) join `users` `c_u` on(`c`.`user_id` = `c_u`.`id`)) join `positions` `p` on(`v`.`position_id` = `p`.`id`)) ORDER BY `v`.`created_at` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `view_voter_details`
--
DROP TABLE IF EXISTS `view_voter_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_voter_details`  AS SELECT `u`.`id` AS `user_id`, `u`.`name` AS `name`, `u`.`email` AS `email`, `u`.`role` AS `role`, `u`.`photo` AS `photo`, `vp`.`id` AS `profile_id`, `vp`.`student_id` AS `student_id`, `vp`.`course` AS `course`, `vp`.`year_level` AS `year_level`, `vp`.`section` AS `section`, exists(select 1 from (`votes` `v` join `elections` `e` on(`v`.`election_id` = `e`.`id`)) where `v`.`user_id` = `u`.`id` and `e`.`is_active` = 1 limit 1) AS `has_voted_active`, `u`.`created_at` AS `created_at`, `u`.`updated_at` AS `updated_at` FROM (`users` `u` left join `voter_profiles` `vp` on(`u`.`id` = `vp`.`user_id`)) WHERE `u`.`role` = 'voter' ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_is_published_index` (`is_published`),
  ADD KEY `announcements_audience_index` (`audience`),
  ADD KEY `announcements_created_by_index` (`created_by`);

--
-- Indexes for table `approved_students`
--
ALTER TABLE `approved_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `approved_students_student_id_unique` (`student_id`),
  ADD UNIQUE KEY `approved_students_email_unique` (`email`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `candidates_user_id_foreign` (`user_id`),
  ADD KEY `candidates_election_id_foreign` (`election_id`),
  ADD KEY `candidates_position_id_foreign` (`position_id`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `positions_election_id_foreign` (`election_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `voter_profiles`
--
ALTER TABLE `voter_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voter_profiles_student_id_unique` (`student_id`),
  ADD KEY `voter_profiles_user_id_foreign` (`user_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `votes_user_id_foreign` (`user_id`),
  ADD KEY `votes_election_id_foreign` (`election_id`),
  ADD KEY `votes_candidate_id_foreign` (`candidate_id`),
  ADD KEY `votes_position_id_foreign` (`position_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `approved_students`
--
ALTER TABLE `approved_students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `voter_profiles`
--
ALTER TABLE `voter_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidates_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `voter_profiles`
--
ALTER TABLE `voter_profiles`
  ADD CONSTRAINT `voter_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
