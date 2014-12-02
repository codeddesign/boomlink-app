-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 02, 2014 at 07:54 AM
-- Server version: 5.5.40-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `site_analysis`
--

-- --------------------------------------------------------

--
-- Table structure for table `algorithms`
--

CREATE TABLE IF NOT EXISTS `algorithms` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `user_id` int(3) NOT NULL,
  `title` text NOT NULL,
  `is_public` int(1) NOT NULL DEFAULT '0',
  `config` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `crawler_agents`
--

CREATE TABLE IF NOT EXISTS `crawler_agents` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=75 ;

-- --------------------------------------------------------

--
-- Table structure for table `crawler_config`
--

CREATE TABLE IF NOT EXISTS `crawler_config` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `crawler_path` text NOT NULL,
  `config` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `domains_to_crawl`
--

CREATE TABLE IF NOT EXISTS `domains_to_crawl` (
  `idx` int(11) NOT NULL,
  `DomainURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `domain_rankings`
--

CREATE TABLE IF NOT EXISTS `domain_rankings` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `DomainURLIDX` int(20) NOT NULL,
  `DomainURL` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Rank` int(5) NOT NULL,
  `Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `google_pages`
--

CREATE TABLE IF NOT EXISTS `google_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `page_idx` int(11) NOT NULL,
  `page_url` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `page_content` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9849 ;

-- --------------------------------------------------------

--
-- Table structure for table `keyword_rankings`
--

CREATE TABLE IF NOT EXISTS `keyword_rankings` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `DomainURLIDX` int(20) NOT NULL,
  `DomainURL` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `PageURL` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Keyword` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Rank` int(11) NOT NULL,
  `Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `lists`
--

CREATE TABLE IF NOT EXISTS `lists` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(3) NOT NULL,
  `ListURL` text COLLATE utf8_unicode_ci NOT NULL,
  `Name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `cron_processed` tinyint(4) NOT NULL,
  `pages_connected` text COLLATE utf8_unicode_ci,
  `ListType` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=53 ;

-- --------------------------------------------------------

--
-- Table structure for table `log_action`
--

CREATE TABLE IF NOT EXISTS `log_action` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(99) COLLATE utf8_unicode_ci NOT NULL,
  `user_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `user_role` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `page` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `page_action` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `action_params` text COLLATE utf8_unicode_ci,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2311 ;

-- --------------------------------------------------------

--
-- Table structure for table `master_campaign`
--

CREATE TABLE IF NOT EXISTS `master_campaign` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `CampaignType` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `unique_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=379 ;

-- --------------------------------------------------------

--
-- Table structure for table `pages_to_campaign`
--

CREATE TABLE IF NOT EXISTS `pages_to_campaign` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
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
  `image_url` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `page_kw_info`
--

CREATE TABLE IF NOT EXISTS `page_kw_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `KWSNegative` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `page_link_info`
--

CREATE TABLE IF NOT EXISTS `page_link_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `DomainURLIDX` int(11) NOT NULL,
  `InOrOutLink` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `DomainURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `PageURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `Link` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `page_main_info`
--

CREATE TABLE IF NOT EXISTS `page_main_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `page_title` text COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
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
  `sentimental` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `positive` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `negative` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fb_shares` int(12) DEFAULT NULL,
  `fb_comments` int(12) DEFAULT NULL,
  `fb_likes` int(12) DEFAULT NULL,
  `tweeter` int(12) DEFAULT NULL,
  `google_plus` int(12) DEFAULT NULL,
  `google_rank` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1631 ;

-- --------------------------------------------------------

--
-- Table structure for table `page_main_info_body`
--

CREATE TABLE IF NOT EXISTS `page_main_info_body` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=532 ;

-- --------------------------------------------------------

--
-- Table structure for table `proxies_list`
--

CREATE TABLE IF NOT EXISTS `proxies_list` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `ProxyIP` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ProxyPort` int(11) NOT NULL,
  `user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`idx`),
  UNIQUE KEY `ProxyIP` (`ProxyIP`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `site_xmlmap`
--

CREATE TABLE IF NOT EXISTS `site_xmlmap` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `DomainURL` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `URL` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `status_domain`
--

CREATE TABLE IF NOT EXISTS `status_domain` (
  `user_id` int(3) NOT NULL DEFAULT '0',
  `DomainURLIDX` int(11) NOT NULL AUTO_INCREMENT,
  `project_title` text COLLATE utf8_unicode_ci,
  `Status` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DomainURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `domain_name` text COLLATE utf8_unicode_ci,
  `registration_date` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `server_ip` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `server_location` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hosting_company` text COLLATE utf8_unicode_ci,
  `robots_file` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`DomainURLIDX`),
  KEY `index1` (`DomainURL`(255),`Status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `status_page_links`
--

CREATE TABLE IF NOT EXISTS `status_page_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `DomainURLIDX` int(11) NOT NULL,
  `Status` int(11) NOT NULL,
  `DomainURL` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `Link` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `index1` (`DomainURL`(255),`Link`(255),`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `track_visitors`
--

CREATE TABLE IF NOT EXISTS `track_visitors` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `URL` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `DomainURLIDX` int(20) DEFAULT NULL,
  `DateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Date` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=63 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `email` text COLLATE utf8_unicode_ci NOT NULL,
  `limits` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `pages` text COLLATE utf8_unicode_ci,
  `editing` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `domains` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=54 ;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_browsers`
--

CREATE TABLE IF NOT EXISTS `_sitemap_browsers` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=75 ;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_cron_config`
--

CREATE TABLE IF NOT EXISTS `_sitemap_cron_config` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `hours` int(3) NOT NULL,
  `seconds` int(3) NOT NULL,
  `current_job` text COLLATE utf8_unicode_ci NOT NULL,
  `nodejs_server` text COLLATE utf8_unicode_ci NOT NULL,
  `cronjob_server` text COLLATE utf8_unicode_ci NOT NULL,
  `path_to_process_php` text COLLATE utf8_unicode_ci NOT NULL,
  `path_to_process_phantom_php` text COLLATE utf8_unicode_ci NOT NULL,
  `browser` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_domain_info`
--

CREATE TABLE IF NOT EXISTS `_sitemap_domain_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_links`
--

CREATE TABLE IF NOT EXISTS `_sitemap_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `page_url` text COLLATE utf8_unicode_ci NOT NULL,
  `parsed_status` int(1) NOT NULL DEFAULT '0',
  `api_data_status` int(1) NOT NULL DEFAULT '0',
  `proxy_data_status` int(1) NOT NULL DEFAULT '0',
  `depth` int(3) DEFAULT '0',
  `href` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3794 ;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_links_external`
--

CREATE TABLE IF NOT EXISTS `_sitemap_links_external` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `link_id` int(3) NOT NULL,
  `href` text COLLATE utf8_unicode_ci NOT NULL,
  `href_text` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2218651 ;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_links_headers`
--

CREATE TABLE IF NOT EXISTS `_sitemap_links_headers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `mime_version` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `id_2` (`id`),
  UNIQUE KEY `link_id` (`link_id`),
  UNIQUE KEY `id_3` (`id`),
  UNIQUE KEY `link_id_2` (`link_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=101947 ;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_links_info`
--

CREATE TABLE IF NOT EXISTS `_sitemap_links_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `fb_comments` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`link_id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `link_id` (`link_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1950 ;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_links_internal`
--

CREATE TABLE IF NOT EXISTS `_sitemap_links_internal` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `link_id` int(3) NOT NULL,
  `href` text COLLATE utf8_unicode_ci NOT NULL,
  `href_text` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12412279 ;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_links_social`
--

CREATE TABLE IF NOT EXISTS `_sitemap_links_social` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_id` int(11) NOT NULL,
  `googlep` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `fb_shares` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `fb_likes` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `fb_comments` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `twitter` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `link_id` (`link_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=101947 ;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_user_history`
--

CREATE TABLE IF NOT EXISTS `_sitemap_user_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(3) NOT NULL,
  `domain_id` int(3) NOT NULL,
  `action_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `entry_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `density_result` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=45 ;

-- --------------------------------------------------------

--
-- Table structure for table `_sitemap_user_words`
--

CREATE TABLE IF NOT EXISTS `_sitemap_user_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `history_id` int(11) NOT NULL,
  `words` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
