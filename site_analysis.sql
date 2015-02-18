-- phpMyAdmin SQL Dump
-- version 4.2.6deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 17, 2015 at 11:55 PM
-- Server version: 5.5.40-0ubuntu1
-- PHP Version: 5.5.12-2ubuntu4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `site_analysis`
--
CREATE DATABASE IF NOT EXISTS `site_analysis` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `site_analysis`;

-- --------------------------------------------------------

--
-- Table structure for table `algorithms`
--

CREATE TABLE IF NOT EXISTS `algorithms` (
  `id` int(3) NOT NULL,
  `user_id` int(3) NOT NULL,
  `title` text NOT NULL,
  `is_public` int(1) NOT NULL DEFAULT '0',
  `config` text NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `crawler_agents`
--

CREATE TABLE IF NOT EXISTS `crawler_agents` (
  `id` int(3) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `crawler_config`
--

CREATE TABLE IF NOT EXISTS `crawler_config` (
  `id` int(3) NOT NULL,
  `crawler_path` text NOT NULL,
  `config` text NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `domains_to_crawl`
--

CREATE TABLE IF NOT EXISTS `domains_to_crawl` (
  `idx` int(11) NOT NULL,
  `DomainURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `domain_rankings`
--

CREATE TABLE IF NOT EXISTS `domain_rankings` (
  `id` int(20) NOT NULL,
  `DomainURLIDX` int(20) NOT NULL,
  `DomainURL` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Rank` int(5) NOT NULL,
  `Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `google_pages`
--

CREATE TABLE IF NOT EXISTS `google_pages` (
  `id` int(11) NOT NULL,
  `keyword` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `page_idx` int(11) NOT NULL,
  `page_url` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `page_content` longtext COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keyword_rankings`
--

CREATE TABLE IF NOT EXISTS `keyword_rankings` (
  `id` int(20) unsigned NOT NULL,
  `DomainURLIDX` int(20) NOT NULL,
  `DomainURL` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `PageURL` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Keyword` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Rank` int(11) NOT NULL,
  `Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lists`
--

CREATE TABLE IF NOT EXISTS `lists` (
  `id` int(20) NOT NULL,
  `user_id` int(3) NOT NULL,
  `ListURL` text COLLATE utf8_unicode_ci NOT NULL,
  `Name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `cron_processed` tinyint(4) NOT NULL,
  `pages_connected` text COLLATE utf8_unicode_ci,
  `ListType` varchar(10) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_action`
--

CREATE TABLE IF NOT EXISTS `log_action` (
  `id` int(255) NOT NULL,
  `user_id` varchar(99) COLLATE utf8_unicode_ci NOT NULL,
  `user_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `user_role` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `page` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `page_action` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `action_params` text COLLATE utf8_unicode_ci,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_campaign`
--

CREATE TABLE IF NOT EXISTS `master_campaign` (
  `id` int(20) NOT NULL,
  `Name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `CampaignType` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `unique_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages_to_campaign`
--

CREATE TABLE IF NOT EXISTS `pages_to_campaign` (
  `id` int(11) unsigned NOT NULL,
  `unique_id` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `campaign_url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `main_url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `url_ref` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `DomainURLIDX` int(11) NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `keywords` text COLLATE utf8_unicode_ci,
  `achor_text` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `keywords_for_analytics` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `html_embed` text COLLATE utf8_unicode_ci,
  `video_url` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_url` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_kw_info`
--

CREATE TABLE IF NOT EXISTS `page_kw_info` (
  `id` int(11) NOT NULL,
  `DomainURLIDX` int(11) NOT NULL,
  `Keyword` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `KWCntOfWords` int(11) NOT NULL,
  `KWOccurences` int(11) NOT NULL,
  `PageURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `KWSPositive` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `KWDensity` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `DomainURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `KWSentiment` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `KWSNegative` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_link_info`
--

CREATE TABLE IF NOT EXISTS `page_link_info` (
  `id` int(11) NOT NULL,
  `DomainURLIDX` int(11) NOT NULL,
  `InOrOutLink` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `DomainURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `PageURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `Link` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_main_info`
--

CREATE TABLE IF NOT EXISTS `page_main_info` (
  `id` int(11) NOT NULL,
  `PageURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `DomainURLIDX` int(11) NOT NULL,
  `DomainURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `href` text COLLATE utf8_unicode_ci,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `link_limit` int(11) NOT NULL DEFAULT '1',
  `is_301` tinyint(4) NOT NULL DEFAULT '0',
  `parsed_status` int(1) DEFAULT '0',
  `api_data_status` int(1) NOT NULL DEFAULT '0',
  `proxy_data_status` int(1) NOT NULL DEFAULT '0',
  `phantom_data_status` int(1) NOT NULL DEFAULT '0',
  `depth` int(3) NOT NULL DEFAULT '0',
  `page_title` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content_language` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `external_links` int(6) NOT NULL,
  `internal_links` int(6) NOT NULL,
  `no_follow_links` int(6) NOT NULL,
  `follow_links` int(6) NOT NULL,
  `h1` int(6) NOT NULL,
  `h2` int(6) NOT NULL,
  `h3` int(6) NOT NULL,
  `h4` int(6) NOT NULL,
  `h5` int(6) NOT NULL,
  `h6` int(6) NOT NULL,
  `http_code` int(5) NOT NULL,
  `charset` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `load_time` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `page_weight` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `indexed_bing` int(11) DEFAULT NULL,
  `indexed_google` int(11) DEFAULT NULL,
  `server_config` text COLLATE utf8_unicode_ci NOT NULL,
  `total_back_links` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sentimental_positive` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sentimental_negative` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sentimental_type` int(1) DEFAULT NULL,
  `fb_shares` int(12) DEFAULT NULL,
  `fb_comments` int(12) DEFAULT NULL,
  `fb_likes` int(12) DEFAULT NULL,
  `tweeter` int(12) DEFAULT NULL,
  `google_plus` int(12) DEFAULT NULL,
  `google_rank` int(2) DEFAULT NULL,
  `completed_algos` varchar(1024) COLLATE utf8_unicode_ci DEFAULT '[]'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_main_info_body`
--

CREATE TABLE IF NOT EXISTS `page_main_info_body` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_main_info_headings`
--

CREATE TABLE IF NOT EXISTS `page_main_info_headings` (
  `id` int(9) NOT NULL,
  `page_id` int(9) NOT NULL,
  `heading_text` text NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `page_main_info_points`
--

CREATE TABLE IF NOT EXISTS `page_main_info_points` (
  `id` int(9) NOT NULL,
  `page_id` int(9) NOT NULL,
  `algo_id` int(9) NOT NULL,
  `points` varchar(15) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `proxies_list`
--

CREATE TABLE IF NOT EXISTS `proxies_list` (
  `idx` int(11) NOT NULL,
  `ProxyIP` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ProxyPort` int(11) NOT NULL,
  `user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_xmlmap`
--

CREATE TABLE IF NOT EXISTS `site_xmlmap` (
  `id` int(10) unsigned NOT NULL,
  `DomainURL` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `URL` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status_domain`
--

CREATE TABLE IF NOT EXISTS `status_domain` (
  `user_id` int(3) NOT NULL DEFAULT '0',
  `DomainURLIDX` int(11) NOT NULL,
  `project_title` text COLLATE utf8_unicode_ci,
  `Status` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DomainURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `domain_name` text COLLATE utf8_unicode_ci,
  `registration_date` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `server_ip` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `server_location` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hosting_company` text COLLATE utf8_unicode_ci,
  `robots_file` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status_page_links`
--

CREATE TABLE IF NOT EXISTS `status_page_links` (
  `id` int(11) NOT NULL,
  `DomainURLIDX` int(11) NOT NULL,
  `Status` int(11) NOT NULL,
  `DomainURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `Link` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `track_visitors`
--

CREATE TABLE IF NOT EXISTS `track_visitors` (
  `id` int(20) unsigned NOT NULL,
  `ip` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `URL` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `DomainURLIDX` int(20) DEFAULT NULL,
  `DateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Date` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL,
  `username` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `email` text COLLATE utf8_unicode_ci NOT NULL,
  `limits` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `pages` text COLLATE utf8_unicode_ci,
  `editing` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `domains` text COLLATE utf8_unicode_ci
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_browsers`
--

CREATE TABLE IF NOT EXISTS `_sitemap_browsers` (
  `id` int(3) NOT NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_cron_config`
--

CREATE TABLE IF NOT EXISTS `_sitemap_cron_config` (
  `id` int(3) NOT NULL,
  `hours` int(3) NOT NULL,
  `seconds` int(3) NOT NULL,
  `current_job` text COLLATE utf8_unicode_ci NOT NULL,
  `nodejs_server` text COLLATE utf8_unicode_ci NOT NULL,
  `cronjob_server` text COLLATE utf8_unicode_ci NOT NULL,
  `path_to_process_php` text COLLATE utf8_unicode_ci NOT NULL,
  `path_to_process_phantom_php` text COLLATE utf8_unicode_ci NOT NULL,
  `browser` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_domain_info`
--

CREATE TABLE IF NOT EXISTS `_sitemap_domain_info` (
  `id` int(11) NOT NULL,
  `idx` int(3) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `domain_name` text COLLATE utf8_unicode_ci NOT NULL,
  `registration_date` text COLLATE utf8_unicode_ci,
  `project_url` text COLLATE utf8_unicode_ci NOT NULL,
  `google_rank` int(2) DEFAULT NULL,
  `server_ip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `server_location` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hosting_company` text COLLATE utf8_unicode_ci,
  `report_file` text COLLATE utf8_unicode_ci,
  `create_report` tinyint(1) NOT NULL DEFAULT '0',
  `robots_file` text COLLATE utf8_unicode_ci,
  `config` text COLLATE utf8_unicode_ci,
  `project_title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_links`
--

CREATE TABLE IF NOT EXISTS `_sitemap_links` (
  `id` int(11) NOT NULL,
  `domain_id` int(11) NOT NULL,
  `page_url` text COLLATE utf8_unicode_ci NOT NULL,
  `parsed_status` int(1) NOT NULL DEFAULT '0',
  `api_data_status` int(1) NOT NULL DEFAULT '0',
  `proxy_data_status` int(1) NOT NULL DEFAULT '0',
  `depth` int(3) DEFAULT '0',
  `href` text COLLATE utf8_unicode_ci
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_links_external`
--

CREATE TABLE IF NOT EXISTS `_sitemap_links_external` (
  `id` int(3) NOT NULL,
  `link_id` int(3) NOT NULL,
  `href` text COLLATE utf8_unicode_ci NOT NULL,
  `href_text` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_links_headers`
--

CREATE TABLE IF NOT EXISTS `_sitemap_links_headers` (
  `id` int(11) NOT NULL,
  `link_id` int(11) NOT NULL,
  `cache_control` text COLLATE utf8_unicode_ci NOT NULL,
  `pragma` text COLLATE utf8_unicode_ci NOT NULL,
  `content_language` text COLLATE utf8_unicode_ci NOT NULL,
  `content_type` text COLLATE utf8_unicode_ci NOT NULL,
  `expires` text COLLATE utf8_unicode_ci NOT NULL,
  `last_modified` text COLLATE utf8_unicode_ci NOT NULL,
  `server` text COLLATE utf8_unicode_ci NOT NULL,
  `vary` text COLLATE utf8_unicode_ci NOT NULL,
  `served_by` text COLLATE utf8_unicode_ci NOT NULL,
  `x_cache` text COLLATE utf8_unicode_ci NOT NULL,
  `x_cache_backend` text COLLATE utf8_unicode_ci NOT NULL,
  `x_varnish` text COLLATE utf8_unicode_ci NOT NULL,
  `date` text COLLATE utf8_unicode_ci NOT NULL,
  `transfer_encoding` text COLLATE utf8_unicode_ci NOT NULL,
  `connection` text COLLATE utf8_unicode_ci NOT NULL,
  `content_length` text COLLATE utf8_unicode_ci NOT NULL,
  `x_wr_modification` text COLLATE utf8_unicode_ci NOT NULL,
  `etag` text COLLATE utf8_unicode_ci NOT NULL,
  `x_backend` text COLLATE utf8_unicode_ci NOT NULL,
  `age` text COLLATE utf8_unicode_ci NOT NULL,
  `via` text COLLATE utf8_unicode_ci NOT NULL,
  `x_cache_saintmode` text COLLATE utf8_unicode_ci NOT NULL,
  `x_redirect_status` text COLLATE utf8_unicode_ci NOT NULL,
  `accept_ranges` text COLLATE utf8_unicode_ci NOT NULL,
  `status` text COLLATE utf8_unicode_ci NOT NULL,
  `x_powered_by` text COLLATE utf8_unicode_ci NOT NULL,
  `x_rack_cache` text COLLATE utf8_unicode_ci NOT NULL,
  `x_request_id` text COLLATE utf8_unicode_ci NOT NULL,
  `x_runtime` text COLLATE utf8_unicode_ci NOT NULL,
  `x_ua_compatible` text COLLATE utf8_unicode_ci NOT NULL,
  `mime_version` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_links_info`
--

CREATE TABLE IF NOT EXISTS `_sitemap_links_info` (
  `id` int(11) NOT NULL,
  `link_id` int(11) NOT NULL,
  `page_title` text COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `content_language` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `density` text COLLATE utf8_unicode_ci NOT NULL,
  `external_links` int(5) NOT NULL,
  `internal_links` int(5) NOT NULL,
  `follow_links` int(5) NOT NULL,
  `no_follow_links` int(5) NOT NULL,
  `h1` int(3) NOT NULL,
  `h2` int(3) NOT NULL,
  `h3` int(3) NOT NULL,
  `h4` int(3) NOT NULL,
  `h5` int(3) NOT NULL,
  `h6` int(3) NOT NULL,
  `google_rank` int(2) DEFAULT NULL,
  `indexed_google` int(1) NOT NULL DEFAULT '0',
  `indexed_bing` int(1) NOT NULL DEFAULT '0',
  `load_time` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `page_weight` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `http_code` int(3) NOT NULL,
  `charset` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `page_trackers` text COLLATE utf8_unicode_ci NOT NULL,
  `cached` int(1) NOT NULL,
  `server_config` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `sentimental` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `positive` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `negative` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `google_plus` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tweeter` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fb_shares` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fb_likes` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fb_comments` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_links_internal`
--

CREATE TABLE IF NOT EXISTS `_sitemap_links_internal` (
  `id` int(3) NOT NULL,
  `link_id` int(3) NOT NULL,
  `href` text COLLATE utf8_unicode_ci NOT NULL,
  `href_text` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_links_social`
--

CREATE TABLE IF NOT EXISTS `_sitemap_links_social` (
  `id` int(11) NOT NULL,
  `link_id` int(11) NOT NULL,
  `googlep` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `fb_shares` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `fb_likes` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `fb_comments` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `twitter` varchar(10) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_user_history`
--

CREATE TABLE IF NOT EXISTS `_sitemap_user_history` (
  `id` int(11) NOT NULL,
  `user_id` int(3) NOT NULL,
  `domain_id` int(3) NOT NULL,
  `action_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `entry_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `density_result` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_user_words`
--

CREATE TABLE IF NOT EXISTS `_sitemap_user_words` (
  `id` int(11) NOT NULL,
  `history_id` int(11) NOT NULL,
  `words` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `algorithms`
--
ALTER TABLE `algorithms`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `crawler_agents`
--
ALTER TABLE `crawler_agents`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `crawler_config`
--
ALTER TABLE `crawler_config`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `domains_to_crawl`
--
ALTER TABLE `domains_to_crawl`
ADD PRIMARY KEY (`idx`);

--
-- Indexes for table `domain_rankings`
--
ALTER TABLE `domain_rankings`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `google_pages`
--
ALTER TABLE `google_pages`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `keyword_rankings`
--
ALTER TABLE `keyword_rankings`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lists`
--
ALTER TABLE `lists`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_action`
--
ALTER TABLE `log_action`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_campaign`
--
ALTER TABLE `master_campaign`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages_to_campaign`
--
ALTER TABLE `pages_to_campaign`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_kw_info`
--
ALTER TABLE `page_kw_info`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_link_info`
--
ALTER TABLE `page_link_info`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_main_info`
--
ALTER TABLE `page_main_info`
ADD PRIMARY KEY (`id`), ADD KEY `id` (`id`), ADD KEY `DomainURLIDX` (`DomainURLIDX`), ADD KEY `parsed_status` (`parsed_status`), ADD KEY `api_data_status` (`api_data_status`), ADD KEY `proxy_data_status` (`proxy_data_status`), ADD KEY `phantom_data_status` (`phantom_data_status`), ADD KEY `sentimental_type` (`sentimental_type`), ADD KEY `completed_algos` (`completed_algos`(255));

--
-- Indexes for table `page_main_info_body`
--
ALTER TABLE `page_main_info_body`
ADD PRIMARY KEY (`id`), ADD KEY `page_id` (`page_id`), ADD FULLTEXT KEY `body` (`body`);

--
-- Indexes for table `page_main_info_headings`
--
ALTER TABLE `page_main_info_headings`
ADD PRIMARY KEY (`id`), ADD KEY `page_id` (`page_id`);

--
-- Indexes for table `page_main_info_points`
--
ALTER TABLE `page_main_info_points`
ADD PRIMARY KEY (`id`), ADD KEY `page_id` (`page_id`), ADD KEY `algo_id` (`algo_id`), ADD KEY `points` (`points`);

--
-- Indexes for table `proxies_list`
--
ALTER TABLE `proxies_list`
ADD PRIMARY KEY (`idx`), ADD UNIQUE KEY `ProxyIP` (`ProxyIP`);

--
-- Indexes for table `site_xmlmap`
--
ALTER TABLE `site_xmlmap`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_domain`
--
ALTER TABLE `status_domain`
ADD PRIMARY KEY (`DomainURLIDX`), ADD KEY `index1` (`DomainURL`(255),`Status`);

--
-- Indexes for table `status_page_links`
--
ALTER TABLE `status_page_links`
ADD PRIMARY KEY (`id`), ADD KEY `index1` (`DomainURL`(255),`Link`(255),`Status`);

--
-- Indexes for table `track_visitors`
--
ALTER TABLE `track_visitors`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `_sitemap_browsers`
--
ALTER TABLE `_sitemap_browsers`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `_sitemap_cron_config`
--
ALTER TABLE `_sitemap_cron_config`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `_sitemap_domain_info`
--
ALTER TABLE `_sitemap_domain_info`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `_sitemap_links`
--
ALTER TABLE `_sitemap_links`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `_sitemap_links_external`
--
ALTER TABLE `_sitemap_links_external`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `_sitemap_links_headers`
--
ALTER TABLE `_sitemap_links_headers`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`), ADD UNIQUE KEY `id_2` (`id`), ADD UNIQUE KEY `link_id` (`link_id`), ADD UNIQUE KEY `id_3` (`id`), ADD UNIQUE KEY `link_id_2` (`link_id`);

--
-- Indexes for table `_sitemap_links_info`
--
ALTER TABLE `_sitemap_links_info`
ADD PRIMARY KEY (`link_id`), ADD UNIQUE KEY `id` (`id`), ADD UNIQUE KEY `link_id` (`link_id`);

--
-- Indexes for table `_sitemap_links_internal`
--
ALTER TABLE `_sitemap_links_internal`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `_sitemap_links_social`
--
ALTER TABLE `_sitemap_links_social`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`), ADD UNIQUE KEY `link_id` (`link_id`);

--
-- Indexes for table `_sitemap_user_history`
--
ALTER TABLE `_sitemap_user_history`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `_sitemap_user_words`
--
ALTER TABLE `_sitemap_user_words`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `algorithms`
--
ALTER TABLE `algorithms`
MODIFY `id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `crawler_agents`
--
ALTER TABLE `crawler_agents`
MODIFY `id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=75;
--
-- AUTO_INCREMENT for table `crawler_config`
--
ALTER TABLE `crawler_config`
MODIFY `id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `domain_rankings`
--
ALTER TABLE `domain_rankings`
MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `google_pages`
--
ALTER TABLE `google_pages`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9849;
--
-- AUTO_INCREMENT for table `keyword_rankings`
--
ALTER TABLE `keyword_rankings`
MODIFY `id` int(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `lists`
--
ALTER TABLE `lists`
MODIFY `id` int(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=54;
--
-- AUTO_INCREMENT for table `log_action`
--
ALTER TABLE `log_action`
MODIFY `id` int(255) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=379;
--
-- AUTO_INCREMENT for table `master_campaign`
--
ALTER TABLE `master_campaign`
MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pages_to_campaign`
--
ALTER TABLE `pages_to_campaign`
MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `page_kw_info`
--
ALTER TABLE `page_kw_info`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `page_link_info`
--
ALTER TABLE `page_link_info`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `page_main_info`
--
ALTER TABLE `page_main_info`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=607765;
--
-- AUTO_INCREMENT for table `page_main_info_body`
--
ALTER TABLE `page_main_info_body`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=277090;
--
-- AUTO_INCREMENT for table `page_main_info_headings`
--
ALTER TABLE `page_main_info_headings`
MODIFY `id` int(9) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=277070;
--
-- AUTO_INCREMENT for table `page_main_info_points`
--
ALTER TABLE `page_main_info_points`
MODIFY `id` int(9) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2510463;
--
-- AUTO_INCREMENT for table `proxies_list`
--
ALTER TABLE `proxies_list`
MODIFY `idx` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=51;
--
-- AUTO_INCREMENT for table `site_xmlmap`
--
ALTER TABLE `site_xmlmap`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `status_domain`
--
ALTER TABLE `status_domain`
MODIFY `DomainURLIDX` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `status_page_links`
--
ALTER TABLE `status_page_links`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `track_visitors`
--
ALTER TABLE `track_visitors`
MODIFY `id` int(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=63;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=55;
--
-- AUTO_INCREMENT for table `_sitemap_browsers`
--
ALTER TABLE `_sitemap_browsers`
MODIFY `id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=75;
--
-- AUTO_INCREMENT for table `_sitemap_cron_config`
--
ALTER TABLE `_sitemap_cron_config`
MODIFY `id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `_sitemap_domain_info`
--
ALTER TABLE `_sitemap_domain_info`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `_sitemap_links`
--
ALTER TABLE `_sitemap_links`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3794;
--
-- AUTO_INCREMENT for table `_sitemap_links_external`
--
ALTER TABLE `_sitemap_links_external`
MODIFY `id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2218651;
--
-- AUTO_INCREMENT for table `_sitemap_links_headers`
--
ALTER TABLE `_sitemap_links_headers`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=101947;
--
-- AUTO_INCREMENT for table `_sitemap_links_info`
--
ALTER TABLE `_sitemap_links_info`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1950;
--
-- AUTO_INCREMENT for table `_sitemap_links_internal`
--
ALTER TABLE `_sitemap_links_internal`
MODIFY `id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12412279;
--
-- AUTO_INCREMENT for table `_sitemap_links_social`
--
ALTER TABLE `_sitemap_links_social`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=101947;
--
-- AUTO_INCREMENT for table `_sitemap_user_history`
--
ALTER TABLE `_sitemap_user_history`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=45;
--
-- AUTO_INCREMENT for table `_sitemap_user_words`
--
ALTER TABLE `_sitemap_user_words`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
