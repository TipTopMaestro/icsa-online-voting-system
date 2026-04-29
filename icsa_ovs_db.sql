-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2026 at 02:24 PM
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

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `audience`, `is_published`, `published_at`, `created_by`, `created_at`, `updated_at`) VALUES
(4, 'ICSA 2025 Election', 'The Institute of Computing Student Assembly (ICSA) officially announces the commencement of the ICSA 2025 Election. All qualified students are invited to participate in the democratic selection of future student leaders who will represent the institute for the upcoming academic year.', 'all', 1, '2025-12-09 20:36:48', 1, '2025-12-09 20:36:48', '2025-12-09 20:36:48');

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
(1, '2024-00819', 'ABUTON, CARL JUDI LABAO', 'abuton.carl@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(2, '2024-00802', 'ACEDO, TRIZTAN MARCOS', 'acedo.triztan@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(3, '2024-00637', 'AGAG, RUZEL JHON YGONIA', 'agag.ruzel@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(4, '2024-00813', 'AGMOHOL, MELVIN JR. BASCO', 'agmohol.melvin@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(5, '2024-00767', 'AMANDOG, KIRBY GIAN COMIDA', 'amandog.kirby@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(6, '2024-00579', 'AMOGUIS, CLEMM BATUCAN', 'amoguis.clemm@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(7, '2024-00393', 'BALBERONA, NERELIE', 'balberona.nerelie@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(8, '2024-00530', 'BALDON, JOHN LORENS ABUDA', 'baldon.john@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(9, '2024-00714', 'BASILLOTE, JONARD DIVINO', 'basillote.jonard@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(10, '2024-00558', 'BATILONG, KIMBIE JARO', 'batilong.kimbie@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(11, '2024-00684', 'BAYACAG, GIAN CEDRICK', 'bayacag.gian@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(12, '2024-00724', 'CAPUNO, CHRISTIAN LACSON', 'capuno.christian@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(13, '2024-00633', 'CARBAJOSA, FROYD DUGOY', 'carbajosa.froyd@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(14, '2024-02234', 'DESIERTO, HENDREY CLARIDAD', 'desierto.hendrey@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(15, '2024-00776', 'EYAS, CHRISTIAN JAMES OGONG', 'eyas.christian@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(16, '2024-00758', 'FUENTES, KHINT JOSEPH OMPAD', 'fuentes.khint@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(17, '2024-00678', 'GABAYE, JON ARCELLE GEMOTA', 'gabaye.jon@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(18, '2024-00731', 'GALVE, ZAIRANICOLE SOMBRIO', 'galve.zairanicole@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(19, '2024-00703', 'GEQUILAN, ELVIE NARIZ', 'gequilan.elvie@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(20, '2024-00388', 'GOLOSINO, FELAURA VIVIEN', 'golosino.felaura@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(21, '2024-00647', 'MANTE, CYRIL MARK FLORES', 'mante.cyril@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(22, '2024-00591', 'MENDOZA, JOHN PAUL REDORME', 'mendoza.john@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(23, '2024-00785', 'OSAHITA, JOY ANNE LITERAL', 'osahita.joy@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(24, '2024-00545', 'PAGALAN, BRYL JAMES', 'pagalan.bryl@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(25, '2024-00585', 'PANGAN, TJ LANADA', 'pangan.tj@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(26, '2024-00849', 'PARSAN, RUEL CLIANT', 'parsan.ruel@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(27, '2024-01903', 'PEREZ, SEVERINO PAMA', 'perez.severino@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(28, '2024-00551', 'QUINES, MONCH WALTER', 'quines.monch@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(29, '2024-00797', 'RABOR, FELIX VICTOR DAROCA', 'rabor.felix@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(30, '2024-02510', 'RAMIREZ, EARL VINCENT REYES', 'ramirez.earl@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(31, '2024-00832', 'RODRIGUEZ, KARYL ROSE RAMA', 'rodriguez.karyl@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(32, '2023-00535', 'ROTA, JHON CARLO VILLARANTE', 'rota.jhon@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(33, '2024-00654', 'SARIO, KRISHIA MAE', 'sario.krishia@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(34, '2024-00840', 'TULISANA, DIXTER BAJENTING', 'tulisana.dixter@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(35, '2024-00665', 'TUMAGAN, LIEZEL NERI', 'tumagan.liezel@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04'),
(36, '2024-00699', 'VILLAR, RAFAEL MAR LAMINTAC', 'villar.rafael@dnscedu.onmicrosoft.com', 'BSIT', 2, 'B', '2025-12-07 23:06:04', '2025-12-07 23:06:04');

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
('icsa-ovs-cache-1c361c7abce4d66995ef1645586fd76c', 'i:1;', 1765420054),
('icsa-ovs-cache-1c361c7abce4d66995ef1645586fd76c:timer', 'i:1765420054;', 1765420054),
('icsa-ovs-cache-2984e9a66b952a201bf604364c1f27b6', 'i:1;', 1768297609),
('icsa-ovs-cache-2984e9a66b952a201bf604364c1f27b6:timer', 'i:1768297609;', 1768297609),
('icsa-ovs-cache-356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1765179521),
('icsa-ovs-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1765179521;', 1765179521),
('icsa-ovs-cache-3a6817bfc4bfa69574963f255a5e107f', 'i:2;', 1765179493),
('icsa-ovs-cache-3a6817bfc4bfa69574963f255a5e107f:timer', 'i:1765179493;', 1765179493),
('icsa-ovs-cache-3d90d3f2c870e7b0508a251316a89720', 'i:1;', 1765420311),
('icsa-ovs-cache-3d90d3f2c870e7b0508a251316a89720:timer', 'i:1765420311;', 1765420311),
('icsa-ovs-cache-6061f0e68acab7b0bbc0bf3d6fe27a5d', 'i:1;', 1765242574),
('icsa-ovs-cache-6061f0e68acab7b0bbc0bf3d6fe27a5d:timer', 'i:1765242574;', 1765242574),
('icsa-ovs-cache-8e3e0009b802f03de6647c2da1b06b0d', 'i:1;', 1765247462),
('icsa-ovs-cache-8e3e0009b802f03de6647c2da1b06b0d:timer', 'i:1765247462;', 1765247462),
('icsa-ovs-cache-d197339ec6404448184d520c5fa610e1', 'i:1;', 1765420259),
('icsa-ovs-cache-d197339ec6404448184d520c5fa610e1:timer', 'i:1765420259;', 1765420259),
('icsa-ovs-cache-ea57acf224e0c1f363fbfb882d5608ab', 'i:1;', 1765420226),
('icsa-ovs-cache-ea57acf224e0c1f363fbfb882d5608ab:timer', 'i:1765420226;', 1765420226),
('icsa-ovs-cache-fd21416c953885902347c91f4c15c63d', 'i:1;', 1765341808),
('icsa-ovs-cache-fd21416c953885902347c91f4c15c63d:timer', 'i:1765341808;', 1765341808);

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
(37, 50, 12, 20, 'BUGSAI', 2, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', '1765419569_597341450_1427212878822998_7008031593482909603_n.jpg', 'BSIT', '2', 'B', '2025-12-10 18:19:30', '2025-12-10 18:30:08'),
(38, 51, 12, 20, 'ABANTE', 1, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', '1765419761_WIN_20251114_11_18_10_Pro.jpg', 'BSIT', '2', 'B', '2025-12-10 18:22:41', '2025-12-10 18:28:38');

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
(12, 'PHC Election', 'new officers', '2025-12-11 02:14:00', '2025-12-25 02:14:00', 1, '2025-12-10 18:14:39', '2025-12-10 18:26:14');

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
('froydcarbajosa14@gmail.com', '$2y$12$tj.AEo2FkQJWQ5gVbl..3uETb9Ht2rCPLxqwe5TAa5IxT4UmsAs6i', '2025-12-08 18:11:22');

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
(20, 12, 'President', 1, '2025-12-10 18:15:20', '2025-12-10 18:15:20'),
(21, 12, 'Vice president', 1, '2025-12-10 18:15:29', '2025-12-10 18:15:29');

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
('CKAd7jzWgxoQ4n164u6uPBHGvtWOefRjs820ivdF', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiamZLdHdRUzViR1FXdTlNeTNqUjlWUlZrTVJxVEJTU2NuOFFWZmNHaSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1768215161),
('nvAiYY9qzRmLmL1NyXg7l8ovwfbO4FOpts9XpvC8', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiamQ5ZUMyM0JBaGdHc1BWeTlacGx4MXYxcnhiTU1IN0dBbmpYMWpSdiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9fQ==', 1765420440),
('nviMOaUz4g5CbAQX2ar1pCwYouLOdiHUNyQkG21P', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoickc4dFFVdldLVllCRlBNUkNVak9Ja3hmbHhyZ3ozdGRiVWthOHNzSyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2Rhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO319', 1768307699),
('qVAuNNnBounXHLm6XkCR03Jk9kXmcEPKmAoFZuna', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQTRLd1VqNlZHVmhWVGJndFlkdm9ETUtzckdyNU5hbGJxRVJuM3YzUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1768297653),
('VwCMdmLzYfuSzEdjR78sQQadZqfPFMepy7yCBAYs', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZTBGYWl4SDl6TzJaN29UajlhZXMzZEhtU1NqWDhEMnZYb3dJNHZCRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768271103);

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
(1, 'Admin User', 'froydcarbajosa14@gmail.com', NULL, '$2y$12$djMF7QmAR1hT/oWDzyc3COv12K464CXWVmAKsHkflzGVGNfwgtqPO', NULL, NULL, NULL, 'admin', NULL, '2025-12-07 22:32:02', '2025-12-07 23:37:42', NULL),
(17, 'GOLOSINO, FELAURA VIVIEN', 'golosino.felaura@dnscedu.onmicrosoft.com', NULL, '$2y$12$zl9iwIuIrNLpnY5S5irdm.vogx5yjbPfnpXRPTRfq6Gx/C/BKlX9y', NULL, NULL, NULL, 'voter', NULL, '2025-12-07 23:22:11', '2025-12-07 23:22:37', 'voters/PteGLWfSAlOWKUQJkwtL1GsmaKrCwP5lzWAvktUW.jpg'),
(18, 'BATILONG, KIMBIE JARO', 'batilong.kimbie@dnscedu.onmicrosoft.com', NULL, '$2y$12$iYaAiHwsvKlFdqSYnjfhBuQ.eFYi4lx5TYhi1nAOD9Dr9mab0.OGO', NULL, NULL, NULL, 'voter', NULL, '2025-12-07 23:23:43', '2025-12-07 23:24:09', 'voters/59aitdSeVrtciRoMThefnWiCuuQzFwLj36CRFf1k.png'),
(49, 'TUMAGAN, LIEZEL NERI', 'tumagan.liezel@dnscedu.onmicrosoft.com', NULL, '$2y$12$pollmFUbhApFSS.7hEC4b.iEOlG75dCn5V0FLoCBhX.jDC38PP89W', NULL, NULL, NULL, 'voter', NULL, '2025-12-09 20:38:14', '2025-12-09 20:38:55', 'voters/yOhtfd5XwwsdecDevpjozCuN8Qf90EfWokv11mMZ.jpg'),
(50, 'enji', 'nosleenejayneorio@gmail.com', NULL, '$2y$12$PvIbsOBkmAifj08JbF6s.O6Hdsk6ft2bxA.SgOKF8LfFsk3DOh0vG', NULL, NULL, NULL, 'candidate', NULL, '2025-12-10 18:19:29', '2025-12-10 18:19:29', NULL),
(51, 'Froyd Carbajosa', 'froydzkie09@gmail.com', NULL, '$2y$12$yo3SM3y.NzOSTZqxQfFBguSFFdLHsUIh9g02DFR0/H6ZqYwYMRlc2', NULL, NULL, NULL, 'candidate', NULL, '2025-12-10 18:22:41', '2025-12-10 18:22:41', NULL),
(52, 'Froyd Carbajos', 'carbajosa.froyd@dnscedu.onmicrosoft.com', NULL, '$2y$12$qLeZDWs9F53gDYt536mUXefQXOnnOCRLQVHW8YrLkO/3vgHBpe4UC', NULL, NULL, NULL, 'voter', NULL, '2025-12-10 18:25:29', '2025-12-10 18:27:13', 'voters/EZ5iUsXy9WLWfdZBDU5oKKKLCwmI4UuuohpXeTap.jpg');

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
(6, 17, '2024-00388', 'BSIT', '2', 'B', 0, '2025-12-07 23:22:11', '2025-12-07 23:22:11'),
(7, 18, '2024-00558', 'BSIT', '2', 'B', 0, '2025-12-07 23:23:43', '2025-12-07 23:23:43'),
(10, 49, '2024-00665', 'BSIT', '2', 'B', 0, '2025-12-09 20:38:14', '2025-12-09 20:38:14'),
(11, 52, '2024-00633', 'BSIT', '2', 'B', 0, '2025-12-10 18:25:29', '2025-12-10 18:25:29');

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
(76, 52, 12, 38, 20, '2025-12-10 18:28:38', '2025-12-10 18:28:38'),
(77, 17, 12, 37, 20, '2025-12-10 18:29:39', '2025-12-10 18:29:39'),
(78, 18, 12, 37, 20, '2025-12-10 18:30:08', '2025-12-10 18:30:08');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `approved_students`
--
ALTER TABLE `approved_students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `voter_profiles`
--
ALTER TABLE `voter_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

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
