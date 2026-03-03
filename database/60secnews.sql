-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 02, 2026 at 02:42 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `60secnews`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `featured_image_id` int(11) DEFAULT NULL,
  `status` enum('draft','pending','published','embargoed','trashed') DEFAULT 'draft',
  `is_breaking` tinyint(1) DEFAULT 0,
  `is_pinned` tinyint(1) DEFAULT 0,
  `fact_checked` tinyint(1) DEFAULT 0,
  `publish_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `slug`, `summary`, `content`, `author_id`, `category_id`, `featured_image_id`, `status`, `is_breaking`, `is_pinned`, `fact_checked`, `publish_at`, `created_at`, `updated_at`) VALUES
(1, 'PHP 8.3 Release Highlights', 'php-8-3-release-highlights', 'The latest PHP 8.3 update brings major performance boosts and new functions.', '\r\n<br>\r\n&lt;br&gt;<br>\r\n&amp;lt;br&amp;gt;&lt;br&gt;<br>\r\nPHP 8.3 has been released with performance optimizations and new features like json_validate. These enhancements aim to improve developer productivity and application speed. This is a short article to test the platform.', 1, 3, 4, 'published', 1, 0, 0, '2026-02-27 18:13:00', '2026-02-27 18:12:14', '2026-02-27 23:03:22'),
(11, 'Global Tech Leaders Announce Groundbreaking Open-Source AI Initiative', 'global-tech-leaders-announce-open-source-ai-initiative', 'Top technology firms team up to release a unified, open-source AI architecture aimed at democratizing development and ensuring ethical alignment.', 'In an unprecedented move for the tech industry, a coalition of the world\'s leading technology firms announced today the creation of the Open AI Federation. This new initiative intends to pool resources, hardware architectures, and proprietary models into a single, unified open-source framework.\n\nHistorically fiercely competitive, these companies have united under growing global pressure regarding AI safety, data privacy, and ethical alignment. The federation\'s primary goal is to democratize advanced AI research while establishing a rigid, transparent standard for model alignment.\n\n\"We recognized that advancing AI safely isn\'t a race we can afford to win alone,\" stated the coalition\'s newly appointed chairperson during the livestreamed press conference. \n\nCritics remain skeptical about how seamlessly these giant corporations can collaborate without monopolistic intent, but supporters herald this as the beginning of a safer, more transparent era of machine learning for developers worldwide.', 1, 14, 6, 'published', 1, 1, 1, '2026-02-28 15:15:02', '2026-02-28 15:15:02', '2026-02-28 15:15:02'),
(12, 'The Future of AI and Machine Learning', 'future-of-ai', 'Artificial intelligence continues to evolve at an unprecedented pace, transforming industries globally.', 'Experts believe that AI will drive the next industrial revolution. From intelligent automation to generative content, the possibilities are endless. However, ethical concerns and regulatory challenges remain a significant hurdle that developers must cross.\n\nAre we prepared for an AI-driven society?', 2, 24, 7, 'published', 0, 0, 0, '2026-02-28 18:46:11', '2026-02-28 18:46:11', '2026-02-28 18:46:11'),
(13, 'Global Markets Rally Amid Positive Economic Data', 'global-markets-rally', 'Stock markets reached record highs today as the latest economic reports showed stronger-than-expected growth.', 'Investors are optimistic following the release of the quarterly economic report. Key sectors such as technology and healthcare spurred the growth, showing a sharp recovery from last year\'s dip. As central banks hold interest rates steady, many anticipate the bull run to continue well into the year.', 3, 24, 8, 'published', 0, 0, 0, '2026-02-28 18:46:11', '2026-02-28 18:46:11', '2026-02-28 18:46:11'),
(14, 'Blockbuster Movie Breaks Box Office Records', 'blockbuster-records', 'The highly anticipated sequel has shattered opening weekend records, crossing the billion-dollar mark globally.', 'Audiences flocked to theaters this weekend. The film has been universally praised for its visual effects and emotional storytelling. Directors and cast members celebrated the historic milestone, thanking fans for their overwhelmingly positive reception. This could mark the beginning of a massive cinematic universe.', 4, 24, 9, 'published', 0, 0, 0, '2026-02-28 18:46:11', '2026-02-28 18:46:11', '2026-02-28 18:46:11'),
(15, 'International Summit Focuses on Climate Change', 'international-climate-summit', 'World leaders gather to discuss urgent measures needed to combat rising global temperatures and carbon emissions.', 'Dozens of nations have pledged to accelerate their transition to renewable energy sources by 2030. The unprecedented agreement highlights the severe risks of ignoring climate warnings over the past decade. Activists are cautiously optimistic but demand strict accountability measures.', 5, 24, 10, 'published', 0, 0, 0, '2026-02-28 18:46:11', '2026-02-28 18:46:11', '2026-02-28 18:46:11'),
(16, 'The Rise of ESG Investing in Modern Business', 'esg-investing-trends', 'Environmental, Social, and Governance (ESG) investing is no longer a niche, becoming a core strategy for modern business leaders.', 'Business news is heavily focusing on the massive shift toward ESG investing. Companies failing to adapt to these new societal expectations are seeing pushback from shareholders.\n\nWith new compliance laws on the horizon, business leaders are scrambling to ensure their supply chains and corporate governance meet the new global standards.', 2, 1, 11, 'published', 0, 0, 0, '2026-02-28 18:51:33', '2026-02-28 18:51:33', '2026-02-28 18:51:33'),
(17, 'Breakthrough in Quantum Computing Unveiled', 'quantum-computing-breakthrough', 'Scientists have successfully stabilized a quantum state at room temperature, a massive leap for processing power.', 'In a stunning announcement, researchers revealed they have overcome one of the biggest hurdles in quantum physics: temperature stability. By isolating qubits using a novel diamond-lattice structure, quantum computers could soon operate outside of deep-freeze environments.\n\nThis scientific milestone paves the way for drug discovery and complex climate modeling.', 3, 4, 12, 'published', 0, 0, 0, '2026-02-28 18:51:33', '2026-02-28 18:51:33', '2026-02-28 18:51:33'),
(18, 'Top 10 Entertainment Shows Streaming This Fall', 'top-10-streaming-shows', 'Get ready for an incredible season of television. Here are the most anticipated entertainment shows hitting streaming platforms.', 'From big-budget sci-fi epics to intimate indie dramas, the streaming wars are heating up this fall. Major platforms are dropping their flagship entertainment shows in hopes of capturing the elusive attention economy.\n\nCritics are already praising several pilots for their groundbreaking cinematography and diverse casting.', 4, 1, 13, 'published', 0, 0, 0, '2026-02-28 18:51:33', '2026-02-28 18:51:33', '2026-02-28 18:51:33'),
(19, 'Behind the Scenes: Inside Award-Winning Shows', 'behind-the-scenes-shows', 'Ever wonder what it takes to produce a hit show? We sit down with showrunners to discuss the creative process.', 'Creating captivating shows requires a delicate balance of pacing, character development, and relentless editing. In our exclusive interview, legendary producers share their secrets on managing writing rooms and directing high-stakes dramatic finales.\n\nIt turns out, the drama off-camera is sometimes just as intense as what ends up on screen.', 5, 9, 14, 'published', 0, 0, 0, '2026-02-28 18:51:33', '2026-02-28 18:51:33', '2026-02-28 18:51:33'),
(20, 'Daily News Brief: Major Events to Watch Today', 'daily-news-briefing', 'Your 60-second summary of the most critical headlines shaping the global news cycle today.', 'In today\'s fast-paced news environment, staying informed is critical. Trade negotiations in Europe have stalled, while markets in Asia showed unexpected resilience morning.\n\nMeanwhile, legislative bodies are preparing to vote on a landmark infrastructure bill that could reshape public transit for decades to come. Stay tuned for live updates.', 1, 11, 15, 'published', 0, 0, 0, '2026-02-28 18:51:33', '2026-02-28 18:51:33', '2026-02-28 18:51:33'),
(21, 'The Emerging Trends in Entertainment for 2026', 'emerging-trends-entertainment-177230591133', 'A comprehensive overview of recent developments, challenges, and the future outlook of entertainment on a global scale.', 'The landscape of entertainment is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of entertainment is more crucial than ever for staying informed in our modern economy.', 5, 5, 16, 'published', 0, 0, 0, '2026-02-28 19:11:51', '2026-02-28 19:11:51', '2026-02-28 19:11:51'),
(22, 'The Emerging Trends in Sports for 2026', 'emerging-trends-sports-177230591958', 'A comprehensive overview of recent developments, challenges, and the future outlook of sports on a global scale.', 'The landscape of sports is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of sports is more crucial than ever for staying informed in our modern economy.', 1, 6, 17, 'published', 0, 0, 0, '2026-02-28 19:11:59', '2026-02-28 19:11:59', '2026-02-28 19:11:59'),
(23, 'The Emerging Trends in Economy for 2026', 'emerging-trends-economy-177230592374', 'A comprehensive overview of recent developments, challenges, and the future outlook of economy on a global scale.', 'The landscape of economy is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of economy is more crucial than ever for staying informed in our modern economy.', 4, 15, 18, 'published', 0, 0, 0, '2026-02-28 19:12:03', '2026-02-28 19:12:03', '2026-02-28 19:12:03'),
(24, 'The Emerging Trends in Agriculture for 2026', 'emerging-trends-agriculture-177230592676', 'A comprehensive overview of recent developments, challenges, and the future outlook of agriculture on a global scale.', 'The landscape of agriculture is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of agriculture is more crucial than ever for staying informed in our modern economy.', 3, 16, 19, 'published', 0, 0, 0, '2026-02-28 19:12:06', '2026-02-28 19:12:06', '2026-02-28 19:12:06'),
(25, 'The Emerging Trends in Industries for 2026', 'emerging-trends-industries-177230592979', 'A comprehensive overview of recent developments, challenges, and the future outlook of industries on a global scale.', 'The landscape of industries is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of industries is more crucial than ever for staying informed in our modern economy.', 5, 17, 20, 'published', 0, 0, 0, '2026-02-28 19:12:09', '2026-02-28 19:12:09', '2026-02-28 19:12:09'),
(26, 'The Emerging Trends in Startup for 2026', 'emerging-trends-startup-177230593214', 'A comprehensive overview of recent developments, challenges, and the future outlook of startup on a global scale.', 'The landscape of startup is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of startup is more crucial than ever for staying informed in our modern economy.', 4, 18, 21, 'published', 0, 0, 0, '2026-02-28 19:12:12', '2026-02-28 19:12:12', '2026-02-28 19:12:12'),
(27, 'The Emerging Trends in Global Economy for 2026', 'emerging-trends-global-economy-177230594320', 'A comprehensive overview of recent developments, challenges, and the future outlook of global economy on a global scale.', 'The landscape of global economy is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of global economy is more crucial than ever for staying informed in our modern economy.', 5, 19, 22, 'published', 0, 0, 0, '2026-02-28 19:12:23', '2026-02-28 19:12:23', '2026-02-28 19:12:23'),
(28, 'The Emerging Trends in Politics for 2026', 'emerging-trends-politics-177230594754', 'A comprehensive overview of recent developments, challenges, and the future outlook of politics on a global scale.', 'The landscape of politics is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of politics is more crucial than ever for staying informed in our modern economy.', 5, 21, 23, 'published', 0, 0, 0, '2026-02-28 19:12:27', '2026-02-28 19:12:27', '2026-02-28 19:12:27'),
(29, 'The Emerging Trends in Governance for 2026', 'emerging-trends-governance-177230595047', 'A comprehensive overview of recent developments, challenges, and the future outlook of governance on a global scale.', 'The landscape of governance is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of governance is more crucial than ever for staying informed in our modern economy.', 4, 22, 24, 'published', 0, 0, 0, '2026-02-28 19:12:30', '2026-02-28 19:12:30', '2026-02-28 19:12:30'),
(30, 'The Emerging Trends in Crime and Justice for 2026', 'emerging-trends-crime-and-justice-177230595342', 'A comprehensive overview of recent developments, challenges, and the future outlook of crime and justice on a global scale.', 'The landscape of crime and justice is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of crime and justice is more crucial than ever for staying informed in our modern economy.', 1, 23, 25, 'published', 0, 0, 0, '2026-02-28 19:12:33', '2026-02-28 19:12:33', '2026-02-28 19:12:33'),
(31, 'The Emerging Trends in Education for 2026', 'emerging-trends-education-177230595646', 'A comprehensive overview of recent developments, challenges, and the future outlook of education on a global scale.', 'The landscape of education is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of education is more crucial than ever for staying informed in our modern economy.', 3, 26, 26, 'published', 0, 0, 0, '2026-02-28 19:12:36', '2026-02-28 19:12:36', '2026-02-28 19:12:36'),
(32, 'The Emerging Trends in Healthcare for 2026', 'emerging-trends-healthcare-177230595935', 'A comprehensive overview of recent developments, challenges, and the future outlook of healthcare on a global scale.', 'The landscape of healthcare is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of healthcare is more crucial than ever for staying informed in our modern economy.', 4, 27, 27, 'published', 0, 0, 0, '2026-02-28 19:12:39', '2026-02-28 19:12:39', '2026-02-28 19:12:39'),
(33, 'The Emerging Trends in Environment for 2026', 'emerging-trends-environment-177230596388', 'A comprehensive overview of recent developments, challenges, and the future outlook of environment on a global scale.', 'The landscape of environment is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of environment is more crucial than ever for staying informed in our modern economy.', 1, 28, 28, 'published', 0, 0, 0, '2026-02-28 19:12:43', '2026-02-28 19:12:43', '2026-02-28 19:12:43'),
(34, 'The Emerging Trends in Industry for 2026', 'emerging-trends-industry-177230596648', 'A comprehensive overview of recent developments, challenges, and the future outlook of industry on a global scale.', 'The landscape of industry is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of industry is more crucial than ever for staying informed in our modern economy.', 3, 33, 29, 'published', 0, 0, 0, '2026-02-28 19:12:46', '2026-02-28 19:12:46', '2026-02-28 19:12:46'),
(35, 'The Emerging Trends in Startups for 2026', 'emerging-trends-startups-177230596988', 'A comprehensive overview of recent developments, challenges, and the future outlook of startups on a global scale.', 'The landscape of startups is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of startups is more crucial than ever for staying informed in our modern economy.', 4, 34, 30, 'published', 0, 0, 0, '2026-02-28 19:12:49', '2026-02-28 19:12:49', '2026-02-28 19:12:49'),
(36, 'The Emerging Trends in Cricket for 2026', 'emerging-trends-cricket-177230597295', 'A comprehensive overview of recent developments, challenges, and the future outlook of cricket on a global scale.', 'The landscape of cricket is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of cricket is more crucial than ever for staying informed in our modern economy.', 1, 37, 31, 'published', 0, 0, 0, '2026-02-28 19:12:52', '2026-02-28 19:12:52', '2026-02-28 19:12:52'),
(37, 'The Emerging Trends in Football for 2026', 'emerging-trends-football-177230597572', 'A comprehensive overview of recent developments, challenges, and the future outlook of football on a global scale.', 'The landscape of football is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of football is more crucial than ever for staying informed in our modern economy.', 5, 38, 32, 'published', 0, 0, 0, '2026-02-28 19:12:55', '2026-02-28 19:12:55', '2026-02-28 19:12:55'),
(38, 'The Emerging Trends in Tennis for 2026', 'emerging-trends-tennis-177230597828', 'A comprehensive overview of recent developments, challenges, and the future outlook of tennis on a global scale.', 'The landscape of tennis is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of tennis is more crucial than ever for staying informed in our modern economy.', 2, 39, 33, 'published', 0, 0, 0, '2026-02-28 19:12:58', '2026-02-28 19:12:58', '2026-02-28 19:12:58'),
(39, 'The Emerging Trends in More Sports for 2026', 'emerging-trends-more-sports-177230598590', 'A comprehensive overview of recent developments, challenges, and the future outlook of more sports on a global scale.', 'The landscape of more sports is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of more sports is more crucial than ever for staying informed in our modern economy.', 5, 40, 34, 'published', 0, 0, 0, '2026-02-28 19:13:05', '2026-02-28 19:13:05', '2026-02-28 19:13:05'),
(40, 'The Emerging Trends in Movies for 2026', 'emerging-trends-movies-177230599261', 'A comprehensive overview of recent developments, challenges, and the future outlook of movies on a global scale.', 'The landscape of movies is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of movies is more crucial than ever for staying informed in our modern economy.', 5, 62, 35, 'published', 0, 0, 0, '2026-02-28 19:13:12', '2026-02-28 19:13:12', '2026-02-28 19:13:12'),
(41, 'The Emerging Trends in Music for 2026', 'emerging-trends-music-177230599550', 'A comprehensive overview of recent developments, challenges, and the future outlook of music on a global scale.', 'The landscape of music is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of music is more crucial than ever for staying informed in our modern economy.', 5, 63, 36, 'published', 0, 0, 0, '2026-02-28 19:13:15', '2026-02-28 19:13:15', '2026-02-28 19:13:15'),
(42, 'The Emerging Trends in Celebrity for 2026', 'emerging-trends-celebrity-177230599841', 'A comprehensive overview of recent developments, challenges, and the future outlook of celebrity on a global scale.', 'The landscape of celebrity is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of celebrity is more crucial than ever for staying informed in our modern economy.', 1, 64, 37, 'published', 0, 0, 0, '2026-02-28 19:13:18', '2026-02-28 19:13:18', '2026-02-28 19:13:18'),
(43, 'The Emerging Trends in Gaming for 2026', 'emerging-trends-gaming-177230600118', 'A comprehensive overview of recent developments, challenges, and the future outlook of gaming on a global scale.', 'The landscape of gaming is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of gaming is more crucial than ever for staying informed in our modern economy.', 2, 65, 38, 'published', 0, 0, 0, '2026-02-28 19:13:21', '2026-02-28 19:13:21', '2026-02-28 19:13:21'),
(44, 'The Emerging Trends in Space for 2026', 'emerging-trends-space-177230600416', 'A comprehensive overview of recent developments, challenges, and the future outlook of space on a global scale.', 'The landscape of space is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of space is more crucial than ever for staying informed in our modern economy.', 1, 67, 39, 'published', 0, 0, 0, '2026-02-28 19:13:24', '2026-02-28 19:13:24', '2026-02-28 19:13:24'),
(45, 'The Emerging Trends in Biology for 2026', 'emerging-trends-biology-177230600730', 'A comprehensive overview of recent developments, challenges, and the future outlook of biology on a global scale.', 'The landscape of biology is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of biology is more crucial than ever for staying informed in our modern economy.', 5, 68, 40, 'published', 0, 0, 0, '2026-02-28 19:13:27', '2026-02-28 19:13:27', '2026-02-28 19:13:27'),
(46, 'The Emerging Trends in Physics for 2026', 'emerging-trends-physics-177230601099', 'A comprehensive overview of recent developments, challenges, and the future outlook of physics on a global scale.', 'The landscape of physics is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of physics is more crucial than ever for staying informed in our modern economy.', 5, 69, 41, 'published', 0, 0, 0, '2026-02-28 19:13:30', '2026-02-28 19:13:30', '2026-02-28 19:13:30'),
(47, 'The Emerging Trends in Research for 2026', 'emerging-trends-research-177230601214', 'A comprehensive overview of recent developments, challenges, and the future outlook of research on a global scale.', 'The landscape of research is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of research is more crucial than ever for staying informed in our modern economy.', 3, 70, 42, 'published', 0, 0, 0, '2026-02-28 19:13:32', '2026-02-28 19:13:32', '2026-02-28 19:13:32'),
(48, 'The Emerging Trends in TV Shows for 2026', 'emerging-trends-tv-shows-177230601564', 'A comprehensive overview of recent developments, challenges, and the future outlook of tv shows on a global scale.', 'The landscape of tv shows is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of tv shows is more crucial than ever for staying informed in our modern economy.', 3, 72, 43, 'published', 0, 0, 0, '2026-02-28 19:13:35', '2026-02-28 19:13:35', '2026-02-28 19:13:35'),
(49, 'The Emerging Trends in Podcasts for 2026', 'emerging-trends-podcasts-177230601835', 'A comprehensive overview of recent developments, challenges, and the future outlook of podcasts on a global scale.', 'The landscape of podcasts is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of podcasts is more crucial than ever for staying informed in our modern economy.', 3, 73, 44, 'published', 0, 0, 0, '2026-02-28 19:13:38', '2026-02-28 19:13:38', '2026-02-28 19:13:38'),
(50, 'The Emerging Trends in Documentaries for 2026', 'emerging-trends-documentaries-177230602125', 'A comprehensive overview of recent developments, challenges, and the future outlook of documentaries on a global scale.', 'The landscape of documentaries is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of documentaries is more crucial than ever for staying informed in our modern economy.', 2, 74, 45, 'published', 0, 0, 0, '2026-02-28 19:13:41', '2026-02-28 19:13:41', '2026-02-28 19:13:41'),
(51, 'The Emerging Trends in AI for 2026', 'emerging-trends-ai-177230602412', 'A comprehensive overview of recent developments, challenges, and the future outlook of ai on a global scale.', 'The landscape of ai is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of ai is more crucial than ever for staying informed in our modern economy.', 1, 76, 46, 'published', 0, 0, 0, '2026-02-28 19:13:44', '2026-02-28 19:13:44', '2026-02-28 19:13:44'),
(52, 'The Emerging Trends in Gadgets for 2026', 'emerging-trends-gadgets-177230602764', 'A comprehensive overview of recent developments, challenges, and the future outlook of gadgets on a global scale.', 'The landscape of gadgets is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of gadgets is more crucial than ever for staying informed in our modern economy.', 3, 77, 47, 'published', 0, 0, 0, '2026-02-28 19:13:47', '2026-02-28 19:13:47', '2026-02-28 19:13:47'),
(53, 'The Emerging Trends in Software for 2026', 'emerging-trends-software-177230603075', 'A comprehensive overview of recent developments, challenges, and the future outlook of software on a global scale.', 'The landscape of software is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of software is more crucial than ever for staying informed in our modern economy.', 5, 78, 48, 'published', 0, 0, 0, '2026-02-28 19:13:50', '2026-02-28 19:13:50', '2026-02-28 19:13:50'),
(54, 'The Emerging Trends in Cybersecurity for 2026', 'emerging-trends-cybersecurity-177230603294', 'A comprehensive overview of recent developments, challenges, and the future outlook of cybersecurity on a global scale.', 'The landscape of cybersecurity is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of cybersecurity is more crucial than ever for staying informed in our modern economy.', 3, 79, 49, 'published', 0, 0, 0, '2026-02-28 19:13:52', '2026-02-28 19:13:52', '2026-02-28 19:13:52'),
(55, 'The Emerging Trends in North America for 2026', 'emerging-trends-north-america-177230604477', 'A comprehensive overview of recent developments, challenges, and the future outlook of north america on a global scale.', 'The landscape of north america is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of north america is more crucial than ever for staying informed in our modern economy.', 4, 81, 50, 'published', 0, 0, 0, '2026-02-28 19:14:04', '2026-02-28 19:14:04', '2026-02-28 19:14:04'),
(56, 'The Emerging Trends in Europe for 2026', 'emerging-trends-europe-177230604751', 'A comprehensive overview of recent developments, challenges, and the future outlook of europe on a global scale.', 'The landscape of europe is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of europe is more crucial than ever for staying informed in our modern economy.', 2, 82, 51, 'published', 0, 0, 0, '2026-02-28 19:14:07', '2026-02-28 19:14:07', '2026-02-28 19:14:07'),
(57, 'The Emerging Trends in Asia for 2026', 'emerging-trends-asia-177230605028', 'A comprehensive overview of recent developments, challenges, and the future outlook of asia on a global scale.', 'The landscape of asia is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\n\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of asia is more crucial than ever for staying informed in our modern economy.', 4, 83, 52, 'published', 0, 0, 0, '2026-02-28 19:14:10', '2026-02-28 19:14:10', '2026-02-28 19:14:10'),
(58, 'The Emerging Trends in Africa for 2026', 'emerging-trends-africa-177230605350', 'A comprehensive overview of recent developments, challenges, and the future outlook of africa on a global scale.', 'The landscape of africa is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\r\n\r\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of africa is more crucial than ever for staying informed in our modern economy.', 2, 84, 53, 'published', 0, 0, 0, '2026-02-28 19:14:00', '2026-02-28 19:14:13', '2026-03-01 15:47:21'),
(59, 'The Emerging Trends in South America for 2026', 'emerging-trends-south-america-177230605692', 'A comprehensive overview of recent developments, challenges, and the future outlook of south america on a global scale.', 'The landscape of south america is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\r\n\r\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of south america is more crucial than ever for staying informed in our modern economy.', 3, 85, 54, 'published', 0, 0, 0, '2026-02-27 19:14:00', '2026-02-28 19:14:16', '2026-03-01 07:41:07'),
(60, 'Jetski Browser Agent Test Article', 'jetski-browser-agent-test-article', 'A test article created by Jetski to verify the article creation process.', 'This is a test story. It is short and concise, strictly adhering to the 150-word limit. We are testing the frontend, backend, and database features of 60SecNews. Everything seems to be working as expected so far. The UI is clean, and the interactions are smooth.', 1, 3, NULL, 'published', 0, 0, 0, '2026-02-28 23:58:52', '2026-02-28 23:57:59', '2026-02-28 23:58:52'),
(61, 'Sohan is now writing', 'sohan-is-now-writing', 'Check date and month when select', 'lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem  lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem  lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem  lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem  lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem  lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem  lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem  lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem  lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem  lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem .', 1, 24, NULL, 'published', 0, 0, 0, '2026-03-01 07:12:00', '2026-03-01 00:04:39', '2026-03-01 07:12:22');

-- --------------------------------------------------------

--
-- Table structure for table `article_versions`
--

CREATE TABLE `article_versions` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `editor_id` int(11) NOT NULL,
  `content_diff` text DEFAULT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_versions`
--

INSERT INTO `article_versions` (`id`, `article_id`, `editor_id`, `content_diff`, `saved_at`) VALUES
(1, 1, 1, '{\"title\":\"PHP 8.3 Release Highlights\",\"content\":\"PHP 8.3 has been released with performance optimizations and new features like json_validate. These enhancements aim to improve developer productivity and application speed. This is a short article to test the platform.\"}', '2026-02-27 18:12:14'),
(2, 1, 1, '{\"title\":\"PHP 8.3 Release Highlights\",\"content\":\"\\r\\n                            PHP 8.3 has been released with performance optimizations and new features like json_validate. These enhancements aim to improve developer productivity and application speed. This is a short article to test the platform.                        \"}', '2026-02-27 18:12:40'),
(3, 1, 1, '{\"title\":\"PHP 8.3 Release Highlights\",\"content\":\"\\r\\n                            <br>\\r\\n                            PHP 8.3 has been released with performance optimizations and new features like json_validate. These enhancements aim to improve developer productivity and application speed. This is a short article to test the platform.                                                \"}', '2026-02-27 18:13:19'),
(4, 1, 1, '{\"title\":\"PHP 8.3 Release Highlights\",\"content\":\"\\r\\n                            <br>\\r\\n                            &lt;br&gt;<br>\\r\\n                            PHP 8.3 has been released with performance optimizations and new features like json_validate. These enhancements aim to improve developer productivity and application speed. This is a short article to test the platform.                                                                        \"}', '2026-02-27 18:14:10'),
(5, 1, 1, '{\"title\":\"PHP 8.3 Release Highlights\",\"content\":\"\\r\\n                            <br>\\r\\n                            &lt;br&gt;<br>\\r\\n                            &amp;lt;br&amp;gt;&lt;br&gt;<br>\\r\\n                            PHP 8.3 has been released with performance optimizations and new features like json_validate. These enhancements aim to improve developer productivity and application speed. This is a short article to test the platform.                                                                                                \"}', '2026-02-27 18:20:22'),
(6, 1, 1, '{\"title\":\"PHP 8.3 Release Highlights\",\"content\":\"\\r\\n<br>\\r\\n&lt;br&gt;<br>\\r\\n&amp;lt;br&amp;gt;&lt;br&gt;<br>\\r\\nPHP 8.3 has been released with performance optimizations and new features like json_validate. These enhancements aim to improve developer productivity and application speed. This is a short article to test the platform.\"}', '2026-02-27 21:48:07'),
(10, 60, 1, '{\"title\":\"Jetski Browser Agent Test Article\",\"content\":\"This is a test story. It is short and concise, strictly adhering to the 150-word limit. We are testing the frontend, backend, and database features of 60SecNews. Everything seems to be working as expected so far. The UI is clean, and the interactions are smooth.\"}', '2026-02-28 23:57:59'),
(11, 61, 1, '{\"title\":\"Sohan is now writing\",\"content\":\"lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem\\u00a0 lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem\\u00a0 lorem lorem lorem lorem lorem lorem\\u00a0lorem lorem lorem lorem lorem lorem lorem lorem lorem\\u00a0 lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem\\u00a0 lorem lorem lorem lorem lorem lorem\\u00a0lorem lorem lorem lorem lorem lorem lorem lorem lorem\\u00a0 lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem\\u00a0 lorem lorem lorem lorem lorem lorem\\u00a0lorem lorem lorem lorem lorem lorem lorem lorem lorem\\u00a0 lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem\\u00a0 lorem lorem lorem lorem lorem lorem\\u00a0lorem lorem lorem lorem lorem lorem lorem lorem lorem\\u00a0 lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem .\"}', '2026-03-01 00:04:39'),
(12, 59, 1, '{\"title\":\"The Emerging Trends in South America for 2026\",\"content\":\"The landscape of south america is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\\r\\n\\r\\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of south america is more crucial than ever for staying informed in our modern economy.\"}', '2026-03-01 07:13:07'),
(13, 58, 1, '{\"title\":\"The Emerging Trends in Africa for 2026\",\"content\":\"The landscape of africa is undergoing unprecedented shifts today. Experts note a steady acceleration in major developments that are affecting professionals, consumers, and lawmakers worldwide.\\r\\n\\r\\nFrom fresh regulatory challenges to groundbreaking innovations, understanding the trajectory of africa is more crucial than ever for staying informed in our modern economy.\"}', '2026-03-01 07:41:43');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `ip_address`, `timestamp`) VALUES
(1, 1, 'login', NULL, NULL, '::1', '2026-02-27 18:07:33'),
(2, 1, 'Created article: PHP 8.3 Release Highlights', 'articles', 1, '::1', '2026-02-27 18:12:14'),
(3, 1, 'Updated article: PHP 8.3 Release Highlights', 'articles', 1, '::1', '2026-02-27 18:12:40'),
(4, 1, 'Updated article: PHP 8.3 Release Highlights', 'articles', 1, '::1', '2026-02-27 18:13:19'),
(5, 1, 'Updated article: PHP 8.3 Release Highlights', 'articles', 1, '::1', '2026-02-27 18:14:10'),
(6, 1, 'Updated article: PHP 8.3 Release Highlights', 'articles', 1, '::1', '2026-02-27 18:20:22'),
(7, 1, 'login', NULL, NULL, '::1', '2026-02-27 21:24:18'),
(8, 1, 'Created category: Sports', 'categories', 6, '::1', '2026-02-27 21:35:08'),
(9, 1, 'logout', NULL, NULL, '::1', '2026-02-27 21:37:21'),
(10, 1, 'login', NULL, NULL, '::1', '2026-02-27 21:45:09'),
(11, 1, 'Updated article: PHP 8.3 Release Highlights', 'articles', 1, '::1', '2026-02-27 21:48:07'),
(12, 1, 'Updated article: PHP 8.3 Release Highlights', 'articles', 1, '::1', '2026-02-27 21:49:28'),
(13, 1, 'Changed user status to active', 'users', 7, '::1', '2026-02-27 21:49:48'),
(14, 1, 'Changed user status to banned', 'users', 7, '::1', '2026-02-27 21:50:26'),
(15, 1, 'Changed user status to active', 'users', 7, '::1', '2026-02-27 21:50:38'),
(16, 1, 'Changed user status to active', 'users', 7, '::1', '2026-02-27 21:51:18'),
(17, 7, 'login', NULL, NULL, '::1', '2026-02-27 21:52:05'),
(18, 1, 'Created article: asfrgdthfgjkj', 'articles', 2, '::1', '2026-02-27 21:54:25'),
(19, 1, 'Created article: asfrgdthfgjkj', 'articles', 9, '::1', '2026-02-27 22:12:40'),
(20, 7, 'logout', NULL, NULL, '::1', '2026-02-27 22:13:53'),
(21, 1, 'login', NULL, NULL, '::1', '2026-02-27 22:14:05'),
(22, 1, 'Updated article: asfrgdthfgjkj', 'articles', 9, '::1', '2026-02-27 22:14:18'),
(23, 1, 'Updated article: asfrgdthfgjkj', 'articles', 2, '::1', '2026-02-27 22:14:39'),
(24, 1, 'logout', NULL, NULL, '::1', '2026-02-27 22:25:24'),
(25, 7, 'login', NULL, NULL, '::1', '2026-02-27 22:25:32'),
(26, 1, 'Updated article: asfrgdthfgjkj', 'articles', 9, '::1', '2026-02-27 22:48:16'),
(27, 1, 'Updated article: PHP 8.3 Release Highlights', 'articles', 1, '::1', '2026-02-27 23:03:14'),
(28, 1, 'Updated article: PHP 8.3 Release Highlights', 'articles', 1, '::1', '2026-02-27 23:03:23'),
(29, 1, 'Created article: rstdryfughj', 'articles', 10, '::1', '2026-02-27 23:04:40'),
(30, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-27 23:48:15'),
(31, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-27 23:48:35'),
(32, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-27 23:50:23'),
(33, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-27 23:50:35'),
(34, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-27 23:50:55'),
(35, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-27 23:51:10'),
(36, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-27 23:51:32'),
(37, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-27 23:56:29'),
(38, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-27 23:56:51'),
(39, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-27 23:57:05'),
(40, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 00:08:00'),
(41, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 00:10:31'),
(42, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 00:10:36'),
(43, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 00:31:37'),
(44, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 00:32:46'),
(45, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:13:58'),
(46, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:14:56'),
(47, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:15:33'),
(48, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:17:15'),
(49, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:17:36'),
(50, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:19:45'),
(51, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:27:42'),
(52, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:32:59'),
(53, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:40:09'),
(54, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:40:17'),
(55, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:41:54'),
(56, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:42:01'),
(57, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:42:03'),
(58, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:42:14'),
(59, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 09:42:17'),
(60, 1, 'login', NULL, NULL, '::1', '2026-02-28 10:20:11'),
(61, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 10:20:31'),
(62, 1, 'Deleted category ID: 12', 'categories', 12, '::1', '2026-02-28 10:53:45'),
(63, 1, 'Deleted category ID: 13', 'categories', 13, '::1', '2026-02-28 10:53:51'),
(64, 1, 'Cleared 2 frontend cache file(s)', 'system', NULL, '::1', '2026-02-28 10:58:39'),
(65, 1, 'Cleared 2 frontend cache file(s)', 'system', NULL, '::1', '2026-02-28 15:10:49'),
(66, 1, 'logout', NULL, NULL, '::1', '2026-02-28 16:14:38'),
(67, 1, 'login', NULL, NULL, '::1', '2026-02-28 16:31:43'),
(68, 1, 'logout', NULL, NULL, '::1', '2026-02-28 16:33:31'),
(69, 7, 'login', NULL, NULL, '::1', '2026-02-28 16:33:50'),
(70, 7, 'logout', NULL, NULL, '::1', '2026-02-28 16:34:05'),
(71, 7, 'login', NULL, NULL, '::1', '2026-02-28 16:39:36'),
(72, 7, 'logout', NULL, NULL, '::1', '2026-02-28 16:46:20'),
(73, 7, 'login', NULL, NULL, '::1', '2026-02-28 17:03:55'),
(74, 7, 'logout', NULL, NULL, '::1', '2026-02-28 17:19:26'),
(75, 1, 'login', NULL, NULL, '::1', '2026-02-28 17:19:51'),
(76, 1, 'Deleted category ID: 2', 'categories', 2, '::1', '2026-02-28 17:20:06'),
(77, 1, 'Deleted article ID 10', 'articles', 10, '::1', '2026-02-28 18:58:32'),
(78, 1, 'Deleted article ID 2', 'articles', 2, '::1', '2026-02-28 19:01:20'),
(79, 1, 'Deleted article ID 9', 'articles', 9, '::1', '2026-02-28 19:01:27'),
(80, 1, 'logout', NULL, NULL, '::1', '2026-02-28 20:30:35'),
(81, 1, 'login', NULL, NULL, '::1', '2026-02-28 20:37:21'),
(82, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 20:38:21'),
(83, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 20:38:25'),
(84, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 20:38:28'),
(85, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 20:38:33'),
(86, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 20:38:40'),
(87, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 20:43:47'),
(88, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 20:44:06'),
(89, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 20:44:09'),
(90, 1, 'Manually cleared frontend cache', 'system', NULL, '::1', '2026-02-28 20:47:38'),
(91, 1, 'Updated system settings', 'config', NULL, '::1', '2026-02-28 20:48:32'),
(92, 1, 'Updated system settings', 'config', NULL, '::1', '2026-02-28 20:49:27'),
(93, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 20:49:58'),
(94, 1, 'logout', NULL, NULL, '::1', '2026-02-28 22:44:17'),
(95, 1, 'login', NULL, NULL, '::1', '2026-02-28 22:49:47'),
(96, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-02-28 22:49:56'),
(97, 1, 'Deleted user ID 8', 'users', 8, '::1', '2026-02-28 23:00:57'),
(98, 1, 'Deleted user ID 9', 'users', 9, '::1', '2026-02-28 23:01:00'),
(99, 1, 'Deleted user ID 10', 'users', 10, '::1', '2026-02-28 23:01:05'),
(100, 1, 'Manually cleared frontend cache', 'system', NULL, '::1', '2026-02-28 23:47:00'),
(101, 1, 'login', NULL, NULL, '::1', '2026-02-28 23:52:52'),
(102, 1, 'Created article: Jetski Browser Agent Test Article', 'articles', 60, '::1', '2026-02-28 23:57:59'),
(103, 1, 'Updated article: Jetski Browser Agent Test Article', 'articles', 60, '::1', '2026-02-28 23:58:52'),
(104, 1, 'Created article: Sohan is now writing', 'articles', 61, '::1', '2026-03-01 00:04:39'),
(105, 1, 'login', NULL, NULL, '::1', '2026-03-01 06:57:20'),
(106, 1, 'logout', NULL, NULL, '::1', '2026-03-01 06:59:00'),
(107, 7, 'login', NULL, NULL, '::1', '2026-03-01 06:59:33'),
(108, 7, 'logout', NULL, NULL, '::1', '2026-03-01 07:00:42'),
(109, 1, 'login', NULL, NULL, '::1', '2026-03-01 07:07:58'),
(110, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 07:08:51'),
(111, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 07:09:20'),
(112, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 07:09:41'),
(113, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 07:10:16'),
(114, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 07:10:24'),
(115, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 07:10:29'),
(116, 1, 'Updated article: Sohan is now writing', 'articles', 61, '::1', '2026-03-01 07:11:52'),
(117, 1, 'Updated article: Sohan is now writing', 'articles', 61, '::1', '2026-03-01 07:12:10'),
(118, 1, 'Updated article: Sohan is now writing', 'articles', 61, '::1', '2026-03-01 07:12:22'),
(119, 1, 'Updated article: The Emerging Trends in South America for 2026', 'articles', 59, '::1', '2026-03-01 07:13:07'),
(120, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 07:13:23'),
(121, 1, 'Manually cleared frontend cache', 'system', NULL, '::1', '2026-03-01 07:15:34'),
(122, 1, 'logout', NULL, NULL, '::1', '2026-03-01 07:20:57'),
(123, 1, 'login', NULL, NULL, '::1', '2026-03-01 07:36:14'),
(124, 1, 'logout', NULL, NULL, '::1', '2026-03-01 07:37:00'),
(125, 1, 'login', NULL, NULL, '::1', '2026-03-01 07:37:56'),
(126, 1, 'Updated article: The Emerging Trends in South America for 2026', 'articles', 59, '::1', '2026-03-01 07:40:38'),
(127, 1, 'Updated article: The Emerging Trends in South America for 2026', 'articles', 59, '::1', '2026-03-01 07:41:07'),
(128, 1, 'Updated article: The Emerging Trends in Africa for 2026', 'articles', 58, '::1', '2026-03-01 07:41:43'),
(129, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 07:42:07'),
(130, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 07:42:11'),
(131, 1, 'login', NULL, NULL, '::1', '2026-03-01 08:16:17'),
(132, 1, 'login', NULL, NULL, '::1', '2026-03-01 13:39:07'),
(133, 1, 'Manually cleared frontend cache', 'system', NULL, '::1', '2026-03-01 13:39:16'),
(134, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 13:39:28'),
(135, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 13:39:40'),
(136, 1, 'login', NULL, NULL, '::1', '2026-03-01 13:43:55'),
(137, 1, 'logout', NULL, NULL, '::1', '2026-03-01 13:44:01'),
(138, 7, 'login', NULL, NULL, '::1', '2026-03-01 13:47:08'),
(139, 7, 'logout', NULL, NULL, '::1', '2026-03-01 13:47:18'),
(140, 1, 'logout', NULL, NULL, '::1', '2026-03-01 15:42:47'),
(141, 1, 'login', NULL, NULL, '::1', '2026-03-01 15:45:57'),
(142, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 15:46:20'),
(143, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 15:46:29'),
(144, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 15:46:42'),
(145, 1, 'Updated theme settings', 'config', NULL, '::1', '2026-03-01 15:46:47'),
(146, 1, 'Updated article: The Emerging Trends in Africa for 2026', 'articles', 58, '::1', '2026-03-01 15:47:21');

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `article_id` int(11) NOT NULL,
  `type` enum('like','bookmark') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookmarks`
--

INSERT INTO `bookmarks` (`id`, `session_id`, `article_id`, `type`, `created_at`) VALUES
(3, '8jv8iq9mi8d4cuqlimd2p32rk6', 11, 'bookmark', '2026-02-28 19:52:11'),
(4, '4f760kv1eit194c18j6v6rc6a2', 43, 'like', '2026-03-01 06:52:24'),
(5, '4f760kv1eit194c18j6v6rc6a2', 43, 'bookmark', '2026-03-01 06:52:26'),
(6, 'ok5n926rhs6ash0rep6478viu0', 11, 'like', '2026-03-01 15:45:19'),
(7, 'ok5n926rhs6ash0rep6478viu0', 11, 'bookmark', '2026-03-01 15:45:22');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `created_at`) VALUES
(1, NULL, 'World', 'world', '2026-02-27 17:37:25'),
(3, NULL, 'Business', 'business', '2026-02-27 17:37:25'),
(4, NULL, 'Science', 'science', '2026-02-27 17:37:25'),
(5, NULL, 'Entertainment', 'entertainment', '2026-02-27 17:37:25'),
(6, NULL, 'Sports', 'sports', '2026-02-27 21:35:08'),
(9, NULL, 'Shows', 'shows', '2026-02-28 10:24:35'),
(11, NULL, 'News', 'news', '2026-02-28 10:24:35'),
(14, NULL, 'Technology', 'technology', '2026-02-28 15:15:02'),
(15, 3, 'Economy', 'economy', '2026-02-28 15:36:54'),
(16, 3, 'Agriculture', 'agriculture', '2026-02-28 15:36:54'),
(17, 3, 'Industries', 'industries', '2026-02-28 15:36:54'),
(18, 3, 'Startup', 'startup', '2026-02-28 15:36:54'),
(19, 3, 'Global Economy', 'global-economy', '2026-02-28 15:36:54'),
(21, 11, 'Politics', 'politics', '2026-02-28 15:47:04'),
(22, 11, 'Governance', 'governance', '2026-02-28 15:47:04'),
(23, 11, 'Crime and Justice', 'crime-and-justice', '2026-02-28 15:47:04'),
(24, 11, 'Accidents and Fires', 'accidents-and-fires', '2026-02-28 15:47:04'),
(26, 11, 'Education', 'education', '2026-02-28 15:47:04'),
(27, 11, 'Healthcare', 'healthcare', '2026-02-28 15:47:04'),
(28, 11, 'Environment', 'environment', '2026-02-28 15:47:04'),
(33, 3, 'Industry', 'industry', '2026-02-28 15:47:04'),
(34, 3, 'Startups', 'startups', '2026-02-28 15:47:04'),
(37, 6, 'Cricket', 'cricket', '2026-02-28 15:47:04'),
(38, 6, 'Football', 'football', '2026-02-28 15:47:04'),
(39, 6, 'Tennis', 'tennis', '2026-02-28 15:47:04'),
(40, 6, 'More Sports', 'more-sports', '2026-02-28 15:47:04'),
(62, 5, 'Movies', 'movies', '2026-02-28 17:25:14'),
(63, 5, 'Music', 'music', '2026-02-28 17:25:14'),
(64, 5, 'Celebrity', 'celebrity', '2026-02-28 17:25:14'),
(65, 5, 'Gaming', 'gaming', '2026-02-28 17:25:14'),
(67, 4, 'Space', 'space', '2026-02-28 17:25:14'),
(68, 4, 'Biology', 'biology', '2026-02-28 17:25:14'),
(69, 4, 'Physics', 'physics', '2026-02-28 17:25:14'),
(70, 4, 'Research', 'research', '2026-02-28 17:25:14'),
(72, 9, 'TV Shows', 'tv-shows', '2026-02-28 17:25:14'),
(73, 9, 'Podcasts', 'podcasts', '2026-02-28 17:25:14'),
(74, 9, 'Documentaries', 'documentaries', '2026-02-28 17:25:14'),
(76, 14, 'AI', 'ai', '2026-02-28 17:25:14'),
(77, 14, 'Gadgets', 'gadgets', '2026-02-28 17:25:14'),
(78, 14, 'Software', 'software', '2026-02-28 17:25:14'),
(79, 14, 'Cybersecurity', 'cybersecurity', '2026-02-28 17:25:14'),
(81, 1, 'North America', 'north-america', '2026-02-28 17:25:14'),
(82, 1, 'Europe', 'europe', '2026-02-28 17:25:14'),
(83, 1, 'Asia', 'asia', '2026-02-28 17:25:14'),
(84, 1, 'Africa', 'africa', '2026-02-28 17:25:14'),
(85, 1, 'South America', 'south-america', '2026-02-28 17:25:14');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_ip` varchar(45) DEFAULT NULL,
  `author_name` varchar(100) DEFAULT NULL,
  `content` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `article_id`, `user_ip`, `author_name`, `content`, `status`, `created_at`) VALUES
(1, 1, '::1', NULL, 'qwdsvfg', 'pending', '2026-02-28 09:21:04'),
(9, 61, '::1', '', 'Good observation', 'approved', '2026-03-01 07:14:45'),
(10, 61, '::1', 'Niloy', 'Good Writing', 'approved', '2026-03-01 07:15:02');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `folder` varchar(100) DEFAULT 'general',
  `alt_text` varchar(255) DEFAULT NULL,
  `uploader_id` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `filename`, `folder`, `alt_text`, `uploader_id`, `uploaded_at`) VALUES
(4, 'img_69a222ba11f02_1772233402.jpeg', 'articles', 'PHP 8.3 Release Highlights', 1, '2026-02-27 23:03:22'),
(6, 'img_69a30676a78e1_1772291702.png', 'articles', 'Futuristic glowing AI brain network', 1, '2026-02-28 15:15:02'),
(7, 'sample_tech.jpg', 'general', 'Sample image', 1, '2026-02-28 18:46:04'),
(8, 'sample_biz.jpg', 'general', 'Sample image', 1, '2026-02-28 18:46:06'),
(9, 'sample_ent.jpg', 'general', 'Sample image', 1, '2026-02-28 18:46:08'),
(10, 'sample_world.jpg', 'general', 'Sample image', 1, '2026-02-28 18:46:11'),
(11, 'sample_biz_news.jpg', 'general', 'Sample image', 1, '2026-02-28 18:51:21'),
(12, 'sample_sci.jpg', 'general', 'Sample image', 1, '2026-02-28 18:51:24'),
(13, 'sample_entshows.jpg', 'general', 'Sample image', 1, '2026-02-28 18:51:27'),
(14, 'sample_shows.jpg', 'general', 'Sample image', 1, '2026-02-28 18:51:31'),
(15, 'sample_news.jpg', 'general', 'Sample image', 1, '2026-02-28 18:51:33'),
(16, 'sample_entertainment.jpg', 'general', 'Image for Entertainment', 1, '2026-02-28 19:11:51'),
(17, 'sample_sports.jpg', 'general', 'Image for Sports', 1, '2026-02-28 19:11:59'),
(18, 'sample_economy.jpg', 'general', 'Image for Economy', 1, '2026-02-28 19:12:03'),
(19, 'sample_agriculture.jpg', 'general', 'Image for Agriculture', 1, '2026-02-28 19:12:06'),
(20, 'sample_industries.jpg', 'general', 'Image for Industries', 1, '2026-02-28 19:12:09'),
(21, 'sample_startup.jpg', 'general', 'Image for Startup', 1, '2026-02-28 19:12:12'),
(22, 'sample_global_economy.jpg', 'general', 'Image for Global Economy', 1, '2026-02-28 19:12:23'),
(23, 'sample_politics.jpg', 'general', 'Image for Politics', 1, '2026-02-28 19:12:27'),
(24, 'sample_governance.jpg', 'general', 'Image for Governance', 1, '2026-02-28 19:12:30'),
(25, 'sample_crime_and_justice.jpg', 'general', 'Image for Crime and Justice', 1, '2026-02-28 19:12:33'),
(26, 'sample_education.jpg', 'general', 'Image for Education', 1, '2026-02-28 19:12:36'),
(27, 'sample_healthcare.jpg', 'general', 'Image for Healthcare', 1, '2026-02-28 19:12:39'),
(28, 'sample_environment.jpg', 'general', 'Image for Environment', 1, '2026-02-28 19:12:43'),
(29, 'sample_industry.jpg', 'general', 'Image for Industry', 1, '2026-02-28 19:12:46'),
(30, 'sample_startups.jpg', 'general', 'Image for Startups', 1, '2026-02-28 19:12:49'),
(31, 'sample_cricket.jpg', 'general', 'Image for Cricket', 1, '2026-02-28 19:12:52'),
(32, 'sample_football.jpg', 'general', 'Image for Football', 1, '2026-02-28 19:12:55'),
(33, 'sample_tennis.jpg', 'general', 'Image for Tennis', 1, '2026-02-28 19:12:58'),
(34, 'sample_more_sports.jpg', 'general', 'Image for More Sports', 1, '2026-02-28 19:13:05'),
(35, 'sample_movies.jpg', 'general', 'Image for Movies', 1, '2026-02-28 19:13:12'),
(36, 'sample_music.jpg', 'general', 'Image for Music', 1, '2026-02-28 19:13:15'),
(37, 'sample_celebrity.jpg', 'general', 'Image for Celebrity', 1, '2026-02-28 19:13:18'),
(38, 'sample_gaming.jpg', 'general', 'Image for Gaming', 1, '2026-02-28 19:13:21'),
(39, 'sample_space.jpg', 'general', 'Image for Space', 1, '2026-02-28 19:13:24'),
(40, 'sample_biology.jpg', 'general', 'Image for Biology', 1, '2026-02-28 19:13:27'),
(41, 'sample_physics.jpg', 'general', 'Image for Physics', 1, '2026-02-28 19:13:30'),
(42, 'sample_research.jpg', 'general', 'Image for Research', 1, '2026-02-28 19:13:32'),
(43, 'sample_tv_shows.jpg', 'general', 'Image for TV Shows', 1, '2026-02-28 19:13:35'),
(44, 'sample_podcasts.jpg', 'general', 'Image for Podcasts', 1, '2026-02-28 19:13:38'),
(45, 'sample_documentaries.jpg', 'general', 'Image for Documentaries', 1, '2026-02-28 19:13:41'),
(46, 'sample_ai.jpg', 'general', 'Image for AI', 1, '2026-02-28 19:13:44'),
(47, 'sample_gadgets.jpg', 'general', 'Image for Gadgets', 1, '2026-02-28 19:13:47'),
(48, 'sample_software.jpg', 'general', 'Image for Software', 1, '2026-02-28 19:13:50'),
(49, 'sample_cybersecurity.jpg', 'general', 'Image for Cybersecurity', 1, '2026-02-28 19:13:52'),
(50, 'sample_north_america.jpg', 'general', 'Image for North America', 1, '2026-02-28 19:14:04'),
(51, 'sample_europe.jpg', 'general', 'Image for Europe', 1, '2026-02-28 19:14:07'),
(52, 'sample_asia.jpg', 'general', 'Image for Asia', 1, '2026-02-28 19:14:10'),
(53, 'sample_africa.jpg', 'general', 'Image for Africa', 1, '2026-02-28 19:14:13'),
(54, 'sample_south_america.jpg', 'general', 'Image for South America', 1, '2026-02-28 19:14:16');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`) VALUES
(1, 'Admin', '2026-02-27 17:37:25'),
(2, 'Publisher', '2026-02-27 17:37:25'),
(3, 'Editor', '2026-02-27 17:37:25'),
(4, 'Writer', '2026-02-27 17:37:25'),
(5, 'Media', '2026-02-27 17:37:25');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_string` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_string`) VALUES
(1, 'create_article'),
(1, 'delete_article'),
(1, 'edit_any_article'),
(1, 'manage_categories'),
(1, 'manage_comments'),
(1, 'manage_roles'),
(1, 'manage_settings'),
(1, 'manage_users'),
(1, 'publish_article'),
(1, 'upload_media'),
(1, 'view_logs'),
(2, 'edit_any_article'),
(2, 'manage_categories'),
(2, 'publish_article'),
(3, 'create_article'),
(3, 'edit_any_article'),
(3, 'manage_comments'),
(4, 'create_article'),
(4, 'edit_own_article'),
(5, 'upload_media');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`id`, `email`, `subscribed_at`) VALUES
(1, 'niloy@gmail.com', '2026-02-27 22:27:27'),
(2, 'reyad96.cse.bu@gmail.com', '2026-02-28 00:33:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `lockout_time` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','banned') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role_id`, `created_at`, `last_login`, `login_attempts`, `lockout_time`, `status`) VALUES
(1, 'sohan', 'admin@60secnews.com', '$2y$10$NUCKu6rDAqMuiMfhGKhC5.AFQJ4FoZK0uFsRke.5m2ArTyIQYXOKq', 1, '2026-02-27 17:37:25', '2026-03-01 15:45:57', 0, NULL, 'active'),
(2, 'publisher_user', 'publisher@60secnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '2026-02-27 17:37:25', NULL, 0, NULL, 'active'),
(3, 'editor_user', 'editor@60secnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, '2026-02-27 17:37:25', NULL, 0, NULL, 'active'),
(4, 'writer_user', 'writer@60secnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, '2026-02-27 17:37:25', NULL, 0, NULL, 'active'),
(5, 'media_user', 'media@60secnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, '2026-02-27 17:37:25', NULL, 0, NULL, 'active'),
(7, 'niloy', 'niloy@gmail.com', '$2y$10$VkU/Um.Rdysn2D47VGhqh.5Wsr8dCTtO8VthghaGz2h0JnPhx1rjm', 4, '2026-02-27 21:43:56', '2026-03-01 13:47:08', 0, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `views`
--

CREATE TABLE `views` (
  `article_id` int(11) NOT NULL,
  `view_date` date NOT NULL,
  `view_count` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `views`
--

INSERT INTO `views` (`article_id`, `view_date`, `view_count`) VALUES
(1, '2026-02-28', 13),
(1, '2026-03-01', 3),
(11, '2026-02-28', 8),
(11, '2026-03-01', 26),
(12, '2026-03-01', 1),
(43, '2026-03-01', 1),
(59, '2026-03-01', 1),
(60, '2026-03-01', 1),
(61, '2026-03-01', 8);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `featured_image_id` (`featured_image_id`);

--
-- Indexes for table `article_versions`
--
ALTER TABLE `article_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `editor_id` (`editor_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bookmark` (`session_id`,`article_id`,`type`),
  ADD KEY `article_id` (`article_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_cat_parent` (`parent_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploader_id` (`uploader_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_string`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `views`
--
ALTER TABLE `views`
  ADD PRIMARY KEY (`article_id`,`view_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `article_versions`
--
ALTER TABLE `article_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `articles_ibfk_3` FOREIGN KEY (`featured_image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `article_versions`
--
ALTER TABLE `article_versions`
  ADD CONSTRAINT `article_versions_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_versions_ibfk_2` FOREIGN KEY (`editor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `views`
--
ALTER TABLE `views`
  ADD CONSTRAINT `views_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
