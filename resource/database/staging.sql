-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 05, 2024 at 09:34 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tendopia`
--

-- --------------------------------------------------------

--
-- Table structure for table `sw_account_admin`
--

CREATE TABLE `sw_account_admin` (
  `id` int(11) NOT NULL,
  `admin_id` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `web3_address` varchar(255) DEFAULT NULL,
  `nickname` varchar(128) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `tag` varchar(64) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `authenticator` varchar(64) DEFAULT NULL,
  `status` enum('active','inactivated','freezed','suspended') DEFAULT 'active',
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_account_admin`
--

INSERT INTO `sw_account_admin` (`id`, `admin_id`, `created_at`, `updated_at`, `deleted_at`, `web3_address`, `nickname`, `password`, `tag`, `email`, `authenticator`, `status`, `remark`) VALUES
(1, 'E9QMJCW7K23A5QT1', '2024-01-09 14:51:17', '2024-03-05 14:36:38', NULL, '0xBdc76521b93cbF4E1dEf17a8d17a7767A3B85C4c', 'eric', NULL, NULL, NULL, 'web3_address', 'active', NULL),
(2, 'E9QMJCW7K23A5QT5', '2024-01-10 13:13:17', '2024-01-10 13:13:17', NULL, '0xf0E9784EA2B904eCae8aD0a6C18c91Fa9cf57c55', 'david', NULL, NULL, NULL, NULL, 'active', NULL),
(3, 'O14XIXHVHYOJHXMO', '2024-01-23 16:53:42', '2024-01-23 16:53:42', NULL, '0x0e1497245518320e8F089Eb648c8533DB636C696', 'zk', NULL, NULL, NULL, NULL, 'active', NULL),
(4, 'T0OXJT7AXEFB86VE', '2024-01-24 19:03:56', '2024-01-24 19:03:56', NULL, '0xEA6BAE28525bc41624d67B1e5F01Efdcd813419c', 'clement', NULL, NULL, NULL, NULL, 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_account_user`
--

CREATE TABLE `sw_account_user` (
  `id` int(11) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `character` varchar(255) DEFAULT NULL,
  `web3_address` varchar(255) DEFAULT NULL,
  `nickname` varchar(128) DEFAULT NULL,
  `login_id` varchar(128) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `tag` varchar(64) DEFAULT NULL,
  `authenticator` varchar(64) DEFAULT NULL,
  `status` enum('active','inactivated','freezed','suspended') DEFAULT 'active',
  `telegram` varchar(255) DEFAULT NULL,
  `discord` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `google` varchar(255) DEFAULT NULL,
  `telegram_name` varchar(255) DEFAULT NULL,
  `discord_name` varchar(255) DEFAULT NULL,
  `twitter_name` varchar(255) DEFAULT NULL,
  `google_name` varchar(255) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_account_user`
--

INSERT INTO `sw_account_user` (`id`, `user_id`, `created_at`, `updated_at`, `deleted_at`, `avatar`, `character`, `web3_address`, `nickname`, `login_id`, `password`, `tag`, `authenticator`, `status`, `telegram`, `discord`, `twitter`, `google`, `telegram_name`, `discord_name`, `twitter_name`, `google_name`, `remark`) VALUES
(1, '2S5OCULMGFW7DVPY', '2024-02-23 21:18:39', '2024-03-05 15:38:00', NULL, NULL, NULL, '0xBdc76521b93cbF4E1dEf17a8d17a7767A3B85C4c', NULL, NULL, NULL, NULL, 'web3_address', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_admin_permission`
--

CREATE TABLE `sw_admin_permission` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `admin_uid` int(11) DEFAULT 0 COMMENT 'refer to account_admin',
  `role` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_admin_permission`
--

INSERT INTO `sw_admin_permission` (`id`, `created_at`, `updated_at`, `deleted_at`, `admin_uid`, `role`) VALUES
(1, '2023-11-24 17:39:06', '2023-11-24 17:39:09', NULL, 1, 1),
(2, '2023-11-24 17:39:06', '2024-01-10 17:35:33', NULL, 2, 1),
(3, '2023-11-24 17:39:06', '2024-01-10 17:35:33', NULL, 3, 1),
(4, '2023-11-24 17:39:06', '2024-01-10 17:35:33', NULL, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sw_log_admin`
--

CREATE TABLE `sw_log_admin` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `used_at` varchar(50) DEFAULT NULL,
  `admin_uid` int(11) DEFAULT 0 COMMENT 'refer to account_admin',
  `by_admin_uid` int(11) DEFAULT 0 COMMENT 'refer to account_admin',
  `ip` varchar(255) DEFAULT NULL,
  `ref_table` varchar(64) DEFAULT NULL,
  `ref_id` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_log_api`
--

CREATE TABLE `sw_log_api` (
  `id` bigint(20) NOT NULL,
  `sn` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `ref_table` varchar(64) DEFAULT NULL,
  `ref_id` int(11) DEFAULT 0,
  `response` text DEFAULT NULL,
  `by_pass` varchar(128) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_log_cronjob`
--

CREATE TABLE `sw_log_cronjob` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `used_at` varchar(50) DEFAULT NULL,
  `cronjob_code` varchar(128) DEFAULT NULL,
  `info` text DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_log_user`
--

CREATE TABLE `sw_log_user` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `used_at` varchar(50) DEFAULT NULL,
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `by_uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `ip` varchar(255) DEFAULT NULL,
  `ref_table` varchar(64) DEFAULT NULL,
  `ref_id` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_network_sponsor`
--

CREATE TABLE `sw_network_sponsor` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `upline_uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_network_sponsor`
--

INSERT INTO `sw_network_sponsor` (`id`, `created_at`, `updated_at`, `deleted_at`, `uid`, `upline_uid`, `remark`) VALUES
(1, '2024-02-16 02:29:49', '2024-02-16 02:29:53', NULL, 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_permission_template`
--

CREATE TABLE `sw_permission_template` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `template_code` varchar(64) DEFAULT NULL,
  `rule` text DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_permission_template`
--

INSERT INTO `sw_permission_template` (`id`, `deleted_at`, `template_code`, `rule`, `remark`) VALUES
(1, NULL, 'admin', '[\"*\"]', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_permission_warehouse`
--

CREATE TABLE `sw_permission_warehouse` (
  `id` int(11) NOT NULL,
  `code` varchar(128) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `from_site` varchar(128) DEFAULT 'common',
  `path` varchar(255) DEFAULT NULL,
  `action` varchar(128) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_permission_warehouse`
--

INSERT INTO `sw_permission_warehouse` (`id`, `code`, `deleted_at`, `from_site`, `path`, `action`, `remark`) VALUES
(1, 'admin_auth_request@get', NULL, 'admin', '/admin/auth/request', 'GET', NULL),
(2, 'admin_auth_verify@post', NULL, 'admin', '/admin/auth/verify', 'POST', NULL),
(3, 'admin_auth_rule@get', NULL, 'admin', '/admin/auth/rule', 'GET', NULL),
(4, 'admin_auth_logout@post', NULL, 'admin', '/admin/auth/logout', 'POST', NULL),
(5, 'admin_enumlist_list@get', NULL, 'admin', '/admin/enumList/list', 'GET', NULL),
(6, 'admin_permission_warehouse@post', NULL, 'admin', '/admin/permission/warehouse', 'POST', NULL),
(7, 'admin_permission_warehouse_id@put', NULL, 'admin', '/admin/permission/warehouse/{id:\\d+}', 'PUT', NULL),
(8, 'admin_permission_warehouse_id@delete', NULL, 'admin', '/admin/permission/warehouse/{id:\\d+}', 'DELETE', NULL),
(9, 'admin_permission_warehouse_id@get', NULL, 'admin', '/admin/permission/warehouse/{id:\\d+}', 'GET', NULL),
(10, 'admin_permission_warehouse_list@get', NULL, 'admin', '/admin/permission/warehouse/list', 'GET', NULL),
(11, 'admin_permission_warehouse@get', NULL, 'admin', '/admin/permission/warehouse', 'GET', NULL),
(12, 'admin_account_user@post', NULL, 'admin', '/admin/account/user', 'POST', NULL),
(13, 'admin_account_user_id@put', NULL, 'admin', '/admin/account/user/{id:\\d+}', 'PUT', NULL),
(14, 'admin_account_user_id@delete', NULL, 'admin', '/admin/account/user/{id:\\d+}', 'DELETE', NULL),
(15, 'admin_account_user_id@get', NULL, 'admin', '/admin/account/user/{id:\\d+}', 'GET', NULL),
(16, 'admin_account_user_list@get', NULL, 'admin', '/admin/account/user/list', 'GET', NULL),
(17, 'admin_account_user@get', NULL, 'admin', '/admin/account/user', 'GET', NULL),
(18, 'admin_account_user_viewbalance_id@get', NULL, 'admin', '/admin/account/user/viewBalance/{id:\\d+}', 'GET', NULL),
(19, 'admin_account_user_addbalance_id@put', NULL, 'admin', '/admin/account/user/addBalance/{id:\\d+}', 'PUT', NULL),
(20, 'admin_account_user_deductbalance_id@put', NULL, 'admin', '/admin/account/user/deductBalance/{id:\\d+}', 'PUT', NULL),
(21, 'admin_account_admin_id@get', NULL, 'admin', '/admin/account/admin/{id:\\d+}', 'GET', NULL),
(22, 'admin_account_admin_list@get', NULL, 'admin', '/admin/account/admin/list', 'GET', NULL),
(23, 'admin_account_admin@get', NULL, 'admin', '/admin/account/admin', 'GET', NULL),
(24, 'admin_account_admin@post', NULL, 'admin', '/admin/account/admin', 'POST', NULL),
(25, 'admin_account_admin_id@put', NULL, 'admin', '/admin/account/admin/{id:\\d+}', 'PUT', NULL),
(26, 'admin_account_admin_id@delete', NULL, 'admin', '/admin/account/admin/{id:\\d+}', 'DELETE', NULL),
(27, 'admin_log_admin_id@get', NULL, 'admin', '/admin/log/admin/{id:\\d+}', 'GET', NULL),
(28, 'admin_log_admin_list@get', NULL, 'admin', '/admin/log/admin/list', 'GET', NULL),
(29, 'admin_log_admin@get', NULL, 'admin', '/admin/log/admin', 'GET', NULL),
(30, 'admin_log_admin@post', NULL, 'admin', '/admin/log/admin', 'POST', NULL),
(31, 'admin_log_admin_id@put', NULL, 'admin', '/admin/log/admin/{id:\\d+}', 'PUT', NULL),
(32, 'admin_log_admin_id@delete', NULL, 'admin', '/admin/log/admin/{id:\\d+}', 'DELETE', NULL),
(33, 'admin_log_api_id@get', NULL, 'admin', '/admin/log/api/{id:\\d+}', 'GET', NULL),
(34, 'admin_log_api_list@get', NULL, 'admin', '/admin/log/api/list', 'GET', NULL),
(35, 'admin_log_api@get', NULL, 'admin', '/admin/log/api', 'GET', NULL),
(36, 'admin_log_api@post', NULL, 'admin', '/admin/log/api', 'POST', NULL),
(37, 'admin_log_api_id@put', NULL, 'admin', '/admin/log/api/{id:\\d+}', 'PUT', NULL),
(38, 'admin_log_api_id@delete', NULL, 'admin', '/admin/log/api/{id:\\d+}', 'DELETE', NULL),
(39, 'admin_log_api_resend@get', NULL, 'admin', '/admin/log/api/resend', 'GET', NULL),
(40, 'admin_log_cronjob_id@get', NULL, 'admin', '/admin/log/cronjob/{id:\\d+}', 'GET', NULL),
(41, 'admin_log_cronjob_list@get', NULL, 'admin', '/admin/log/cronjob/list', 'GET', NULL),
(42, 'admin_log_cronjob@get', NULL, 'admin', '/admin/log/cronjob', 'GET', NULL),
(43, 'admin_log_cronjob@post', NULL, 'admin', '/admin/log/cronjob', 'POST', NULL),
(44, 'admin_log_cronjob_id@put', NULL, 'admin', '/admin/log/cronjob/{id:\\d+}', 'PUT', NULL),
(45, 'admin_log_cronjob_id@delete', NULL, 'admin', '/admin/log/cronjob/{id:\\d+}', 'DELETE', NULL),
(46, 'admin_log_user_id@get', NULL, 'admin', '/admin/log/user/{id:\\d+}', 'GET', NULL),
(47, 'admin_log_user_list@get', NULL, 'admin', '/admin/log/user/list', 'GET', NULL),
(48, 'admin_log_user@get', NULL, 'admin', '/admin/log/user', 'GET', NULL),
(49, 'admin_log_user@post', NULL, 'admin', '/admin/log/user', 'POST', NULL),
(50, 'admin_log_user_id@put', NULL, 'admin', '/admin/log/user/{id:\\d+}', 'PUT', NULL),
(51, 'admin_log_user_id@delete', NULL, 'admin', '/admin/log/user/{id:\\d+}', 'DELETE', NULL),
(52, 'admin_permission_admin_id@get', NULL, 'admin', '/admin/permission/admin/{id:\\d+}', 'GET', NULL),
(53, 'admin_permission_admin_list@get', NULL, 'admin', '/admin/permission/admin/list', 'GET', NULL),
(54, 'admin_permission_admin@get', NULL, 'admin', '/admin/permission/admin', 'GET', NULL),
(55, 'admin_permission_admin@post', NULL, 'admin', '/admin/permission/admin', 'POST', NULL),
(56, 'admin_permission_admin_id@put', NULL, 'admin', '/admin/permission/admin/{id:\\d+}', 'PUT', NULL),
(57, 'admin_permission_admin_id@delete', NULL, 'admin', '/admin/permission/admin/{id:\\d+}', 'DELETE', NULL),
(58, 'admin_permission_template_id@get', NULL, 'admin', '/admin/permission/template/{id:\\d+}', 'GET', NULL),
(59, 'admin_permission_template_list@get', NULL, 'admin', '/admin/permission/template/list', 'GET', NULL),
(60, 'admin_permission_template@get', NULL, 'admin', '/admin/permission/template', 'GET', NULL),
(61, 'admin_permission_template@post', NULL, 'admin', '/admin/permission/template', 'POST', NULL),
(62, 'admin_permission_template_id@put', NULL, 'admin', '/admin/permission/template/{id:\\d+}', 'PUT', NULL),
(63, 'admin_permission_template_id@delete', NULL, 'admin', '/admin/permission/template/{id:\\d+}', 'DELETE', NULL),
(64, 'admin_wallet_transaction_id@get', NULL, 'admin', '/admin/wallet/transaction/{id:\\d+}', 'GET', NULL),
(65, 'admin_wallet_transaction_list@get', NULL, 'admin', '/admin/wallet/transaction/list', 'GET', NULL),
(66, 'admin_wallet_transaction@get', NULL, 'admin', '/admin/wallet/transaction', 'GET', NULL),
(67, 'admin_wallet_transaction@post', NULL, 'admin', '/admin/wallet/transaction', 'POST', NULL),
(68, 'admin_wallet_transaction_id@delete', NULL, 'admin', '/admin/wallet/transaction/{id:\\d+}', 'DELETE', NULL),
(69, 'admin_wallet_transaction_id@put', NULL, 'admin', '/admin/wallet/transaction/{id:\\d+}', 'PUT', NULL),
(70, 'admin_wallet_transactiondetail_id@get', NULL, 'admin', '/admin/wallet/transactionDetail/{id:\\d+}', 'GET', NULL),
(71, 'admin_wallet_transactiondetail_list@get', NULL, 'admin', '/admin/wallet/transactionDetail/list', 'GET', NULL),
(72, 'admin_wallet_transactiondetail@get', NULL, 'admin', '/admin/wallet/transactionDetail', 'GET', NULL),
(73, 'admin_wallet_transactiondetail@post', NULL, 'admin', '/admin/wallet/transactionDetail', 'POST', NULL),
(74, 'admin_wallet_transactiondetail_id@put', NULL, 'admin', '/admin/wallet/transactionDetail/{id:\\d+}', 'PUT', NULL),
(75, 'admin_wallet_transactiondetail_id@delete', NULL, 'admin', '/admin/wallet/transactionDetail/{id:\\d+}', 'DELETE', NULL),
(76, 'admin_reward_record_list@get', NULL, 'admin', '/admin/reward/record/list', 'GET', NULL),
(77, 'admin_reward_record@get', NULL, 'admin', '/admin/reward/record', 'GET', NULL),
(78, 'admin_reward_record@post', NULL, 'admin', '/admin/reward/record', 'POST', NULL),
(79, 'admin_reward_record_id@put', NULL, 'admin', '/admin/reward/record/{id:\\d+}', 'PUT', NULL),
(80, 'admin_reward_record_id@delete', NULL, 'admin', '/admin/reward/record/{id:\\d+}', 'DELETE', NULL),
(81, 'admin_reward_record_id@get', NULL, 'admin', '/admin/reward/record/{id:\\d+}', 'GET', NULL),
(82, 'admin_setting_announcement_id@get', NULL, 'admin', '/admin/setting/announcement/{id:\\d+}', 'GET', NULL),
(83, 'admin_setting_announcement_list@get', NULL, 'admin', '/admin/setting/announcement/list', 'GET', NULL),
(84, 'admin_setting_announcement@get', NULL, 'admin', '/admin/setting/announcement', 'GET', NULL),
(85, 'admin_setting_announcement@post', NULL, 'admin', '/admin/setting/announcement', 'POST', NULL),
(86, 'admin_setting_announcement_id@put', NULL, 'admin', '/admin/setting/announcement/{id:\\d+}', 'PUT', NULL),
(87, 'admin_setting_announcement_id@delete', NULL, 'admin', '/admin/setting/announcement/{id:\\d+}', 'DELETE', NULL),
(88, 'admin_setting_attribute@post', NULL, 'admin', '/admin/setting/attribute', 'POST', NULL),
(89, 'admin_setting_attribute_id@put', NULL, 'admin', '/admin/setting/attribute/{id:\\d+}', 'PUT', NULL),
(90, 'admin_setting_attribute_list@get', NULL, 'admin', '/admin/setting/attribute/list', 'GET', NULL),
(91, 'admin_setting_attribute@get', NULL, 'admin', '/admin/setting/attribute', 'GET', NULL),
(92, 'admin_setting_attribute_id@get', NULL, 'admin', '/admin/setting/attribute/{id:\\d+}', 'GET', NULL),
(93, 'admin_setting_attribute_id@delete', NULL, 'admin', '/admin/setting/attribute/{id:\\d+}', 'DELETE', NULL),
(94, 'admin_setting_blockchainnetwork_id@get', NULL, 'admin', '/admin/setting/blockchainNetwork/{id:\\d+}', 'GET', NULL),
(95, 'admin_setting_blockchainnetwork_list@get', NULL, 'admin', '/admin/setting/blockchainNetwork/list', 'GET', NULL),
(96, 'admin_setting_blockchainnetwork@get', NULL, 'admin', '/admin/setting/blockchainNetwork', 'GET', NULL),
(97, 'admin_setting_blockchainnetwork@post', NULL, 'admin', '/admin/setting/blockchainNetwork', 'POST', NULL),
(98, 'admin_setting_blockchainnetwork_id@delete', NULL, 'admin', '/admin/setting/blockchainNetwork/{id:\\d+}', 'DELETE', NULL),
(99, 'admin_setting_blockchainnetwork_id@put', NULL, 'admin', '/admin/setting/blockchainNetwork/{id:\\d+}', 'PUT', NULL),
(100, 'admin_setting_coin_id@get', NULL, 'admin', '/admin/setting/coin/{id:\\d+}', 'GET', NULL),
(101, 'admin_setting_coin_list@get', NULL, 'admin', '/admin/setting/coin/list', 'GET', NULL),
(102, 'admin_setting_coin@get', NULL, 'admin', '/admin/setting/coin', 'GET', NULL),
(103, 'admin_setting_coin@post', NULL, 'admin', '/admin/setting/coin', 'POST', NULL),
(104, 'admin_setting_coin_id@put', NULL, 'admin', '/admin/setting/coin/{id:\\d+}', 'PUT', NULL),
(105, 'admin_setting_coin_id@delete', NULL, 'admin', '/admin/setting/coin/{id:\\d+}', 'DELETE', NULL),
(106, 'admin_setting_deposit_id@get', NULL, 'admin', '/admin/setting/deposit/{id:\\d+}', 'GET', NULL),
(107, 'admin_setting_deposit_list@get', NULL, 'admin', '/admin/setting/deposit/list', 'GET', NULL),
(108, 'admin_setting_deposit@get', NULL, 'admin', '/admin/setting/deposit', 'GET', NULL),
(109, 'admin_setting_deposit@post', NULL, 'admin', '/admin/setting/deposit', 'POST', NULL),
(110, 'admin_setting_deposit_id@put', NULL, 'admin', '/admin/setting/deposit/{id:\\d+}', 'PUT', NULL),
(111, 'admin_setting_deposit_id@delete', NULL, 'admin', '/admin/setting/deposit/{id:\\d+}', 'DELETE', NULL),
(112, 'admin_setting_general_id@get', NULL, 'admin', '/admin/setting/general/{id:\\d+}', 'GET', NULL),
(113, 'admin_setting_general_list@get', NULL, 'admin', '/admin/setting/general/list', 'GET', NULL),
(114, 'admin_setting_general@get', NULL, 'admin', '/admin/setting/general', 'GET', NULL),
(115, 'admin_setting_general@post', NULL, 'admin', '/admin/setting/general', 'POST', NULL),
(116, 'admin_setting_general_id@put', NULL, 'admin', '/admin/setting/general/{id:\\d+}', 'PUT', NULL),
(117, 'admin_setting_general_id@delete', NULL, 'admin', '/admin/setting/general/{id:\\d+}', 'DELETE', NULL),
(118, 'admin_setting_lang_id@get', NULL, 'admin', '/admin/setting/lang/{id:\\d+}', 'GET', NULL),
(119, 'admin_setting_lang_list@get', NULL, 'admin', '/admin/setting/lang/list', 'GET', NULL),
(120, 'admin_setting_lang@get', NULL, 'admin', '/admin/setting/lang', 'GET', NULL),
(121, 'admin_setting_lang@post', NULL, 'admin', '/admin/setting/lang', 'POST', NULL),
(122, 'admin_setting_lang_id@put', NULL, 'admin', '/admin/setting/lang/{id:\\d+}', 'PUT', NULL),
(123, 'admin_setting_lang_id@delete', NULL, 'admin', '/admin/setting/lang/{id:\\d+}', 'DELETE', NULL),
(124, 'admin_setting_operator_id@get', NULL, 'admin', '/admin/setting/operator/{id:\\d+}', 'GET', NULL),
(125, 'admin_setting_operator_list@get', NULL, 'admin', '/admin/setting/operator/list', 'GET', NULL),
(126, 'admin_setting_operator@get', NULL, 'admin', '/admin/setting/operator', 'GET', NULL),
(127, 'admin_setting_operator@post', NULL, 'admin', '/admin/setting/operator', 'POST', NULL),
(128, 'admin_setting_operator_id@put', NULL, 'admin', '/admin/setting/operator/{id:\\d+}', 'PUT', NULL),
(129, 'admin_setting_operator_id@delete', NULL, 'admin', '/admin/setting/operator/{id:\\d+}', 'DELETE', NULL),
(130, 'admin_setting_payment_id@get', NULL, 'admin', '/admin/setting/payment/{id:\\d+}', 'GET', NULL),
(131, 'admin_setting_payment_list@get', NULL, 'admin', '/admin/setting/payment/list', 'GET', NULL),
(132, 'admin_setting_payment@get', NULL, 'admin', '/admin/setting/payment', 'GET', NULL),
(133, 'admin_setting_payment@post', NULL, 'admin', '/admin/setting/payment', 'POST', NULL),
(134, 'admin_setting_payment_id@put', NULL, 'admin', '/admin/setting/payment/{id:\\d+}', 'PUT', NULL),
(135, 'admin_setting_payment_id@delete', NULL, 'admin', '/admin/setting/payment/{id:\\d+}', 'DELETE', NULL),
(136, 'admin_setting_reward_id@get', NULL, 'admin', '/admin/setting/reward/{id:\\d+}', 'GET', NULL),
(137, 'admin_setting_reward_list@get', NULL, 'admin', '/admin/setting/reward/list', 'GET', NULL),
(138, 'admin_setting_reward@get', NULL, 'admin', '/admin/setting/reward', 'GET', NULL),
(139, 'admin_setting_reward@post', NULL, 'admin', '/admin/setting/reward', 'POST', NULL),
(140, 'admin_setting_reward_id@put', NULL, 'admin', '/admin/setting/reward/{id:\\d+}', 'PUT', NULL),
(141, 'admin_setting_reward_id@delete', NULL, 'admin', '/admin/setting/reward/{id:\\d+}', 'DELETE', NULL),
(142, 'admin_setting_wallet_id@get', NULL, 'admin', '/admin/setting/wallet/{id:\\d+}', 'GET', NULL),
(143, 'admin_setting_wallet_list@get', NULL, 'admin', '/admin/setting/wallet/list', 'GET', NULL),
(144, 'admin_setting_wallet@get', NULL, 'admin', '/admin/setting/wallet', 'GET', NULL),
(145, 'admin_setting_wallet@post', NULL, 'admin', '/admin/setting/wallet', 'POST', NULL),
(146, 'admin_setting_wallet_id@put', NULL, 'admin', '/admin/setting/wallet/{id:\\d+}', 'PUT', NULL),
(147, 'admin_setting_wallet_id@delete', NULL, 'admin', '/admin/setting/wallet/{id:\\d+}', 'DELETE', NULL),
(148, 'admin_setting_walletattribute_id@get', NULL, 'admin', '/admin/setting/walletAttribute/{id:\\d+}', 'GET', NULL),
(149, 'admin_setting_walletattribute_list@get', NULL, 'admin', '/admin/setting/walletAttribute/list', 'GET', NULL),
(150, 'admin_setting_walletattribute@get', NULL, 'admin', '/admin/setting/walletAttribute', 'GET', NULL),
(151, 'admin_setting_walletattribute@post', NULL, 'admin', '/admin/setting/walletAttribute', 'POST', NULL),
(152, 'admin_setting_walletattribute_id@put', NULL, 'admin', '/admin/setting/walletAttribute/{id:\\d+}', 'PUT', NULL),
(153, 'admin_setting_walletattribute_id@delete', NULL, 'admin', '/admin/setting/walletAttribute/{id:\\d+}', 'DELETE', NULL),
(154, 'admin_setting_withdraw_id@get', NULL, 'admin', '/admin/setting/withdraw/{id:\\d+}', 'GET', NULL),
(155, 'admin_setting_withdraw_list@get', NULL, 'admin', '/admin/setting/withdraw/list', 'GET', NULL),
(156, 'admin_setting_withdraw@get', NULL, 'admin', '/admin/setting/withdraw', 'GET', NULL),
(157, 'admin_setting_withdraw@post', NULL, 'admin', '/admin/setting/withdraw', 'POST', NULL),
(158, 'admin_setting_withdraw_id@put', NULL, 'admin', '/admin/setting/withdraw/{id:\\d+}', 'PUT', NULL),
(159, 'admin_setting_withdraw_id@delete', NULL, 'admin', '/admin/setting/withdraw/{id:\\d+}', 'DELETE', NULL),
(160, 'admin_user_deposit_id@get', NULL, 'admin', '/admin/user/deposit/{id:\\d+}', 'GET', NULL),
(161, 'admin_user_deposit_list@get', NULL, 'admin', '/admin/user/deposit/list', 'GET', NULL),
(162, 'admin_user_deposit@get', NULL, 'admin', '/admin/user/deposit', 'GET', NULL),
(163, 'admin_user_deposit@post', NULL, 'admin', '/admin/user/deposit', 'POST', NULL),
(164, 'admin_user_deposit_id@put', NULL, 'admin', '/admin/user/deposit/{id:\\d+}', 'PUT', NULL),
(165, 'admin_user_deposit_id@delete', NULL, 'admin', '/admin/user/deposit/{id:\\d+}', 'DELETE', NULL),
(166, 'admin_user_remark_id@get', NULL, 'admin', '/admin/user/remark/{id:\\d+}', 'GET', NULL),
(167, 'admin_user_remark_list@get', NULL, 'admin', '/admin/user/remark/list', 'GET', NULL),
(168, 'admin_user_remark@get', NULL, 'admin', '/admin/user/remark', 'GET', NULL),
(169, 'admin_user_remark@post', NULL, 'admin', '/admin/user/remark', 'POST', NULL),
(170, 'admin_user_remark_id@put', NULL, 'admin', '/admin/user/remark/{id:\\d+}', 'PUT', NULL),
(171, 'admin_user_remark_id@delete', NULL, 'admin', '/admin/user/remark/{id:\\d+}', 'DELETE', NULL),
(172, 'admin_user_withdraw_id@get', NULL, 'admin', '/admin/user/withdraw/{id:\\d+}', 'GET', NULL),
(173, 'admin_user_withdraw_list@get', NULL, 'admin', '/admin/user/withdraw/list', 'GET', NULL),
(174, 'admin_user_withdraw@get', NULL, 'admin', '/admin/user/withdraw', 'GET', NULL),
(175, 'admin_user_withdraw@post', NULL, 'admin', '/admin/user/withdraw', 'POST', NULL),
(176, 'admin_user_withdraw_id@put', NULL, 'admin', '/admin/user/withdraw/{id:\\d+}', 'PUT', NULL),
(177, 'admin_user_withdraw_id@delete', NULL, 'admin', '/admin/user/withdraw/{id:\\d+}', 'DELETE', NULL),
(178, 'admin_setting_pet@post', NULL, 'admin', '/admin/setting/pet', 'POST', NULL),
(179, 'admin_setting_pet_id@put', NULL, 'admin', '/admin/setting/pet/{id:\\d+}', 'PUT', NULL),
(180, 'admin_setting_pet_id@delete', NULL, 'admin', '/admin/setting/pet/{id:\\d+}', 'DELETE', NULL),
(181, 'admin_setting_pet_id@get', NULL, 'admin', '/admin/setting/pet/{id:\\d+}', 'GET', NULL),
(182, 'admin_setting_pet_list@get', NULL, 'admin', '/admin/setting/pet/list', 'GET', NULL),
(183, 'admin_setting_pet@get', NULL, 'admin', '/admin/setting/pet', 'GET', NULL),
(184, 'admin_setting_petattribute@post', NULL, 'admin', '/admin/setting/petAttribute', 'POST', NULL),
(185, 'admin_setting_petattribute_id@put', NULL, 'admin', '/admin/setting/petAttribute/{id:\\d+}', 'PUT', NULL),
(186, 'admin_setting_petattribute_id@delete', NULL, 'admin', '/admin/setting/petAttribute/{id:\\d+}', 'DELETE', NULL),
(187, 'admin_setting_petattribute@get', NULL, 'admin', '/admin/setting/petAttribute', 'GET', NULL),
(188, 'admin_setting_petattribute_list@get', NULL, 'admin', '/admin/setting/petAttribute/list', 'GET', NULL),
(189, 'admin_setting_petattribute_id@get', NULL, 'admin', '/admin/setting/petAttribute/{id:\\d+}', 'GET', NULL),
(190, 'admin_setting_item@post', NULL, 'admin', '/admin/setting/item', 'POST', NULL),
(191, 'admin_setting_item_id@put', NULL, 'admin', '/admin/setting/item/{id:\\d+}', 'PUT', NULL),
(192, 'admin_setting_item_id@delete', NULL, 'admin', '/admin/setting/item/{id:\\d+}', 'DELETE', NULL),
(193, 'admin_setting_item_id@get', NULL, 'admin', '/admin/setting/item/{id:\\d+}', 'GET', NULL),
(194, 'admin_setting_item_list@get', NULL, 'admin', '/admin/setting/item/list', 'GET', NULL),
(195, 'admin_setting_item@get', NULL, 'admin', '/admin/setting/item', 'GET', NULL),
(196, 'admin_setting_itemattribute@post', NULL, 'admin', '/admin/setting/itemAttribute', 'POST', NULL),
(197, 'admin_setting_itemattribute_id@put', NULL, 'admin', '/admin/setting/itemAttribute/{id:\\d+}', 'PUT', NULL),
(198, 'admin_setting_itemattribute_id@delete', NULL, 'admin', '/admin/setting/itemAttribute/{id:\\d+}', 'DELETE', NULL),
(199, 'admin_setting_itemattribute_id@get', NULL, 'admin', '/admin/setting/itemAttribute/{id:\\d+}', 'GET', NULL),
(200, 'admin_setting_itemattribute_list@get', NULL, 'admin', '/admin/setting/itemAttribute/list', 'GET', NULL),
(201, 'admin_setting_itemattribute@get', NULL, 'admin', '/admin/setting/itemAttribute', 'GET', NULL),
(202, 'admin_user_pet@post', NULL, 'admin', '/admin/user/pet', 'POST', NULL),
(203, 'admin_user_pet_id@put', NULL, 'admin', '/admin/user/pet/{id:\\d+}', 'PUT', NULL),
(204, 'admin_user_pet_id@delete', NULL, 'admin', '/admin/user/pet/{id:\\d+}', 'DELETE', NULL),
(205, 'admin_user_pet_id@get', NULL, 'admin', '/admin/user/pet/{id:\\d+}', 'GET', NULL),
(206, 'admin_user_pet_list@get', NULL, 'admin', '/admin/user/pet/list', 'GET', NULL),
(207, 'admin_user_pet@get', NULL, 'admin', '/admin/user/pet', 'GET', NULL),
(208, 'admin_user_stamina@post', NULL, 'admin', '/admin/user/stamina', 'POST', NULL),
(209, 'admin_user_stamina_id@put', NULL, 'admin', '/admin/user/stamina/{id:\\d+}', 'PUT', NULL),
(210, 'admin_user_stamina_id@delete', NULL, 'admin', '/admin/user/stamina/{id:\\d+}', 'DELETE', NULL),
(211, 'admin_user_stamina_id@get', NULL, 'admin', '/admin/user/stamina/{id:\\d+}', 'GET', NULL),
(212, 'admin_user_stamina_list@get', NULL, 'admin', '/admin/user/stamina/list', 'GET', NULL),
(213, 'admin_user_stamina@get', NULL, 'admin', '/admin/user/stamina', 'GET', NULL),
(214, 'admin_user_level_id@get', NULL, 'admin', '/admin/user/level/{id:\\d+}', 'GET', NULL),
(215, 'admin_user_level@post', NULL, 'admin', '/admin/user/level', 'POST', NULL),
(216, 'admin_user_level_id@put', NULL, 'admin', '/admin/user/level/{id:\\d+}', 'PUT', NULL),
(217, 'admin_user_level_id@delete', NULL, 'admin', '/admin/user/level/{id:\\d+}', 'DELETE', NULL),
(218, 'admin_user_level_list@get', NULL, 'admin', '/admin/user/level/list', 'GET', NULL),
(219, 'admin_user_level@get', NULL, 'admin', '/admin/user/level', 'GET', NULL),
(220, 'admin_setting_rewardattribute@post', NULL, 'admin', '/admin/setting/rewardAttribute', 'POST', NULL),
(221, 'admin_setting_rewardattribute_id@put', NULL, 'admin', '/admin/setting/rewardAttribute/{id:\\d+}', 'PUT', NULL),
(222, 'admin_setting_rewardattribute_id@get', NULL, 'admin', '/admin/setting/rewardAttribute/{id:\\d+}', 'GET', NULL),
(223, 'admin_setting_rewardattribute_list@get', NULL, 'admin', '/admin/setting/rewardAttribute/list', 'GET', NULL),
(224, 'admin_setting_rewardattribute@get', NULL, 'admin', '/admin/setting/rewardAttribute', 'GET', NULL),
(225, 'admin_setting_rewardattribute_id@delete', NULL, 'admin', '/admin/setting/rewardAttribute/{id:\\d+}', 'DELETE', NULL),
(226, 'admin_setting_mission@post', NULL, 'admin', '/admin/setting/mission', 'POST', NULL),
(227, 'admin_setting_mission_id@put', NULL, 'admin', '/admin/setting/mission/{id:\\d+}', 'PUT', NULL),
(228, 'admin_setting_mission_id@delete', NULL, 'admin', '/admin/setting/mission/{id:\\d+}', 'DELETE', NULL),
(229, 'admin_setting_mission_list@get', NULL, 'admin', '/admin/setting/mission/list', 'GET', NULL),
(230, 'admin_setting_mission@get', NULL, 'admin', '/admin/setting/mission', 'GET', NULL),
(231, 'admin_setting_mission_id@get', NULL, 'admin', '/admin/setting/mission/{id:\\d+}', 'GET', NULL),
(232, 'admin_user_inventory@post', NULL, 'admin', '/admin/user/inventory', 'POST', NULL),
(233, 'admin_user_inventory_id@put', NULL, 'admin', '/admin/user/inventory/{id:\\d+}', 'PUT', NULL),
(234, 'admin_user_inventory_id@delete', NULL, 'admin', '/admin/user/inventory/{id:\\d+}', 'DELETE', NULL),
(235, 'admin_user_inventory_id@get', NULL, 'admin', '/admin/user/inventory/{id:\\d+}', 'GET', NULL),
(236, 'admin_user_inventory_list@get', NULL, 'admin', '/admin/user/inventory/list', 'GET', NULL),
(237, 'admin_user_inventory@get', NULL, 'admin', '/admin/user/inventory', 'GET', NULL),
(238, 'admin_user_mission@post', NULL, 'admin', '/admin/user/mission', 'POST', NULL),
(239, 'admin_user_mission_id@put', NULL, 'admin', '/admin/user/mission/{id:\\d+}', 'PUT', NULL),
(240, 'admin_user_mission_id@delete', NULL, 'admin', '/admin/user/mission/{id:\\d+}', 'DELETE', NULL),
(241, 'admin_user_mission_id@get', NULL, 'admin', '/admin/user/mission/{id:\\d+}', 'GET', NULL),
(242, 'admin_user_mission_list@get', NULL, 'admin', '/admin/user/mission/list', 'GET', NULL),
(243, 'admin_user_mission@get', NULL, 'admin', '/admin/user/mission', 'GET', NULL),
(244, 'admin_user_battle@post', NULL, 'admin', '/admin/user/battle', 'POST', NULL),
(245, 'admin_user_battle_id@delete', NULL, 'admin', '/admin/user/battle/{id:\\d+}', 'DELETE', NULL),
(246, 'admin_user_battle_id@put', NULL, 'admin', '/admin/user/battle/{id:\\d+}', 'PUT', NULL),
(247, 'admin_user_battle_id@get', NULL, 'admin', '/admin/user/battle/{id:\\d+}', 'GET', NULL),
(248, 'admin_user_battle_list@get', NULL, 'admin', '/admin/user/battle/list', 'GET', NULL),
(249, 'admin_user_battle@get', NULL, 'admin', '/admin/user/battle', 'GET', NULL),
(250, 'admin_setting_gacha@post', NULL, 'admin', '/admin/setting/gacha', 'POST', NULL),
(251, 'admin_setting_gacha_id@put', NULL, 'admin', '/admin/setting/gacha/{id:\\d+}', 'PUT', NULL),
(252, 'admin_setting_gacha_id@delete', NULL, 'admin', '/admin/setting/gacha/{id:\\d+}', 'DELETE', NULL),
(253, 'admin_setting_gacha_id@get', NULL, 'admin', '/admin/setting/gacha/{id:\\d+}', 'GET', NULL),
(254, 'admin_setting_gacha_list@get', NULL, 'admin', '/admin/setting/gacha/list', 'GET', NULL),
(255, 'admin_setting_gacha@get', NULL, 'admin', '/admin/setting/gacha', 'GET', NULL),
(256, 'admin_setting_level@post', NULL, 'admin', '/admin/setting/level', 'POST', NULL),
(257, 'admin_setting_level_id@put', NULL, 'admin', '/admin/setting/level/{id:\\d+}', 'PUT', NULL),
(258, 'admin_setting_level_list@get', NULL, 'admin', '/admin/setting/level/list', 'GET', NULL),
(259, 'admin_setting_level@get', NULL, 'admin', '/admin/setting/level', 'GET', NULL),
(260, 'admin_setting_level_id@get', NULL, 'admin', '/admin/setting/level/{id:\\d+}', 'GET', NULL),
(261, 'admin_setting_gachaitem_id@get', NULL, 'admin', '/admin/setting/gachaItem/{id:\\d+}', 'GET', NULL),
(262, 'admin_setting_gachaitem@post', NULL, 'admin', '/admin/setting/gachaItem', 'POST', NULL),
(263, 'admin_setting_gachaitem_id@put', NULL, 'admin', '/admin/setting/gachaItem/{id:\\d+}', 'PUT', NULL),
(264, 'admin_setting_gachaitem_id@delete', NULL, 'admin', '/admin/setting/gachaItem/{id:\\d+}', 'DELETE', NULL),
(265, 'admin_setting_gachaitem_list@get', NULL, 'admin', '/admin/setting/gachaItem/list', 'GET', NULL),
(266, 'admin_setting_gachaitem@get', NULL, 'admin', '/admin/setting/gachaItem', 'GET', NULL),
(267, 'admin_network_sponsor@get', NULL, 'admin', '/admin/network/sponsor', 'GET', NULL),
(268, 'admin_user_point@get', NULL, 'admin', '/admin/user/point', 'GET', NULL),
(269, 'admin_user_point@post', NULL, 'admin', '/admin/user/point', 'POST', NULL),
(270, 'admin_network_sponsor@post', NULL, 'admin', '/admin/network/sponsor', 'POST', NULL),
(271, 'admin_user_gacha@post', NULL, 'admin', '/admin/user/gacha', 'POST', NULL),
(272, 'admin_user_gacha_id@put', NULL, 'admin', '/admin/user/gacha/{id:\\d+}', 'PUT', NULL),
(273, 'admin_user_gacha_id@delete', NULL, 'admin', '/admin/user/gacha/{id:\\d+}', 'DELETE', NULL),
(274, 'admin_user_gacha_list@get', NULL, 'admin', '/admin/user/gacha/list', 'GET', NULL),
(275, 'admin_user_gacha@get', NULL, 'admin', '/admin/user/gacha', 'GET', NULL),
(276, 'admin_user_gacha_id@get', NULL, 'admin', '/admin/user/gacha/{id:\\d+}', 'GET', NULL),
(277, 'admin_setting_nft_list@get', NULL, 'admin', '/admin/setting/nft/list', 'GET', NULL),
(278, 'admin_setting_nft_id@get', NULL, 'admin', '/admin/setting/nft/{id:\\d+}', 'GET', NULL),
(279, 'admin_setting_nft@get', NULL, 'admin', '/admin/setting/nft', 'GET', NULL),
(280, 'admin_setting_nft@post', NULL, 'admin', '/admin/setting/nft', 'POST', NULL),
(281, 'admin_setting_nft_id@put', NULL, 'admin', '/admin/setting/nft/{id:\\d+}', 'PUT', NULL),
(282, 'admin_setting_nft_id@delete', NULL, 'admin', '/admin/setting/nft/{id:\\d+}', 'DELETE', NULL),
(283, 'admin_user_nft_id@get', NULL, 'admin', '/admin/user/nft/{id:\\d+}', 'GET', NULL),
(284, 'admin_user_nft_list@get', NULL, 'admin', '/admin/user/nft/list', 'GET', NULL),
(285, 'admin_user_nft@get', NULL, 'admin', '/admin/user/nft', 'GET', NULL),
(286, 'admin_user_nft@post', NULL, 'admin', '/admin/user/nft', 'POST', NULL),
(287, 'admin_user_nft_id@put', NULL, 'admin', '/admin/user/nft/{id:\\d+}', 'PUT', NULL),
(288, 'admin_user_nft_id@delete', NULL, 'admin', '/admin/user/nft/{id:\\d+}', 'DELETE', NULL),
(289, 'admin_account_user_details@get', NULL, 'admin', '/admin/account/user/details', 'GET', NULL),
(290, 'admin_setting_petrank_id@get', NULL, 'admin', '/admin/setting/petRank/{id:\\d+}', 'GET', NULL),
(291, 'admin_setting_petrank_list@get', NULL, 'admin', '/admin/setting/petRank/list', 'GET', NULL),
(292, 'admin_setting_petrank@get', NULL, 'admin', '/admin/setting/petRank', 'GET', NULL),
(293, 'admin_setting_petrank@post', NULL, 'admin', '/admin/setting/petRank', 'POST', NULL),
(294, 'admin_setting_petrank_id@put', NULL, 'admin', '/admin/setting/petRank/{id:\\d+}', 'PUT', NULL),
(295, 'admin_setting_petrank_id@delete', NULL, 'admin', '/admin/setting/petRank/{id:\\d+}', 'DELETE', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_reward_record`
--

CREATE TABLE `sw_reward_record` (
  `id` bigint(20) NOT NULL,
  `sn` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `pay_at` datetime DEFAULT NULL,
  `used_at` varchar(50) DEFAULT NULL,
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `user_pet_id` int(11) DEFAULT NULL COMMENT 'refer to user_pet',
  `from_uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `from_user_pet_id` int(11) DEFAULT NULL COMMENT 'refer to user_pet',
  `reward_type` int(11) DEFAULT 0 COMMENT 'refer to setting_operator',
  `amount` decimal(20,8) DEFAULT 0.00000000,
  `rate` decimal(20,8) DEFAULT 0.00000000,
  `item_reward` text DEFAULT NULL,
  `pet_reward` text DEFAULT NULL,
  `distribution` text DEFAULT NULL,
  `ref_table` varchar(64) DEFAULT NULL,
  `ref_id` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_announcement`
--

CREATE TABLE `sw_setting_announcement` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `code` varchar(128) DEFAULT NULL,
  `lang` int(11) DEFAULT 0 COMMENT 'refer to setting_lang',
  `type` varchar(255) DEFAULT NULL COMMENT 'refer to setting_operator',
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_show` tinyint(1) NOT NULL DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_attribute`
--

CREATE TABLE `sw_setting_attribute` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `code` varchar(128) DEFAULT NULL,
  `category` varchar(128) DEFAULT NULL,
  `filter` varchar(128) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_attribute`
--

INSERT INTO `sw_setting_attribute` (`id`, `deleted_at`, `code`, `category`, `filter`, `remark`) VALUES
(1, NULL, 'rank', 'feature', 'pet', NULL),
(2, NULL, 'star', 'feature', 'pet', NULL),
(3, NULL, 'replenish', 'feature', 'item', NULL),
(4, NULL, 'cooldown', 'feature', 'item', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_blockchain_network`
--

CREATE TABLE `sw_setting_blockchain_network` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `code` varchar(128) DEFAULT NULL,
  `type` varchar(128) DEFAULT NULL,
  `chain_id` int(11) DEFAULT 0,
  `rpc_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_blockchain_network`
--

INSERT INTO `sw_setting_blockchain_network` (`id`, `deleted_at`, `code`, `type`, `chain_id`, `rpc_url`) VALUES
(1, NULL, 'Astar', 'ASTR', 592, 'https://evm.astar.network'),
(2, NULL, 'Shibuya', 'SBY', 81, 'https://evm.shibuya.astar.network');

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_coin`
--

CREATE TABLE `sw_setting_coin` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `code` varchar(128) DEFAULT NULL,
  `wallet_id` int(11) DEFAULT 0 COMMENT 'refer to setting_wallet',
  `is_show` tinyint(1) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_coin`
--

INSERT INTO `sw_setting_coin` (`id`, `deleted_at`, `code`, `wallet_id`, `is_show`, `remark`) VALUES
(1, NULL, 'ASTRGTENDO', 2, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_deposit`
--

CREATE TABLE `sw_setting_deposit` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `coin_id` int(11) DEFAULT 0 COMMENT 'refer to setting_coin',
  `token_address` varchar(66) DEFAULT NULL COMMENT 'contract address',
  `network` int(11) DEFAULT 0 COMMENT 'refer to setting_blockchain_network',
  `address` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `latest_block` varchar(1000) DEFAULT '0',
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `sw_setting_deposit`
--

INSERT INTO `sw_setting_deposit` (`id`, `created_at`, `updated_at`, `deleted_at`, `coin_id`, `token_address`, `network`, `address`, `is_active`, `latest_block`, `remark`) VALUES
(1, '2024-02-28 12:00:00', '2024-02-28 12:00:00', NULL, 1, '0x81b6420daD8b13388444EB85DbFc4F157dDbc2b0', 2, '0xBdc76521b93cbF4E1dEf17a8d17a7767A3B85C4c', 1, '0', NULL),
(2, '2024-02-28 12:00:00', '2024-02-28 12:00:00', NULL, 1, '0x81b6420daD8b13388444EB85DbFc4F157dDbc2b0', 2, '0x64e9BB3647494f22a330C72AF407104a0f9Ba9bB', 1, '0', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_gacha`
--

CREATE TABLE `sw_setting_gacha` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `single_normal_price` decimal(20,8) DEFAULT 0.00000000,
  `single_sales_price` decimal(20,8) DEFAULT 0.00000000,
  `ten_normal_price` decimal(20,8) DEFAULT 0.00000000,
  `ten_sales_price` decimal(20,8) DEFAULT 0.00000000,
  `payment_id` int(11) DEFAULT 0 COMMENT 'refer to setting_payment',
  `is_show` tinyint(1) NOT NULL DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_gacha`
--

INSERT INTO `sw_setting_gacha` (`id`, `deleted_at`, `start_at`, `end_at`, `image`, `name`, `single_normal_price`, `single_sales_price`, `ten_normal_price`, `ten_sales_price`, `payment_id`, `is_show`, `remark`) VALUES
(1, NULL, NULL, NULL, '/img/chest/chest5.png', 'mystery box', 299.00000000, 299.00000000, 2990.00000000, 2990.00000000, 1, 1, NULL),
(2, NULL, NULL, NULL, '/img/chest/chest4.png', 'silver box', 499.00000000, 499.00000000, 4990.00000000, 4990.00000000, 1, 1, NULL),
(3, NULL, NULL, NULL, '/img/chest/chest3.png', 'golden box', 399.00000000, 399.00000000, 3990.00000000, 3990.00000000, 2, 1, NULL),
(4, NULL, NULL, NULL, '/img/chest/chest2.png', 'premium box', 499.00000000, 499.00000000, 4990.00000000, 4990.00000000, 2, 1, NULL),
(5, NULL, NULL, NULL, '/img/chest/chest1.png', 'legendary box', 599.00000000, 599.00000000, 5990.00000000, 5990.00000000, 2, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_gacha_item`
--

CREATE TABLE `sw_setting_gacha_item` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `gacha_id` int(11) DEFAULT 0,
  `ref_table` varchar(64) DEFAULT NULL,
  `ref_id` int(11) DEFAULT 0,
  `token_reward` decimal(20,8) DEFAULT 0.00000000,
  `occurrence` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_gacha_item`
--

INSERT INTO `sw_setting_gacha_item` (`id`, `deleted_at`, `gacha_id`, `ref_table`, `ref_id`, `token_reward`, `occurrence`, `remark`) VALUES
(1, NULL, 1, 'setting_pet', 1, 0.00000000, 20, NULL),
(2, NULL, 1, 'setting_pet', 2, 0.00000000, 14, NULL),
(3, NULL, 1, 'setting_pet', 3, 0.00000000, 3, NULL),
(4, NULL, 1, 'setting_pet', 4, 0.00000000, 2, NULL),
(5, NULL, 1, 'setting_pet', 5, 0.00000000, 1, NULL),
(6, NULL, 1, 'setting_item', 15, 0.00000000, 34, NULL),
(7, NULL, 1, 'setting_item', 16, 0.00000000, 6, NULL),
(8, NULL, 1, 'setting_item', 19, 0.00000000, 30, NULL),
(9, NULL, 1, 'setting_item', 20, 0.00000000, 6, NULL),
(10, NULL, 1, 'setting_item', 21, 0.00000000, 4, NULL),
(11, NULL, 1, 'setting_item', 12, 0.00000000, 10, NULL),
(12, NULL, 1, 'setting_item', 1, 0.00000000, 8, NULL),
(13, NULL, 1, 'setting_item', 2, 0.00000000, 2, NULL),
(14, NULL, 1, 'setting_item', 3, 0.00000000, 20, NULL),
(15, NULL, 1, 'setting_item', 4, 0.00000000, 6, NULL),
(16, NULL, 1, 'setting_item', 5, 0.00000000, 4, NULL),
(17, NULL, 1, 'setting_item', 6, 0.00000000, 20, NULL),
(18, NULL, 1, 'setting_item', 7, 0.00000000, 6, NULL),
(19, NULL, 1, 'setting_item', 8, 0.00000000, 4, NULL),
(20, NULL, 2, 'setting_pet', 1, 0.00000000, 80, NULL),
(21, NULL, 2, 'setting_pet', 2, 0.00000000, 14, NULL),
(22, NULL, 2, 'setting_pet', 3, 0.00000000, 3, NULL),
(23, NULL, 2, 'setting_pet', 4, 0.00000000, 2, NULL),
(24, NULL, 2, 'setting_pet', 5, 0.00000000, 1, NULL),
(25, NULL, 2, 'setting_item', 15, 0.00000000, 34, NULL),
(26, NULL, 2, 'setting_item', 16, 0.00000000, 4, NULL),
(27, NULL, 2, 'setting_item', 17, 0.00000000, 2, NULL),
(28, NULL, 2, 'setting_item', 19, 0.00000000, 28, NULL),
(29, NULL, 2, 'setting_item', 20, 0.00000000, 6, NULL),
(30, NULL, 2, 'setting_item', 21, 0.00000000, 4, NULL),
(31, NULL, 2, 'setting_item', 22, 0.00000000, 2, NULL),
(32, NULL, 2, 'setting_item', 1, 0.00000000, 8, NULL),
(33, NULL, 2, 'setting_item', 2, 0.00000000, 2, NULL),
(34, NULL, 2, 'setting_wallet', 1, 100.00000000, 10, NULL),
(35, NULL, 3, 'setting_pet', 6, 0.00000000, 80, NULL),
(36, NULL, 3, 'setting_pet', 7, 0.00000000, 14, NULL),
(37, NULL, 3, 'setting_pet', 8, 0.00000000, 3, NULL),
(38, NULL, 3, 'setting_pet', 9, 0.00000000, 2, NULL),
(39, NULL, 3, 'setting_pet', 10, 0.00000000, 1, NULL),
(40, NULL, 3, 'setting_item', 15, 0.00000000, 30, NULL),
(41, NULL, 3, 'setting_item', 16, 0.00000000, 6, NULL),
(42, NULL, 3, 'setting_item', 17, 0.00000000, 4, NULL),
(43, NULL, 3, 'setting_item', 20, 0.00000000, 28, NULL),
(44, NULL, 3, 'setting_item', 21, 0.00000000, 6, NULL),
(45, NULL, 3, 'setting_item', 22, 0.00000000, 4, NULL),
(46, NULL, 3, 'setting_item', 23, 0.00000000, 2, NULL),
(47, NULL, 3, 'setting_item', 1, 0.00000000, 8, NULL),
(48, NULL, 3, 'setting_item', 2, 0.00000000, 2, NULL),
(49, NULL, 3, 'setting_wallet', 3, 50.00000000, 10, NULL),
(50, NULL, 4, 'setting_pet', 6, 0.00000000, 80, NULL),
(51, NULL, 4, 'setting_pet', 7, 0.00000000, 14, NULL),
(52, NULL, 4, 'setting_pet', 8, 0.00000000, 3, NULL),
(53, NULL, 4, 'setting_pet', 9, 0.00000000, 2, NULL),
(54, NULL, 4, 'setting_pet', 10, 0.00000000, 1, NULL),
(55, NULL, 4, 'setting_item', 15, 0.00000000, 20, NULL),
(56, NULL, 4, 'setting_item', 16, 0.00000000, 16, NULL),
(57, NULL, 4, 'setting_item', 17, 0.00000000, 4, NULL),
(58, NULL, 4, 'setting_item', 20, 0.00000000, 20, NULL),
(59, NULL, 4, 'setting_item', 21, 0.00000000, 10, NULL),
(60, NULL, 4, 'setting_item', 22, 0.00000000, 6, NULL),
(61, NULL, 4, 'setting_item', 23, 0.00000000, 4, NULL),
(62, NULL, 4, 'setting_item', 1, 0.00000000, 6, NULL),
(63, NULL, 4, 'setting_item', 2, 0.00000000, 4, NULL),
(64, NULL, 4, 'setting_wallet', 3, 100.00000000, 10, NULL),
(65, NULL, 5, 'setting_pet', 6, 0.00000000, 80, NULL),
(66, NULL, 5, 'setting_pet', 7, 0.00000000, 14, NULL),
(67, NULL, 5, 'setting_pet', 8, 0.00000000, 3, NULL),
(68, NULL, 5, 'setting_pet', 9, 0.00000000, 2, NULL),
(69, NULL, 5, 'setting_pet', 10, 0.00000000, 1, NULL),
(70, NULL, 5, 'setting_item', 16, 0.00000000, 30, NULL),
(71, NULL, 5, 'setting_item', 17, 0.00000000, 6, NULL),
(72, NULL, 5, 'setting_item', 18, 0.00000000, 4, NULL),
(73, NULL, 5, 'setting_item', 21, 0.00000000, 20, NULL),
(74, NULL, 5, 'setting_item', 22, 0.00000000, 10, NULL),
(75, NULL, 5, 'setting_item', 23, 0.00000000, 6, NULL),
(76, NULL, 5, 'setting_item', 24, 0.00000000, 4, NULL),
(77, NULL, 5, 'setting_item', 1, 0.00000000, 6, NULL),
(78, NULL, 5, 'setting_item', 2, 0.00000000, 4, NULL),
(79, NULL, 5, 'setting_wallet', 3, 150.00000000, 10, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_general`
--

CREATE TABLE `sw_setting_general` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `category` varchar(128) DEFAULT NULL,
  `code` varchar(128) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `is_show` tinyint(1) NOT NULL DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_general`
--

INSERT INTO `sw_setting_general` (`id`, `deleted_at`, `category`, `code`, `value`, `is_show`, `remark`) VALUES
(1, NULL, 'log_reader', 'allow_access', '1', 1, NULL),
(2, NULL, 'maintenance', 'stop_dapp', '0', 1, NULL),
(3, NULL, 'maintenance', 'stop_admin', '0', 1, NULL),
(4, NULL, 'maintenance', 'stop_login', '0', 1, NULL),
(5, NULL, 'maintenance', 'stop_register', '0', 1, NULL),
(6, NULL, 'maintenance', 'stop_transfer', '0', 1, NULL),
(7, NULL, 'maintenance', 'stop_swap', '0', 1, NULL),
(8, NULL, 'maintenance', 'stop_deposit', '0', 1, NULL),
(9, NULL, 'maintenance', 'stop_withdraw', '0', 1, NULL),
(10, NULL, 'maintenance', 'stop_mission', '0', 1, NULL),
(11, NULL, 'maintenance', 'stop_purchase', '0', 1, NULL),
(12, NULL, 'maintenance', 'stop_gacha', '0', 1, NULL),
(13, NULL, 'maintenance', 'stop_character', '0', 1, NULL),
(14, NULL, 'maintenance', 'stop_pet', '0', 1, NULL),
(15, NULL, 'maintenance', 'stop_item', '0', 1, NULL),
(16, NULL, 'maintenance', 'stop_battle', '0', 1, NULL),
(17, NULL, 'maintenance', 'stop_market', '0', 1, NULL),
(18, NULL, 'by_pass', 'api', '0', 1, NULL),
(19, NULL, 'withdraw', 'withdraw_min', '10', 1, NULL),
(20, NULL, 'withdraw', 'withdraw_max', '0', 1, NULL),
(21, NULL, 'withdraw', 'withdraw_fee_wallet', '1', 1, NULL),
(22, NULL, 'withdraw', 'withdraw_fee', '1', 1, NULL),
(23, NULL, 'withdraw', 'withdraw_gasprice_multiplier', '1', 1, NULL),
(24, NULL, 'deposit', 'deposit_min', '0.01', 1, NULL),
(25, NULL, 'deposit', 'deposit_max', '0', 1, NULL),
(26, NULL, 'market', 'sales_fee_wallet', '1', 1, NULL),
(27, NULL, 'market', 'sales_fee', '1', 1, NULL),
(28, NULL, 'market', 'sales_min', '1', 1, NULL),
(29, NULL, 'market', 'sales_max', '0', 1, NULL),
(30, NULL, 'telegram', 'bot_name', 'TendopiaBot', 1, NULL),
(31, NULL, 'telegram', 'bot_domain', 'https://stagcore.tendopia.com/dapp/telegram/autoload', 1, NULL),
(32, NULL, 'website', 'dapp_website', 'https://stagdapp.tendopia.com', 1, NULL),
(33, NULL, 'website', 'official_website', 'https://stagweb.tendopia.com', 1, NULL),
(34, NULL, 'pet', 'coma_count_days', '3', 1, NULL),
(35, NULL, 'pet', 'dead_count_days', '30', 1, NULL),
(36, NULL, 'pet', 'normal_refund_price', '1-1', 1, NULL),
(37, NULL, 'pet', 'premium_refund_price', '2-1', 1, NULL),
(38, NULL, 'item', 'refund_price', '1-1', 1, NULL),
(39, NULL, 'slot', 'pet_slot_price', '2-100,2-200,2-300,2-400', 1, NULL),
(40, NULL, 'slot', 'inventory_page_price', '2-100,2-200,2-300,2-400', 1, NULL),
(41, NULL, 'onboarding', 'point', '1000', 1, NULL),
(42, NULL, 'onboarding', 'nft_price', '1000', 1, NULL),
(43, NULL, 'onboarding', 'nft_contract_id', '1', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_item`
--

CREATE TABLE `sw_setting_item` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(128) DEFAULT NULL,
  `normal_price` decimal(20,8) DEFAULT 0.00000000,
  `sales_price` decimal(20,8) DEFAULT 0.00000000,
  `payment_id` int(11) DEFAULT 0 COMMENT 'refer to setting_payment',
  `is_show` tinyint(1) NOT NULL DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_item`
--

INSERT INTO `sw_setting_item` (`id`, `deleted_at`, `image`, `name`, `description`, `category`, `normal_price`, `sales_price`, `payment_id`, `is_show`, `remark`) VALUES
(1, NULL, '/img/tools/egg incubator.png', 'egg hatcher', 'upon use, hatch one egg', 'tools', 500.00000000, 500.00000000, 1, 1, NULL),
(2, NULL, '/img/tools/pet revival.png', 'pet revival', 'upon use, immediately recover one coma pet', 'tools', 200.00000000, 200.00000000, 2, 1, NULL),
(3, NULL, '/img/petfood/drinks.png', 'drinks', 'upon use, immediately restore 25 health', 'pet food', 50.00000000, 50.00000000, 1, 1, NULL),
(4, NULL, '/img/petfood/fish.png', 'fish', 'upon use, immediately restore 50 health', 'pet food', 80.00000000, 80.00000000, 1, 1, NULL),
(5, NULL, '/img/petfood/meat.png', 'meat', 'upon use, immediately restore 100 health', 'pet food', 100.00000000, 100.00000000, 1, 1, NULL),
(6, NULL, '/img/characterfood/apple.png', 'apple', 'recover 10 stamina', 'character food', 100.00000000, 100.00000000, 1, 1, NULL),
(7, NULL, '/img/characterfood/watermelon.png', 'watermelon', 'recover 15 stamina', 'character food', 150.00000000, 150.00000000, 1, 1, NULL),
(8, NULL, '/img/characterfood/pancake.png', 'pancake', 'recover 20 stamina', 'character food', 200.00000000, 200.00000000, 1, 1, NULL),
(9, NULL, '/img/characterfood/pizza.png', 'pizza', 'recover 25 stamina', 'character food', 250.00000000, 250.00000000, 1, 1, NULL),
(10, NULL, '/img/characterfood/tea.png', 'tea', 'recover 30 stamina', 'character food', 300.00000000, 300.00000000, 1, 1, NULL),
(11, NULL, '/img/characterfood/juice.png', 'juice', 'recover 35 stamina', 'character food', 350.00000000, 350.00000000, 1, 1, NULL),
(12, NULL, '/img/potion/normal.png', 'stamina potion', 'recover 20% of max stamina', 'potion', 50.00000000, 50.00000000, 2, 1, NULL),
(13, NULL, '/img/potion/golden.png', 'golden stamina potion', 'recover 50% of max stamina', 'potion', 100.00000000, 100.00000000, 2, 1, NULL),
(14, NULL, '/img/potion/premium.png', 'premium stamina potion', 'recover 80% of max stamina', 'potion', 180.00000000, 180.00000000, 2, 1, NULL),
(15, NULL, '/img/level/pet/mana elixir.png', 'mana elixir', 'pet star up item', 'pet level', 200.00000000, 200.00000000, 1, 1, NULL),
(16, NULL, '/img/level/pet/magic elixir.png', 'magic elixir', 'pet star up item', 'pet level', 300.00000000, 300.00000000, 1, 1, NULL),
(17, NULL, '/img/level/pet/power elixir.png', 'power elixir', 'pet star up item', 'pet level', 0.00000000, 0.00000000, 2, 1, NULL),
(18, NULL, '/img/level/pet/speed elixir.png', 'speed elixir', 'pet star up item', 'pet level', 0.00000000, 0.00000000, 2, 1, NULL),
(19, NULL, '/img/level/character/scroll.png', 'scroll', 'character level up item', 'character level', 100.00000000, 100.00000000, 1, 1, NULL),
(20, NULL, '/img/level/character/mystery ore.png', 'mystery ore', 'character level up item', 'character level', 200.00000000, 200.00000000, 1, 1, NULL),
(21, NULL, '/img/level/character/magic book.png', 'magic book', 'character level up item', 'character level', 300.00000000, 300.00000000, 1, 1, NULL),
(22, NULL, '/img/level/character/diamond.png', 'diamond', 'character level up item', 'character level', 0.00000000, 0.00000000, 2, 1, NULL),
(23, NULL, '/img/level/character/ruby.png', 'ruby', 'character level up item', 'character level', 0.00000000, 0.00000000, 2, 1, NULL),
(24, NULL, '/img/level/character/moonstone.png', 'moonstone', 'character level up item', 'character level', 0.00000000, 0.00000000, 2, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_item_attribute`
--

CREATE TABLE `sw_setting_item_attribute` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `item_id` int(11) DEFAULT 0 COMMENT 'refer to setting_item',
  `attribute_id` int(11) DEFAULT 0 COMMENT 'refer to setting_attribute',
  `value` varchar(255) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_item_attribute`
--

INSERT INTO `sw_setting_item_attribute` (`id`, `deleted_at`, `item_id`, `attribute_id`, `value`, `remark`) VALUES
(1, NULL, 3, 3, '25', NULL),
(2, NULL, 4, 3, '50', NULL),
(3, NULL, 5, 3, '100', NULL),
(4, NULL, 6, 3, '10', NULL),
(5, NULL, 7, 3, '15', NULL),
(6, NULL, 8, 3, '20', NULL),
(7, NULL, 9, 3, '25', NULL),
(8, NULL, 10, 3, '30', NULL),
(9, NULL, 11, 3, '35', NULL),
(10, NULL, 12, 3, '20', NULL),
(11, NULL, 13, 3, '50', NULL),
(12, NULL, 14, 3, '80', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_lang`
--

CREATE TABLE `sw_setting_lang` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `code` varchar(128) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_lang`
--

INSERT INTO `sw_setting_lang` (`id`, `deleted_at`, `code`, `value`, `remark`) VALUES
(1, NULL, 'english', 'en', null),
(2, NULL, 'chinese simplified', 'zh', null),
(3, NULL, 'chinese traditional', 'zh-TW', null),
(4, NULL, 'indonesia', 'id', null),
(5, NULL, 'japan', 'ja', null),
(6, NULL, 'korea', 'ko', null),
(7, NULL, 'thailand', 'th', null),
(8, NULL, 'vietnam', 'vi', null);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_level`
--

CREATE TABLE `sw_setting_level` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `level` int(11) DEFAULT 0,
  `item_required` text DEFAULT NULL,
  `pet_required` text DEFAULT NULL,
  `stamina` int(11) DEFAULT 0,
  `pet_slots` int(11) DEFAULT 0,
  `inventory_pages` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_level`
--

INSERT INTO `sw_setting_level` (`id`, `deleted_at`, `level`, `item_required`, `pet_required`, `stamina`, `pet_slots`, `inventory_pages`, `remark`) VALUES
(1, NULL, 1, NULL, NULL, 10, 1, 1, NULL),
(2, NULL, 2, '{\"19\":\"1\"}', '{\"1\":\"1\"}', 15, 1, 1, NULL),
(3, NULL, 3, '{\"19\":\"2\"}', NULL, 20, 1, 1, NULL),
(4, NULL, 4, '{\"19\":\"3\"}', NULL, 25, 1, 1, NULL),
(5, NULL, 5, '{\"19\":\"4\"}', NULL, 30, 1, 1, NULL),
(6, NULL, 6, '{\"20\":\"1\"}', NULL, 35, 1, 1, NULL),
(7, NULL, 7, '{\"20\":\"2\"}', NULL, 40, 1, 1, NULL),
(8, NULL, 8, '{\"20\":\"3\"}', NULL, 45, 1, 1, NULL),
(9, NULL, 9, '{\"20\":\"4\"}', NULL, 50, 1, 1, NULL),
(10, NULL, 10, '{\"19\":\"4\",\"20\":\"4\"}', NULL, 55, 2, 1, NULL),
(11, NULL, 11, '{\"19\":\"4\",\"20\":\"5\"}', NULL, 60, 2, 1, NULL),
(12, NULL, 12, '{\"19\":\"5\",\"20\":\"5\"}', NULL, 65, 2, 1, NULL),
(13, NULL, 13, '{\"19\":\"5\",\"20\":\"6\"}', NULL, 70, 2, 1, NULL),
(14, NULL, 14, '{\"19\":\"6\",\"20\":\"6\"}', NULL, 75, 2, 1, NULL),
(15, NULL, 15, '{\"20\":\"4\",\"21\":\"4\"}', NULL, 80, 2, 2, NULL),
(16, NULL, 16, '{\"20\":\"4\",\"21\":\"5\"}', NULL, 85, 2, 2, NULL),
(17, NULL, 17, '{\"20\":\"5\",\"21\":\"6\"}', NULL, 90, 2, 2, NULL),
(18, NULL, 18, '{\"20\":\"6\",\"21\":\"6\"}', NULL, 95, 2, 2, NULL),
(19, NULL, 19, '{\"20\":\"6\",\"21\":\"7\"}', NULL, 100, 2, 2, NULL),
(20, NULL, 20, '{\"21\":\"4\",\"22\":\"2\"}', NULL, 105, 3, 2, NULL),
(21, NULL, 21, '{\"21\":\"5\",\"22\":\"2\"}', NULL, 110, 3, 2, NULL),
(22, NULL, 22, '{\"21\":\"6\",\"22\":\"3\"}', NULL, 115, 3, 2, NULL),
(23, NULL, 23, '{\"21\":\"7\",\"22\":\"3\"}', NULL, 120, 3, 2, NULL),
(24, NULL, 24, '{\"20\":\"6\",\"21\":\"4\",\"23\":\"2\"}', NULL, 125, 3, 2, NULL),
(25, NULL, 25, '{\"20\":\"6\",\"21\":\"6\",\"23\":\"2\"}', NULL, 130, 4, 2, NULL),
(26, NULL, 26, '{\"20\":\"6\",\"21\":\"7\",\"23\":\"3\"}', NULL, 135, 4, 2, NULL),
(27, NULL, 27, '{\"20\":\"7\",\"21\":\"7\",\"24\":\"2\"}', NULL, 140, 4, 2, NULL),
(28, NULL, 28, '{\"20\":\"8\",\"21\":\"8\",\"24\":\"2\"}', NULL, 145, 4, 2, NULL),
(29, NULL, 29, '{\"20\":\"8\",\"21\":\"8\",\"24\":\"3\"}', NULL, 150, 4, 2, NULL),
(30, NULL, 30, '{\"22\":\"3\",\"23\":\"3\",\"24\":\"3\"}', NULL, 155, 5, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_mission`
--

CREATE TABLE `sw_setting_mission` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `level` int(11) DEFAULT 0,
  `item_reward` text DEFAULT NULL,
  `pet_reward` text DEFAULT NULL,
  `currency_reward` text DEFAULT NULL,
  `requirement` varchar(128) DEFAULT NULL,
  `action` enum('internal','external','bot') DEFAULT 'internal',
  `type` enum('daily','weekly','permanent','limited','onboarding') DEFAULT 'permanent',
  `stamina` int(11) DEFAULT 0,
  `is_show` tinyint(1) NOT NULL DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_mission`
--

INSERT INTO `sw_setting_mission` (`id`, `deleted_at`, `name`, `description`, `level`, `item_reward`, `pet_reward`, `currency_reward`, `requirement`, `action`, `type`, `stamina`, `is_show`, `remark`) VALUES
(1, NULL, 'socialize in tendopia group', NULL, 0, NULL, NULL, NULL, '5', 'internal', 'onboarding', 0, 1, NULL),
(2, NULL, 'link web3 address', NULL, 0, NULL, NULL, '{\"1\":\"100\"}', NULL, 'internal', 'permanent', 0, 1, NULL),
(3, NULL, 'link telegram', NULL, 0, NULL, NULL, '{\"1\":\"100\"}', NULL, 'internal', 'permanent', 0, 1, NULL),
(4, NULL, 'link X', NULL, 0, NULL, NULL, '{\"1\":\"100\"}', NULL, 'internal', 'permanent', 0, 0, NULL),
(5, NULL, 'bind a referral', NULL, 0, NULL, NULL, '{\"1\":\"100\"}', NULL, 'internal', 'permanent', 0, 1, NULL),
(6, NULL, 'invite 3 users into the game', NULL, 0, NULL, NULL, '{\"1\":\"300\"}', '3', 'internal', 'permanent', 0, 1, NULL),
(7, NULL, 'take your first mission', NULL, 0, '{\"19\":\"1\"}', '{\"1\":\"1\"}', NULL, NULL, 'internal', 'permanent', 0, 1, NULL),
(8, NULL, 'level up your character', NULL, 0, '{\"1\":\"1\"}', NULL, NULL, NULL, 'internal', 'permanent', 0, 1, NULL),
(9, NULL, 'hatch your pet', NULL, 0, '{\"19\":\"2\"}', NULL, NULL, NULL, 'internal', 'permanent', 0, 1, NULL),
(10, NULL, 'assign your pet', NULL, 0, '{\"19\":\"3\"}', NULL, NULL, NULL, 'internal', 'permanent', 0, 1, NULL),
(11, NULL, 'chat in tendopia group I', NULL, 1, '{\"3\":\"1\"}', NULL, '{\"1\":\"30\"}', '5', 'bot', 'daily', 10, 1, NULL),
(12, NULL, 'chat in tendopia group with keyword tendopia I', NULL, 1, '{\"6\":\"1\"}', NULL, '{\"1\":\"30\"}', '5', 'bot', 'daily', 10, 1, NULL),
(13, NULL, 'watch this ads I', NULL, 1, '{\"6\":\"1\"}', NULL, '{\"1\":\"30\"}', 'https://youtu.be/f-1_1ZCQjsM', 'external', 'daily', 10, 1, NULL),
(14, NULL, 'chat in tendopia group II', NULL, 11, '{\"4\":\"1\"}', NULL, '{\"1\":\"60\"}', '10', 'bot', 'daily', 30, 1, NULL),
(15, NULL, 'chat in tendopia group with keyword tendopia II', NULL, 11, '{\"7\":\"1\"}', NULL, '{\"1\":\"60\"}', '10', 'bot', 'daily', 30, 1, NULL),
(16, NULL, 'watch this ads II', NULL, 11, '{\"7\":\"1\"}', NULL, '{\"1\":\"60\"}', 'https://youtu.be/f-1_1ZCQjsM', 'external', 'daily', 30, 1, NULL),
(17, NULL, 'chat in tendopia group III', NULL, 21, '{\"5\":\"1\"}', NULL, '{\"1\":\"120\"}', '15', 'bot', 'daily', 50, 1, NULL),
(18, NULL, 'chat in tendopia group with keyword tendopia III', NULL, 21, '{\"8\":\"1\"}', NULL, '{\"1\":\"120\"}', '15', 'bot', 'daily', 50, 1, NULL),
(19, NULL, 'watch this ads III', NULL, 21, '{\"8\":\"1\"}', NULL, '{\"1\":\"120\"}', 'https://youtu.be/f-1_1ZCQjsM', 'external', 'daily', 50, 1, NULL),
(20, NULL, 'purchase from shop 5 times', NULL, 4, '{\"19\":\"5\"}', NULL, NULL, '5', 'internal', 'permanent', 20, 1, NULL),
(21, NULL, 'purchase chestbox draw 1 for 5 times', NULL, 4, '{\"20\":\"5\"}', NULL, NULL, '5', 'internal', 'permanent', 20, 1, NULL),
(22, NULL, 'feed your pet 5 times', NULL, 4, '{\"4\":\"2\"}', NULL, NULL, '5', 'internal', 'permanent', 20, 1, NULL),
(23, NULL, 'feed your character 5 times', NULL, 4, '{\"8\":\"2\"}', NULL, NULL, '5', 'internal', 'permanent', 20, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_nft`
--

CREATE TABLE `sw_setting_nft` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `token_address` varchar(66) DEFAULT NULL COMMENT 'contract address',
  `network` int(11) DEFAULT 0 COMMENT 'refer to setting_blockchain_network',
  `address` varchar(255) DEFAULT NULL,
  `private_key` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `sw_setting_nft`
--

INSERT INTO `sw_setting_nft` (`id`, `created_at`, `updated_at`, `deleted_at`, `token_address`, `network`, `address`, `private_key`, `is_active`, `remark`) VALUES
(1, '2024-02-21 21:52:40', '2024-02-21 21:52:40', NULL, '0xFA01c173CfA023713b4EbfE347356Ae966428102', 2, '0xBdc76521b93cbF4E1dEf17a8d17a7767A3B85C4c', 'gJ0bJe8Cu1o2ILgNCDt7SpR5m6ODNqznvz79QLm0XxDma/ePCODOe+WHR22ydrJJEVUY43jRXcDC258na1nR59yoIC+fIunXL2gH2p3U7ws=', 1, null);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_operator`
--

CREATE TABLE `sw_setting_operator` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `code` varchar(128) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_operator`
--

INSERT INTO `sw_setting_operator` (`id`, `deleted_at`, `category`, `code`, `remark`) VALUES
(1, NULL, 'status', 'pending', NULL),
(2, NULL, 'status', 'processing', NULL),
(3, NULL, 'status', 'success', NULL),
(4, NULL, 'status', 'failed', NULL),
(5, NULL, 'status', 'accepted', NULL),
(6, NULL, 'status', 'rejected', NULL),
(7, NULL, 'status', 'collected', NULL),
(8, NULL, 'status', 'claimed', NULL),
(9, NULL, 'status', 'completed', NULL),
(10, NULL, 'status', 'expired', NULL),
(11, NULL, 'type', 'admin_top_up', NULL),
(12, NULL, 'type', 'admin_deduct', NULL),
(13, NULL, 'type', 'top_up', NULL),
(14, NULL, 'type', 'deduct', NULL),
(15, NULL, 'type', 'redeem', NULL),
(16, NULL, 'type', 'withdraw', NULL),
(17, NULL, 'type', 'withdraw_fee', NULL),
(18, NULL, 'type', 'withdraw_refund', NULL),
(19, NULL, 'type', 'withdraw_refund_fee', NULL),
(20, NULL, 'type', 'transfer_out', NULL),
(21, NULL, 'type', 'transfer_in', NULL),
(22, NULL, 'type', 'transfer_fee', NULL),
(23, NULL, 'type', 'swap_from', NULL),
(24, NULL, 'type', 'swap_to', NULL),
(25, NULL, 'type', 'swap_fee', NULL),
(26, NULL, 'type', 'purchase', NULL),
(27, NULL, 'type', 'market_sales_payment_out', NULL),
(28, NULL, 'type', 'market_sales_payment_in', NULL),
(29, NULL, 'type', 'market_fee', NULL),
(30, NULL, 'type', 'gacha', NULL),
(31, NULL, 'type', 'gacha_token_reward', NULL),
(32, NULL, 'type', 'character_upgrade', NULL),
(33, NULL, 'type', 'pet_upgrade', NULL),
(34, NULL, 'type', 'pet_slot_unlock', NULL),
(35, NULL, 'type', 'inventory_page_unlock', NULL),
(36, NULL, 'type', 'item_refund', NULL),
(37, NULL, 'type', 'pet_refund', NULL),
(38, NULL, 'reward', 'mining_reward', NULL),
(39, NULL, 'reward', 'battle_reward', NULL),
(40, NULL, 'reward', 'mission_reward', NULL),
(41, NULL, 'reward', 'event_reward', NULL),
(42, NULL, 'battle', 'win', NULL),
(43, NULL, 'battle', 'lose', NULL),
(44, NULL, 'battle', 'draw', NULL),
(45, NULL, 'pet', 'hatching', NULL),
(46, NULL, 'pet', 'healthy', NULL),
(47, NULL, 'pet', 'unhealthy', NULL),
(48, NULL, 'pet', 'fainted', NULL),
(49, NULL, 'pet', 'coma', NULL),
(50, NULL, 'pet', 'dead', NULL),
(51, NULL, 'announcement', 'maintenance', NULL),
(52, NULL, 'announcement', 'news', NULL),
(53, NULL, 'announcement', 'version_update', NULL),
(54, NULL, 'announcement', 'version_upgrade', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_payment`
--

CREATE TABLE `sw_setting_payment` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `code` varchar(128) DEFAULT NULL,
  `filter` text DEFAULT NULL,
  `formula` text DEFAULT NULL,
  `calc_formula` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_payment`
--

INSERT INTO `sw_setting_payment` (`id`, `deleted_at`, `code`, `filter`, `formula`, `calc_formula`, `is_active`, `remark`) VALUES
(1, NULL, 'xtendo', '[\"purchase\"]', '{\"1\":\"1\"}', '{\"1\":\"min\"}', 1, NULL),
(2, NULL, 'gtendo', '[\"purchase\"]', '{\"2\":\"1\"}', '{\"2\":\"min\"}', 1, NULL),
(3, NULL, 'rtendo', '[\"purchase\"]', '{\"3\":\"1\"}', '{\"3\":\"min\"}', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_pet`
--

CREATE TABLE `sw_setting_pet` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gif` varchar(255) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `quality` enum('normal','premium') DEFAULT 'normal',
  `is_show` tinyint(1) NOT NULL DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_pet`
--

INSERT INTO `sw_setting_pet` (`id`, `deleted_at`, `image`, `gif`, `name`, `quality`, `is_show`, `remark`) VALUES
(1, NULL, '/img/pet/static/normal/petn.png', '/img/pet/gif/normal/n.gif', 'slime N', 'normal', 1, NULL),
(2, NULL, '/img/pet/static/normal/petr.png', '/img/pet/gif/normal/r.gif', 'slime R', 'normal', 1, NULL),
(3, NULL, '/img/pet/static/normal/petsr.png', '/img/pet/gif/normal/sr.gif', 'slime SR', 'normal', 1, NULL),
(4, NULL, '/img/pet/static/normal/petssr.png', '/img/pet/gif/normal/ssr.gif', 'slime SSR', 'normal', 1, NULL),
(5, NULL, '/img/pet/static/normal/petsssr.png', '/img/pet/gif/normal/sssr.gif', 'slime SSSR', 'normal', 1, NULL),
(6, NULL, '/img/pet/static/premium/petn.png', '/img/pet/gif/premium/n.gif', 'goo N', 'premium', 1, NULL),
(7, NULL, '/img/pet/static/premium/petr.png', '/img/pet/gif/premium/r.gif', 'goo R ', 'premium', 1, NULL),
(8, NULL, '/img/pet/static/premium/petsr.png', '/img/pet/gif/premium/sr.gif', 'goo SR', 'premium', 1, NULL),
(9, NULL, '/img/pet/static/premium/petssr.png', '/img/pet/gif/premium/ssr.gif', 'goo SSR', 'premium', 1, NULL),
(10, NULL, '/img/pet/static/premium/petsssr.png', '/img/pet/gif/premium/sssr.gif', 'goo SSSR', 'premium', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_pet_attribute`
--

CREATE TABLE `sw_setting_pet_attribute` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `pet_id` int(11) DEFAULT 0 COMMENT 'refer to setting_pet',
  `attribute_id` int(11) DEFAULT 0 COMMENT 'refer to setting_attribute',
  `value` varchar(255) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_pet_attribute`
--

INSERT INTO `sw_setting_pet_attribute` (`id`, `deleted_at`, `pet_id`, `attribute_id`, `value`, `remark`) VALUES
(1, NULL, 1, 1, 'N', NULL),
(2, NULL, 1, 2, '0', NULL),
(3, NULL, 2, 1, 'R', NULL),
(4, NULL, 2, 2, '0', NULL),
(5, NULL, 3, 1, 'SR', NULL),
(6, NULL, 3, 2, '0', NULL),
(7, NULL, 4, 1, 'SSR', NULL),
(8, NULL, 4, 2, '0', NULL),
(9, NULL, 5, 1, 'SSSR', NULL),
(10, NULL, 5, 2, '0', NULL),
(11, NULL, 6, 1, 'N', NULL),
(12, NULL, 6, 2, '0', NULL),
(13, NULL, 7, 1, 'R', NULL),
(14, NULL, 7, 2, '0', NULL),
(15, NULL, 8, 1, 'SR', NULL),
(16, NULL, 8, 2, '0', NULL),
(17, NULL, 9, 1, 'SSR', NULL),
(18, NULL, 9, 2, '0', NULL),
(19, NULL, 10, 1, 'SSSR', NULL),
(20, NULL, 10, 2, '0', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_pet_rank`
--

CREATE TABLE `sw_setting_pet_rank` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `quality` enum('normal','premium') DEFAULT 'normal',
  `rank` varchar(50) DEFAULT NULL,
  `star` int(11) DEFAULT 0,
  `item_required` text DEFAULT NULL,
  `mining_rate` decimal(20,8) DEFAULT 0.00000000,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_pet_rank`
--

INSERT INTO `sw_setting_pet_rank` (`id`, `deleted_at`, `quality`, `rank`, `star`, `item_required`, `mining_rate`, `remark`) VALUES
(1, NULL, 'normal', 'N', 0, NULL, 0.21000000, NULL),
(2, NULL, 'normal', 'N', 1, '{\"15\":\"2\",\"16\":\"1\"}', 0.24000000, NULL),
(3, NULL, 'normal', 'N', 2, '{\"15\":\"2\",\"16\":\"2\",\"17\":\"2\"}', 0.28000000, NULL),
(4, NULL, 'normal', 'N', 3, '{\"16\":\"3\",\"17\":\"3\",\"18\":\"3\"}', 0.31000000, NULL),
(5, NULL, 'normal', 'R', 0, NULL, 0.31000000, NULL),
(6, NULL, 'normal', 'R', 1, '{\"15\":\"2\",\"16\":\"1\"}', 0.35000000, NULL),
(7, NULL, 'normal', 'R', 2, '{\"15\":\"2\",\"16\":\"2\",\"17\":\"2\"}', 0.38000000, NULL),
(8, NULL, 'normal', 'R', 3, '{\"16\":\"3\",\"17\":\"3\",\"18\":\"3\"}', 0.42000000, NULL),
(9, NULL, 'normal', 'SR', 0, NULL, 0.42000000, NULL),
(10, NULL, 'normal', 'SR', 1, '{\"15\":\"2\",\"16\":\"1\"}', 0.45000000, NULL),
(11, NULL, 'normal', 'SR', 2, '{\"15\":\"2\",\"16\":\"2\",\"17\":\"2\"}', 0.49000000, NULL),
(12, NULL, 'normal', 'SR', 3, '{\"16\":\"3\",\"17\":\"3\",\"18\":\"3\"}', 0.52000000, NULL),
(13, NULL, 'normal', 'SSR', 0, NULL, 0.52000000, NULL),
(14, NULL, 'normal', 'SSR', 1, '{\"15\":\"2\",\"16\":\"1\"}', 0.56000000, NULL),
(15, NULL, 'normal', 'SSR', 2, '{\"15\":\"2\",\"16\":\"2\",\"17\":\"2\"}', 0.59000000, NULL),
(16, NULL, 'normal', 'SSR', 3, '{\"16\":\"3\",\"17\":\"3\",\"18\":\"3\"}', 0.63000000, NULL),
(17, NULL, 'normal', 'SSSR', 0, NULL, 0.63000000, NULL),
(18, NULL, 'normal', 'SSSR', 1, '{\"15\":\"2\",\"16\":\"1\"}', 0.66000000, NULL),
(19, NULL, 'normal', 'SSSR', 2, '{\"15\":\"2\",\"16\":\"2\",\"17\":\"2\"}', 0.69000000, NULL),
(20, NULL, 'normal', 'SSSR', 3, '{\"16\":\"3\",\"17\":\"3\",\"18\":\"3\"}', 0.76000000, NULL),
(21, NULL, 'premium', 'N', 0, NULL, 0.07000000, NULL),
(22, NULL, 'premium', 'N', 1, '{\"15\":\"2\",\"16\":\"1\"}', 0.08000000, NULL),
(23, NULL, 'premium', 'N', 2, '{\"15\":\"2\",\"16\":\"2\",\"17\":\"2\"}', 0.10000000, NULL),
(24, NULL, 'premium', 'N', 3, '{\"16\":\"3\",\"17\":\"3\",\"18\":\"3\"}', 0.11000000, NULL),
(25, NULL, 'premium', 'R', 0, NULL, 0.11000000, NULL),
(26, NULL, 'premium', 'R', 1, '{\"15\":\"2\",\"16\":\"1\"}', 0.13000000, NULL),
(27, NULL, 'premium', 'R', 2, '{\"15\":\"2\",\"16\":\"2\",\"17\":\"2\"}', 0.14000000, NULL),
(28, NULL, 'premium', 'R', 3, '{\"16\":\"3\",\"17\":\"3\",\"18\":\"3\"}', 0.15000000, NULL),
(29, NULL, 'premium', 'SR', 0, NULL, 0.17000000, NULL),
(30, NULL, 'premium', 'SR', 1, '{\"15\":\"2\",\"16\":\"1\"}', 0.18000000, NULL),
(31, NULL, 'premium', 'SR', 2, '{\"15\":\"2\",\"16\":\"2\",\"17\":\"2\"}', 0.19000000, NULL),
(32, NULL, 'premium', 'SR', 3, '{\"16\":\"3\",\"17\":\"3\",\"18\":\"3\"}', 0.21000000, NULL),
(33, NULL, 'premium', 'SSR', 0, NULL, 0.21000000, NULL),
(34, NULL, 'premium', 'SSR', 1, '{\"15\":\"2\",\"16\":\"1\"}', 0.22000000, NULL),
(35, NULL, 'premium', 'SSR', 2, '{\"15\":\"2\",\"16\":\"2\",\"17\":\"2\"}', 0.24000000, NULL),
(36, NULL, 'premium', 'SSR', 3, '{\"16\":\"3\",\"17\":\"3\",\"18\":\"3\"}', 0.25000000, NULL),
(37, NULL, 'premium', 'SSSR', 0, NULL, 0.25000000, NULL),
(38, NULL, 'premium', 'SSSR', 1, '{\"15\":\"2\",\"16\":\"1\"}', 0.26000000, NULL),
(39, NULL, 'premium', 'SSSR', 2, '{\"15\":\"2\",\"16\":\"2\",\"17\":\"2\"}', 0.28000000, NULL),
(40, NULL, 'premium', 'SSSR', 3, '{\"16\":\"3\",\"17\":\"3\",\"18\":\"3\"}', 0.29000000, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_reward`
--

CREATE TABLE `sw_setting_reward` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `code` varchar(128) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_reward_attribute`
--

CREATE TABLE `sw_setting_reward_attribute` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `reward_id` int(11) DEFAULT 0 COMMENT 'refer to setting_reward',
  `attribute_id` int(11) DEFAULT 0 COMMENT 'refer to setting_attribute',
  `value` varchar(255) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_wallet`
--

CREATE TABLE `sw_setting_wallet` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `code` varchar(128) DEFAULT NULL,
  `is_deposit` tinyint(1) DEFAULT 0,
  `is_withdraw` tinyint(1) DEFAULT 0,
  `is_transfer` tinyint(1) DEFAULT 0,
  `is_swap` tinyint(1) DEFAULT 0,
  `is_show` tinyint(1) NOT NULL DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_setting_wallet`
--

INSERT INTO `sw_setting_wallet` (`id`, `deleted_at`, `image`, `code`, `is_deposit`, `is_withdraw`, `is_transfer`, `is_swap`, `is_show`, `remark`) VALUES
(1, NULL, '/img/wallet/xtendo.png', 'xtendo', 0, 0, 0, 0, 1, NULL),
(2, NULL, '/img/wallet/gtendo.png', 'gtendo', 1, 1, 0, 0, 1, NULL),
(3, NULL, '/img/wallet/rtendo.png', 'rtendo', 0, 0, 0, 0, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_wallet_attribute`
--

CREATE TABLE `sw_setting_wallet_attribute` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `from_wallet_id` int(11) DEFAULT 0 COMMENT 'refer to setting_wallet',
  `to_wallet_id` int(11) DEFAULT 0 COMMENT 'refer to setting_wallet',
  `fee_wallet_id` int(11) DEFAULT 0 COMMENT 'refer to setting_wallet',
  `to_self` tinyint(4) DEFAULT 0,
  `to_other` tinyint(4) DEFAULT 0,
  `to_self_fee` decimal(8,4) DEFAULT 0.0000,
  `to_other_fee` decimal(8,4) DEFAULT 0.0000,
  `to_self_rate` decimal(8,4) DEFAULT 0.0000,
  `to_other_rate` decimal(8,4) DEFAULT 0.0000,
  `is_show` tinyint(1) NOT NULL DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_setting_withdraw`
--

CREATE TABLE `sw_setting_withdraw` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `coin_id` int(11) DEFAULT 0 COMMENT 'refer to setting_coin',
  `token_address` varchar(66) DEFAULT NULL COMMENT 'contract address',
  `network` int(11) DEFAULT 0 COMMENT 'refer to setting_blockchain_network',
  `address` varchar(255) DEFAULT NULL,
  `private_key` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `sw_setting_withdraw`
--

INSERT INTO `sw_setting_withdraw` (`id`, `created_at`, `updated_at`, `deleted_at`, `coin_id`, `token_address`, `network`, `address`, `private_key`, `is_active`, `remark`) VALUES
(1, '2024-02-28 12:47:05', '2024-02-28 12:47:05', NULL, 1, '0x81b6420daD8b13388444EB85DbFc4F157dDbc2b0', 2, '0xBdc76521b93cbF4E1dEf17a8d17a7767A3B85C4c', 'gJ0bJe8Cu1o2ILgNCDt7SpR5m6ODNqznvz79QLm0XxDma/ePCODOe+WHR22ydrJJEVUY43jRXcDC258na1nR59yoIC+fIunXL2gH2p3U7ws=', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_battle`
--

CREATE TABLE `sw_user_battle` (
  `id` bigint(20) NOT NULL,
  `sn` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `user_pet_id` int(11) DEFAULT 0 COMMENT 'refer to user_pet',
  `opponent_uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `opponent_pet_id` int(11) DEFAULT 0 COMMENT 'refer to user_pet',
  `result` int(11) DEFAULT 0 COMMENT 'refer to setting_operator',
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_deposit`
--

CREATE TABLE `sw_user_deposit` (
  `id` bigint(20) NOT NULL,
  `sn` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `amount` decimal(20,8) DEFAULT 0.00000000,
  `status` int(11) DEFAULT 0 COMMENT 'refer to setting_operator',
  `coin_id` int(11) DEFAULT 0 COMMENT 'refer to setting_coin',
  `txid` varchar(66) DEFAULT NULL,
  `log_index` varchar(64) DEFAULT NULL,
  `from_address` varchar(66) DEFAULT NULL,
  `to_address` varchar(66) DEFAULT NULL,
  `network` int(11) DEFAULT 0 COMMENT 'refer to setting_blockchain_network',
  `token_address` varchar(66) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_gacha`
--

CREATE TABLE `sw_user_gacha` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `gacha_id` int(11) DEFAULT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `wallet_id` int(11) DEFAULT NULL,
  `token_reward` decimal(20,8) DEFAULT 0.00000000,
  `ref_table` varchar(64) DEFAULT NULL,
  `ref_id` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_inventory`
--

CREATE TABLE `sw_user_inventory` (
  `id` bigint(20) NOT NULL,
  `sn` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `removed_at` datetime DEFAULT NULL,
  `marketed_at` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `item_id` int(11) DEFAULT 0 COMMENT 'refer to setting_item',
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_level`
--

CREATE TABLE `sw_user_level` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `level` int(11) DEFAULT 0,
  `pet_slots` int(11) DEFAULT 0,
  `inventory_pages` int(11) DEFAULT 0,
  `is_current` tinyint(1) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_user_level`
--

INSERT INTO `sw_user_level` (`id`, `created_at`, `updated_at`, `deleted_at`, `uid`, `level`, `pet_slots`, `inventory_pages`, `is_current`, `remark`) VALUES
(1, '2024-02-23 21:18:39', '2024-02-23 21:18:39', NULL, 1, 1, 1, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_market`
--

CREATE TABLE `sw_user_market` (
  `id` bigint(20) NOT NULL,
  `sn` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `removed_at` datetime DEFAULT NULL,
  `sold_at` datetime DEFAULT NULL,
  `seller_uid` int(11) DEFAULT NULL COMMENT 'refer to account_user',
  `buyer_uid` int(11) DEFAULT NULL COMMENT 'refer to account_user',
  `amount` decimal(20,8) DEFAULT 0.00000000,
  `fee` decimal(20,8) DEFAULT 0.00000000,
  `amount_wallet_id` int(11) DEFAULT 0 COMMENT 'refer to setting_wallet',
  `fee_wallet_id` int(11) DEFAULT 0 COMMENT 'refer to setting_wallet',
  `ref_table` varchar(64) DEFAULT NULL,
  `ref_id` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_mission`
--

CREATE TABLE `sw_user_mission` (
  `id` bigint(20) NOT NULL,
  `sn` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `mission_id` int(11) DEFAULT 0 COMMENT 'refer to setting_mission',
  `status` int(11) DEFAULT 0 COMMENT 'refer to setting_operator',
  `progress` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_user_mission`
--

INSERT INTO `sw_user_mission` (`id`, `sn`, `created_at`, `updated_at`, `deleted_at`, `expired_at`, `uid`, `mission_id`, `status`, `progress`, `remark`) VALUES
(1, 'GUWXQKRRR087CPEE', '2024-02-23 21:18:39', '2024-03-05 12:28:02', NULL, NULL, 1, 1, 1, 0, NULL),
(2, 'Z33Q2942NRW8BA38', '2024-02-23 21:18:39', '2024-02-23 21:18:39', NULL, NULL, 1, 2, 9, 100, NULL),
(3, '5XULRU2KS28Q1PJG', '2024-02-23 21:18:39', '2024-02-23 21:18:39', NULL, NULL, 1, 3, 1, 0, NULL),
(4, 'N6TWRX7RA2OBYNI1', '2024-02-23 21:18:39', '2024-02-23 21:18:39', NULL, NULL, 1, 4, 1, 0, NULL),
(5, 'NVTG1JTXIP8MXPV7', '2024-02-23 21:18:39', '2024-02-23 21:18:39', NULL, NULL, 1, 5, 1, 0, NULL),
(6, '7Z58OG3L4XC6XZB1', '2024-02-23 21:18:39', '2024-02-23 21:18:39', NULL, NULL, 1, 6, 1, 0, NULL),
(7, 'WGM7QB5WZWMHHI6P', '2024-02-23 21:18:39', '2024-02-23 21:18:39', NULL, NULL, 1, 7, 1, 0, NULL),
(8, 'DUU58VWHMV1YD74G', '2024-02-23 21:18:39', '2024-02-23 21:18:39', NULL, NULL, 1, 8, 1, 0, NULL),
(9, '8VOYY7BAYV4RDCFC', '2024-02-23 21:18:39', '2024-02-23 21:18:39', NULL, NULL, 1, 9, 1, 0, NULL),
(10, 'KWXCUS2PRMZF4E83', '2024-02-23 21:18:39', '2024-02-23 21:18:39', NULL, NULL, 1, 10, 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_nft`
--

CREATE TABLE `sw_user_nft` (
  `id` bigint(20) NOT NULL,
  `sn` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT 0,
  `message` text DEFAULT NULL,
  `signed_message` text DEFAULT NULL,
  `status` int(11) DEFAULT 0 COMMENT 'refer to setting_operator',
  `txid` varchar(66) DEFAULT NULL,
  `log_index` varchar(64) DEFAULT NULL,
  `from_address` varchar(66) DEFAULT NULL,
  `to_address` varchar(66) DEFAULT NULL,
  `network` int(11) DEFAULT 0 COMMENT 'refer to setting_blockchain_network',
  `token_address` varchar(66) DEFAULT NULL,
  `ref_table` varchar(64) DEFAULT NULL,
  `ref_id` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_pet`
--

CREATE TABLE `sw_user_pet` (
  `id` bigint(20) NOT NULL,
  `sn` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `mining_cutoff_at` datetime DEFAULT NULL,
  `health_pause_at` datetime DEFAULT NULL,
  `health_end_at` datetime DEFAULT NULL,
  `removed_at` datetime DEFAULT NULL,
  `marketed_at` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `pet_id` int(11) DEFAULT 0 COMMENT 'refer to setting_pet',
  `quality` enum('normal','premium') DEFAULT 'normal',
  `rank` varchar(50) DEFAULT NULL,
  `star` int(11) DEFAULT 0,
  `mining_rate` decimal(20,8) DEFAULT 0.00000000,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_point`
--

CREATE TABLE `sw_user_point` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account user',
  `from_uid` int(11) DEFAULT 0 COMMENT 'refer to account user',
  `point` decimal(20,8) DEFAULT 0.00000000,
  `source` enum('claim','referral','purchase_nft','refund_nft') DEFAULT 'claim',
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_remark`
--

CREATE TABLE `sw_user_remark` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `admin_id` int(11) DEFAULT 0 COMMENT 'refer to account admin',
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account user',
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_stamina`
--

CREATE TABLE `sw_user_stamina` (
  `id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `current_stamina` int(11) DEFAULT 0,
  `max_stamina` int(11) DEFAULT 0,
  `usage` decimal(20,8) DEFAULT 1.00000000,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sw_user_stamina`
--

INSERT INTO `sw_user_stamina` (`id`, `deleted_at`, `uid`, `current_stamina`, `max_stamina`, `usage`, `remark`) VALUES
(1, NULL, 1, 10, 10, 1.00000000, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sw_user_withdraw`
--

CREATE TABLE `sw_user_withdraw` (
  `id` bigint(20) NOT NULL,
  `sn` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT 0,
  `amount` decimal(20,8) DEFAULT 0.00000000,
  `fee` decimal(20,8) DEFAULT 0.00000000,
  `distribution` text DEFAULT NULL,
  `status` int(11) DEFAULT 0 COMMENT 'refer to setting_operator',
  `amount_wallet_id` int(11) DEFAULT 0 COMMENT 'refer to setting_wallet',
  `fee_wallet_id` int(11) DEFAULT 0 COMMENT 'refer to setting_wallet',
  `to_coin_id` int(11) DEFAULT 0 COMMENT 'refer to setting_coin',
  `txid` varchar(66) DEFAULT NULL,
  `log_index` varchar(64) DEFAULT NULL,
  `from_address` varchar(66) DEFAULT NULL,
  `to_address` varchar(66) DEFAULT NULL,
  `network` int(11) DEFAULT 0 COMMENT ' refer to setting_blockchain_network',
  `token_address` varchar(66) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_wallet_transaction`
--

CREATE TABLE `sw_wallet_transaction` (
  `id` bigint(20) NOT NULL,
  `sn` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime DEFAULT NULL,
  `used_at` varchar(50) DEFAULT NULL,
  `transaction_type` int(11) DEFAULT 0 COMMENT 'refer to setting_operator',
  `uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `from_uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `to_uid` int(11) DEFAULT 0 COMMENT 'refer to account_user',
  `amount` decimal(20,8) DEFAULT 0.00000000,
  `distribution` text DEFAULT NULL,
  `ref_table` varchar(64) DEFAULT NULL,
  `ref_id` text DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sw_wallet_transaction_detail`
--

CREATE TABLE `sw_wallet_transaction_detail` (
  `id` bigint(20) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `wallet_transaction_id` int(11) DEFAULT 0 COMMENT 'refer to wallet_transaction',
  `wallet_id` int(11) DEFAULT 0 COMMENT 'refer to setting_wallet',
  `amount` decimal(20,8) DEFAULT 0.00000000,
  `before_amount` decimal(20,8) DEFAULT 0.00000000,
  `after_amount` decimal(20,8) DEFAULT 0.00000000,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_callback_query`
--

CREATE TABLE `tg_callback_query` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique identifier for this query',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'Unique user identifier',
  `chat_id` bigint(20) DEFAULT NULL COMMENT 'Unique chat identifier',
  `message_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Unique message identifier',
  `inline_message_id` char(255) DEFAULT NULL COMMENT 'Identifier of the message sent via the bot in inline mode, that originated the query',
  `chat_instance` char(255) NOT NULL DEFAULT '' COMMENT 'Global identifier, uniquely corresponding to the chat to which the message with the callback button was sent',
  `data` char(255) NOT NULL DEFAULT '' COMMENT 'Data associated with the callback button',
  `game_short_name` char(255) NOT NULL DEFAULT '' COMMENT 'Short name of a Game to be returned, serves as the unique identifier for the game',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_chat`
--

CREATE TABLE `tg_chat` (
  `id` bigint(20) NOT NULL COMMENT 'Unique identifier for this chat',
  `type` enum('private','group','supergroup','channel') NOT NULL COMMENT 'Type of chat, can be either private, group, supergroup or channel',
  `title` char(255) DEFAULT '' COMMENT 'Title, for supergroups, channels and group chats',
  `username` char(255) DEFAULT NULL COMMENT 'Username, for private chats, supergroups and channels if available',
  `first_name` char(255) DEFAULT NULL COMMENT 'First name of the other party in a private chat',
  `last_name` char(255) DEFAULT NULL COMMENT 'Last name of the other party in a private chat',
  `is_forum` tinyint(1) DEFAULT 0 COMMENT 'True, if the supergroup chat is a forum (has topics enabled)',
  `all_members_are_administrators` tinyint(1) DEFAULT 0 COMMENT 'True if a all members of this group are admins',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date update',
  `old_id` bigint(20) DEFAULT NULL COMMENT 'Unique chat identifier, this is filled when a group is converted to a supergroup'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_chat_join_request`
--

CREATE TABLE `tg_chat_join_request` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique identifier for this entry',
  `chat_id` bigint(20) NOT NULL COMMENT 'Chat to which the request was sent',
  `user_id` bigint(20) NOT NULL COMMENT 'User that sent the join request',
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Date the request was sent in Unix time',
  `bio` text DEFAULT NULL COMMENT 'Optional. Bio of the user',
  `invite_link` text DEFAULT NULL COMMENT 'Optional. Chat invite link that was used by the user to send the join request',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_chat_member_updated`
--

CREATE TABLE `tg_chat_member_updated` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique identifier for this entry',
  `chat_id` bigint(20) NOT NULL COMMENT 'Chat the user belongs to',
  `user_id` bigint(20) NOT NULL COMMENT 'Performer of the action, which resulted in the change',
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Date the change was done in Unix time',
  `old_chat_member` text NOT NULL COMMENT 'Previous information about the chat member',
  `new_chat_member` text NOT NULL COMMENT 'New information about the chat member',
  `invite_link` text DEFAULT NULL COMMENT 'Chat invite link, which was used by the user to join the chat; for joining by invite link events only',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_chosen_inline_result`
--

CREATE TABLE `tg_chosen_inline_result` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique identifier for this entry',
  `result_id` char(255) NOT NULL DEFAULT '' COMMENT 'The unique identifier for the result that was chosen',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'The user that chose the result',
  `location` char(255) DEFAULT NULL COMMENT 'Sender location, only for bots that require user location',
  `inline_message_id` char(255) DEFAULT NULL COMMENT 'Identifier of the sent inline message',
  `query` text NOT NULL COMMENT 'The query that was used to obtain the result',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_conversation`
--

CREATE TABLE `tg_conversation` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique identifier for this entry',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'Unique user identifier',
  `chat_id` bigint(20) DEFAULT NULL COMMENT 'Unique user or chat identifier',
  `status` enum('active','cancelled','stopped') NOT NULL DEFAULT 'active' COMMENT 'Conversation state',
  `command` varchar(160) DEFAULT '' COMMENT 'Default command to execute',
  `notes` text DEFAULT NULL COMMENT 'Data stored from command',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date update'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_edited_message`
--

CREATE TABLE `tg_edited_message` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique identifier for this entry',
  `chat_id` bigint(20) DEFAULT NULL COMMENT 'Unique chat identifier',
  `message_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Unique message identifier',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'Unique user identifier',
  `edit_date` timestamp NULL DEFAULT NULL COMMENT 'Date the message was edited in timestamp format',
  `text` text DEFAULT NULL COMMENT 'For text messages, the actual UTF-8 text of the message max message length 4096 char utf8',
  `entities` text DEFAULT NULL COMMENT 'For text messages, special entities like usernames, URLs, bot commands, etc. that appear in the text',
  `caption` text DEFAULT NULL COMMENT 'For message with caption, the actual UTF-8 text of the caption'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_inline_query`
--

CREATE TABLE `tg_inline_query` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique identifier for this query',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'Unique user identifier',
  `location` char(255) DEFAULT NULL COMMENT 'Location of the user',
  `query` text NOT NULL COMMENT 'Text of the query',
  `offset` char(255) DEFAULT NULL COMMENT 'Offset of the result',
  `chat_type` char(255) DEFAULT NULL COMMENT 'Optional. Type of the chat, from which the inline query was sent.',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_message`
--

CREATE TABLE `tg_message` (
  `chat_id` bigint(20) NOT NULL COMMENT 'Unique chat identifier',
  `sender_chat_id` bigint(20) DEFAULT NULL COMMENT 'Sender of the message, sent on behalf of a chat',
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique message identifier',
  `message_thread_id` bigint(20) DEFAULT NULL COMMENT 'Unique identifier of a message thread to which the message belongs; for supergroups only',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'Unique user identifier',
  `date` timestamp NULL DEFAULT NULL COMMENT 'Date the message was sent in timestamp format',
  `forward_from` bigint(20) DEFAULT NULL COMMENT 'Unique user identifier, sender of the original message',
  `forward_from_chat` bigint(20) DEFAULT NULL COMMENT 'Unique chat identifier, chat the original message belongs to',
  `forward_from_message_id` bigint(20) DEFAULT NULL COMMENT 'Unique chat identifier of the original message in the channel',
  `forward_signature` text DEFAULT NULL COMMENT 'For messages forwarded from channels, signature of the post author if present',
  `forward_sender_name` text DEFAULT NULL COMMENT 'Sender''s name for messages forwarded from users who disallow adding a link to their account in forwarded messages',
  `forward_date` timestamp NULL DEFAULT NULL COMMENT 'date the original message was sent in timestamp format',
  `is_topic_message` tinyint(1) DEFAULT 0 COMMENT 'True, if the message is sent to a forum topic',
  `is_automatic_forward` tinyint(1) DEFAULT 0 COMMENT 'True, if the message is a channel post that was automatically forwarded to the connected discussion group',
  `reply_to_chat` bigint(20) DEFAULT NULL COMMENT 'Unique chat identifier',
  `reply_to_message` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Message that this message is reply to',
  `via_bot` bigint(20) DEFAULT NULL COMMENT 'Optional. Bot through which the message was sent',
  `edit_date` timestamp NULL DEFAULT NULL COMMENT 'Date the message was last edited in Unix time',
  `has_protected_content` tinyint(1) DEFAULT 0 COMMENT 'True, if the message can''t be forwarded',
  `media_group_id` text DEFAULT NULL COMMENT 'The unique identifier of a media message group this message belongs to',
  `author_signature` text DEFAULT NULL COMMENT 'Signature of the post author for messages in channels',
  `text` text DEFAULT NULL COMMENT 'For text messages, the actual UTF-8 text of the message max message length 4096 char utf8mb4',
  `entities` text DEFAULT NULL COMMENT 'For text messages, special entities like usernames, URLs, bot commands, etc. that appear in the text',
  `caption_entities` text DEFAULT NULL COMMENT 'For messages with a caption, special entities like usernames, URLs, bot commands, etc. that appear in the caption',
  `audio` text DEFAULT NULL COMMENT 'Audio object. Message is an audio file, information about the file',
  `document` text DEFAULT NULL COMMENT 'Document object. Message is a general file, information about the file',
  `animation` text DEFAULT NULL COMMENT 'Message is an animation, information about the animation',
  `game` text DEFAULT NULL COMMENT 'Game object. Message is a game, information about the game',
  `photo` text DEFAULT NULL COMMENT 'Array of PhotoSize objects. Message is a photo, available sizes of the photo',
  `sticker` text DEFAULT NULL COMMENT 'Sticker object. Message is a sticker, information about the sticker',
  `story` text DEFAULT NULL COMMENT 'Story object. Message is a forwarded story',
  `video` text DEFAULT NULL COMMENT 'Video object. Message is a video, information about the video',
  `voice` text DEFAULT NULL COMMENT 'Voice Object. Message is a Voice, information about the Voice',
  `video_note` text DEFAULT NULL COMMENT 'VoiceNote Object. Message is a Video Note, information about the Video Note',
  `caption` text DEFAULT NULL COMMENT 'For message with caption, the actual UTF-8 text of the caption',
  `has_media_spoiler` tinyint(1) DEFAULT 0 COMMENT 'True, if the message media is covered by a spoiler animation',
  `contact` text DEFAULT NULL COMMENT 'Contact object. Message is a shared contact, information about the contact',
  `location` text DEFAULT NULL COMMENT 'Location object. Message is a shared location, information about the location',
  `venue` text DEFAULT NULL COMMENT 'Venue object. Message is a Venue, information about the Venue',
  `poll` text DEFAULT NULL COMMENT 'Poll object. Message is a native poll, information about the poll',
  `dice` text DEFAULT NULL COMMENT 'Message is a dice with random value from 1 to 6',
  `new_chat_members` text DEFAULT NULL COMMENT 'List of unique user identifiers, new member(s) were added to the group, information about them (one of these members may be the bot itself)',
  `left_chat_member` bigint(20) DEFAULT NULL COMMENT 'Unique user identifier, a member was removed from the group, information about them (this member may be the bot itself)',
  `new_chat_title` char(255) DEFAULT NULL COMMENT 'A chat title was changed to this value',
  `new_chat_photo` text DEFAULT NULL COMMENT 'Array of PhotoSize objects. A chat photo was change to this value',
  `delete_chat_photo` tinyint(1) DEFAULT 0 COMMENT 'Informs that the chat photo was deleted',
  `group_chat_created` tinyint(1) DEFAULT 0 COMMENT 'Informs that the group has been created',
  `supergroup_chat_created` tinyint(1) DEFAULT 0 COMMENT 'Informs that the supergroup has been created',
  `channel_chat_created` tinyint(1) DEFAULT 0 COMMENT 'Informs that the channel chat has been created',
  `message_auto_delete_timer_changed` text DEFAULT NULL COMMENT 'MessageAutoDeleteTimerChanged object. Message is a service message: auto-delete timer settings changed in the chat',
  `migrate_to_chat_id` bigint(20) DEFAULT NULL COMMENT 'Migrate to chat identifier. The group has been migrated to a supergroup with the specified identifier',
  `migrate_from_chat_id` bigint(20) DEFAULT NULL COMMENT 'Migrate from chat identifier. The supergroup has been migrated from a group with the specified identifier',
  `pinned_message` text DEFAULT NULL COMMENT 'Message object. Specified message was pinned',
  `invoice` text DEFAULT NULL COMMENT 'Message is an invoice for a payment, information about the invoice',
  `successful_payment` text DEFAULT NULL COMMENT 'Message is a service message about a successful payment, information about the payment',
  `user_shared` text DEFAULT NULL COMMENT 'Optional. Service message: a user was shared with the bot',
  `chat_shared` text DEFAULT NULL COMMENT 'Optional. Service message: a chat was shared with the bot',
  `connected_website` text DEFAULT NULL COMMENT 'The domain name of the website on which the user has logged in.',
  `write_access_allowed` text DEFAULT NULL COMMENT 'Service message: the user allowed the bot added to the attachment menu to write messages',
  `passport_data` text DEFAULT NULL COMMENT 'Telegram Passport data',
  `proximity_alert_triggered` text DEFAULT NULL COMMENT 'Service message. A user in the chat triggered another user''s proximity alert while sharing Live Location.',
  `forum_topic_created` text DEFAULT NULL COMMENT 'Service message: forum topic created',
  `forum_topic_edited` text DEFAULT NULL COMMENT 'Service message: forum topic edited',
  `forum_topic_closed` text DEFAULT NULL COMMENT 'Service message: forum topic closed',
  `forum_topic_reopened` text DEFAULT NULL COMMENT 'Service message: forum topic reopened',
  `general_forum_topic_hidden` text DEFAULT NULL COMMENT 'Service message: the General forum topic hidden',
  `general_forum_topic_unhidden` text DEFAULT NULL COMMENT 'Service message: the General forum topic unhidden',
  `video_chat_scheduled` text DEFAULT NULL COMMENT 'Service message: video chat scheduled',
  `video_chat_started` text DEFAULT NULL COMMENT 'Service message: video chat started',
  `video_chat_ended` text DEFAULT NULL COMMENT 'Service message: video chat ended',
  `video_chat_participants_invited` text DEFAULT NULL COMMENT 'Service message: new participants invited to a video chat',
  `web_app_data` text DEFAULT NULL COMMENT 'Service message: data sent by a Web App',
  `reply_markup` text DEFAULT NULL COMMENT 'Inline keyboard attached to the message'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_poll`
--

CREATE TABLE `tg_poll` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique poll identifier',
  `question` text NOT NULL COMMENT 'Poll question',
  `options` text NOT NULL COMMENT 'List of poll options',
  `total_voter_count` int(10) UNSIGNED DEFAULT NULL COMMENT 'Total number of users that voted in the poll',
  `is_closed` tinyint(1) DEFAULT 0 COMMENT 'True, if the poll is closed',
  `is_anonymous` tinyint(1) DEFAULT 1 COMMENT 'True, if the poll is anonymous',
  `type` char(255) DEFAULT NULL COMMENT 'Poll type, currently can be “regular” or “quiz”',
  `allows_multiple_answers` tinyint(1) DEFAULT 0 COMMENT 'True, if the poll allows multiple answers',
  `correct_option_id` int(10) UNSIGNED DEFAULT NULL COMMENT '0-based identifier of the correct answer option. Available only for polls in the quiz mode, which are closed, or was sent (not forwarded) by the bot or to the private chat with the bot.',
  `explanation` varchar(255) DEFAULT NULL COMMENT 'Text that is shown when a user chooses an incorrect answer or taps on the lamp icon in a quiz-style poll, 0-200 characters',
  `explanation_entities` text DEFAULT NULL COMMENT 'Special entities like usernames, URLs, bot commands, etc. that appear in the explanation',
  `open_period` int(10) UNSIGNED DEFAULT NULL COMMENT 'Amount of time in seconds the poll will be active after creation',
  `close_date` timestamp NULL DEFAULT NULL COMMENT 'Point in time (Unix timestamp) when the poll will be automatically closed',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_poll_answer`
--

CREATE TABLE `tg_poll_answer` (
  `poll_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique poll identifier',
  `user_id` bigint(20) NOT NULL COMMENT 'The user, who changed the answer to the poll',
  `option_ids` text NOT NULL COMMENT '0-based identifiers of answer options, chosen by the user. May be empty if the user retracted their vote.',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_pre_checkout_query`
--

CREATE TABLE `tg_pre_checkout_query` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique query identifier',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'User who sent the query',
  `currency` char(3) DEFAULT NULL COMMENT 'Three-letter ISO 4217 currency code',
  `total_amount` bigint(20) DEFAULT NULL COMMENT 'Total price in the smallest units of the currency',
  `invoice_payload` char(255) NOT NULL DEFAULT '' COMMENT 'Bot specified invoice payload',
  `shipping_option_id` char(255) DEFAULT NULL COMMENT 'Identifier of the shipping option chosen by the user',
  `order_info` text DEFAULT NULL COMMENT 'Order info provided by the user',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_request_limiter`
--

CREATE TABLE `tg_request_limiter` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique identifier for this entry',
  `chat_id` char(255) DEFAULT NULL COMMENT 'Unique chat identifier',
  `inline_message_id` char(255) DEFAULT NULL COMMENT 'Identifier of the sent inline message',
  `method` char(255) DEFAULT NULL COMMENT 'Request method',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_shipping_query`
--

CREATE TABLE `tg_shipping_query` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Unique query identifier',
  `user_id` bigint(20) DEFAULT NULL COMMENT 'User who sent the query',
  `invoice_payload` char(255) NOT NULL DEFAULT '' COMMENT 'Bot specified invoice payload',
  `shipping_address` char(255) NOT NULL DEFAULT '' COMMENT 'User specified shipping address',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_telegram_update`
--

CREATE TABLE `tg_telegram_update` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT 'Update''s unique identifier',
  `chat_id` bigint(20) DEFAULT NULL COMMENT 'Unique chat identifier',
  `message_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'New incoming message of any kind - text, photo, sticker, etc.',
  `edited_message_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'New version of a message that is known to the bot and was edited',
  `channel_post_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'New incoming channel post of any kind - text, photo, sticker, etc.',
  `edited_channel_post_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'New version of a channel post that is known to the bot and was edited',
  `inline_query_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'New incoming inline query',
  `chosen_inline_result_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'The result of an inline query that was chosen by a user and sent to their chat partner',
  `callback_query_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'New incoming callback query',
  `shipping_query_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'New incoming shipping query. Only for invoices with flexible price',
  `pre_checkout_query_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'New incoming pre-checkout query. Contains full information about checkout',
  `poll_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'New poll state. Bots receive only updates about polls, which are sent or stopped by the bot',
  `poll_answer_poll_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'A user changed their answer in a non-anonymous poll. Bots receive new votes only in polls that were sent by the bot itself.',
  `my_chat_member_updated_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'The bot''s chat member status was updated in a chat. For private chats, this update is received only when the bot is blocked or unblocked by the user.',
  `chat_member_updated_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'A chat member''s status was updated in a chat. The bot must be an administrator in the chat and must explicitly specify “chat_member” in the list of allowed_updates to receive these updates.',
  `chat_join_request_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'A request to join the chat has been sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_user`
--

CREATE TABLE `tg_user` (
  `id` bigint(20) NOT NULL COMMENT 'Unique identifier for this user or bot',
  `is_bot` tinyint(1) DEFAULT 0 COMMENT 'True, if this user is a bot',
  `first_name` char(255) NOT NULL DEFAULT '' COMMENT 'User''s or bot''s first name',
  `last_name` char(255) DEFAULT NULL COMMENT 'User''s or bot''s last name',
  `username` char(191) DEFAULT NULL COMMENT 'User''s or bot''s username',
  `language_code` char(10) DEFAULT NULL COMMENT 'IETF language tag of the user''s language',
  `is_premium` tinyint(1) DEFAULT 0 COMMENT 'True, if this user is a Telegram Premium user',
  `added_to_attachment_menu` tinyint(1) DEFAULT 0 COMMENT 'True, if this user added the bot to the attachment menu',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date creation',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Entry date update'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tg_user_chat`
--

CREATE TABLE `tg_user_chat` (
  `user_id` bigint(20) NOT NULL COMMENT 'Unique user identifier',
  `chat_id` bigint(20) NOT NULL COMMENT 'Unique user or chat identifier'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sw_account_admin`
--
ALTER TABLE `sw_account_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_id` (`admin_id`),
  ADD KEY `web3_address` (`web3_address`);

--
-- Indexes for table `sw_account_user`
--
ALTER TABLE `sw_account_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `web3_address` (`web3_address`);

--
-- Indexes for table `sw_admin_permission`
--
ALTER TABLE `sw_admin_permission`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_log_admin`
--
ALTER TABLE `sw_log_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`admin_uid`),
  ADD KEY `by_uid` (`by_admin_uid`);

--
-- Indexes for table `sw_log_api`
--
ALTER TABLE `sw_log_api`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sn` (`sn`);

--
-- Indexes for table `sw_log_cronjob`
--
ALTER TABLE `sw_log_cronjob`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_log_user`
--
ALTER TABLE `sw_log_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `by_uid` (`by_uid`);

--
-- Indexes for table `sw_network_sponsor`
--
ALTER TABLE `sw_network_sponsor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `upline_uid` (`upline_uid`);

--
-- Indexes for table `sw_permission_template`
--
ALTER TABLE `sw_permission_template`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_permission_warehouse`
--
ALTER TABLE `sw_permission_warehouse`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_reward_record`
--
ALTER TABLE `sw_reward_record`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bonus_type` (`reward_type`),
  ADD KEY `uid` (`uid`),
  ADD KEY `from_uid` (`from_uid`),
  ADD KEY `user_pet_id` (`user_pet_id`),
  ADD KEY `from_user_pet_id` (`from_user_pet_id`);

--
-- Indexes for table `sw_setting_announcement`
--
ALTER TABLE `sw_setting_announcement`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_attribute`
--
ALTER TABLE `sw_setting_attribute`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_blockchain_network`
--
ALTER TABLE `sw_setting_blockchain_network`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_coin`
--
ALTER TABLE `sw_setting_coin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_deposit`
--
ALTER TABLE `sw_setting_deposit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coin_id` (`coin_id`);

--
-- Indexes for table `sw_setting_gacha`
--
ALTER TABLE `sw_setting_gacha`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_gacha_item`
--
ALTER TABLE `sw_setting_gacha_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_general`
--
ALTER TABLE `sw_setting_general`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_item`
--
ALTER TABLE `sw_setting_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_item_attribute`
--
ALTER TABLE `sw_setting_item_attribute`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_lang`
--
ALTER TABLE `sw_setting_lang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_level`
--
ALTER TABLE `sw_setting_level`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_mission`
--
ALTER TABLE `sw_setting_mission`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_nft`
--
ALTER TABLE `sw_setting_nft`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_operator`
--
ALTER TABLE `sw_setting_operator`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_payment`
--
ALTER TABLE `sw_setting_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_pet`
--
ALTER TABLE `sw_setting_pet`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_pet_attribute`
--
ALTER TABLE `sw_setting_pet_attribute`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_pet_rank`
--
ALTER TABLE `sw_setting_pet_rank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_reward`
--
ALTER TABLE `sw_setting_reward`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_reward_attribute`
--
ALTER TABLE `sw_setting_reward_attribute`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_wallet`
--
ALTER TABLE `sw_setting_wallet`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_wallet_attribute`
--
ALTER TABLE `sw_setting_wallet_attribute`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_setting_withdraw`
--
ALTER TABLE `sw_setting_withdraw`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coin_id` (`coin_id`);

--
-- Indexes for table `sw_user_battle`
--
ALTER TABLE `sw_user_battle`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sn` (`sn`),
  ADD KEY `uid` (`uid`),
  ADD KEY `user_pet_id` (`user_pet_id`),
  ADD KEY `opponent_uid` (`opponent_uid`),
  ADD KEY `opponent_pet_id` (`opponent_pet_id`);

--
-- Indexes for table `sw_user_deposit`
--
ALTER TABLE `sw_user_deposit`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sn` (`sn`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `sw_user_gacha`
--
ALTER TABLE `sw_user_gacha`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `gacha_id` (`gacha_id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `sw_user_inventory`
--
ALTER TABLE `sw_user_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sn` (`sn`),
  ADD KEY `uid` (`uid`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `sw_user_level`
--
ALTER TABLE `sw_user_level`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `sw_user_market`
--
ALTER TABLE `sw_user_market`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sn` (`sn`),
  ADD KEY `seller_uid` (`seller_uid`),
  ADD KEY `buyer_uid` (`buyer_uid`);

--
-- Indexes for table `sw_user_mission`
--
ALTER TABLE `sw_user_mission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sn` (`sn`),
  ADD KEY `uid` (`uid`),
  ADD KEY `mission_id` (`mission_id`);

--
-- Indexes for table `sw_user_nft`
--
ALTER TABLE `sw_user_nft`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sn` (`sn`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `sw_user_pet`
--
ALTER TABLE `sw_user_pet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sn` (`sn`),
  ADD KEY `uid` (`uid`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `sw_user_point`
--
ALTER TABLE `sw_user_point`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `from_uid` (`from_uid`);

--
-- Indexes for table `sw_user_remark`
--
ALTER TABLE `sw_user_remark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sw_user_stamina`
--
ALTER TABLE `sw_user_stamina`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `sw_user_withdraw`
--
ALTER TABLE `sw_user_withdraw`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sn` (`sn`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `sw_wallet_transaction`
--
ALTER TABLE `sw_wallet_transaction`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sn` (`sn`),
  ADD KEY `uid` (`uid`),
  ADD KEY `from_uid` (`from_uid`),
  ADD KEY `to_uid` (`to_uid`);

--
-- Indexes for table `sw_wallet_transaction_detail`
--
ALTER TABLE `sw_wallet_transaction_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallet_transaction_id` (`wallet_transaction_id`),
  ADD KEY `wallet_id` (`wallet_id`);

--
-- Indexes for table `tg_callback_query`
--
ALTER TABLE `tg_callback_query`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `message_id` (`message_id`),
  ADD KEY `chat_id_2` (`chat_id`,`message_id`);

--
-- Indexes for table `tg_chat`
--
ALTER TABLE `tg_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `old_id` (`old_id`);

--
-- Indexes for table `tg_chat_join_request`
--
ALTER TABLE `tg_chat_join_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tg_chat_member_updated`
--
ALTER TABLE `tg_chat_member_updated`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tg_chosen_inline_result`
--
ALTER TABLE `tg_chosen_inline_result`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tg_conversation`
--
ALTER TABLE `tg_conversation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `tg_edited_message`
--
ALTER TABLE `tg_edited_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`),
  ADD KEY `message_id` (`message_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `chat_id_2` (`chat_id`,`message_id`);

--
-- Indexes for table `tg_inline_query`
--
ALTER TABLE `tg_inline_query`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tg_message`
--
ALTER TABLE `tg_message`
  ADD PRIMARY KEY (`chat_id`,`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `forward_from` (`forward_from`),
  ADD KEY `forward_from_chat` (`forward_from_chat`),
  ADD KEY `reply_to_chat` (`reply_to_chat`),
  ADD KEY `reply_to_message` (`reply_to_message`),
  ADD KEY `via_bot` (`via_bot`),
  ADD KEY `left_chat_member` (`left_chat_member`),
  ADD KEY `migrate_from_chat_id` (`migrate_from_chat_id`),
  ADD KEY `migrate_to_chat_id` (`migrate_to_chat_id`),
  ADD KEY `reply_to_chat_2` (`reply_to_chat`,`reply_to_message`);

--
-- Indexes for table `tg_poll`
--
ALTER TABLE `tg_poll`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tg_poll_answer`
--
ALTER TABLE `tg_poll_answer`
  ADD PRIMARY KEY (`poll_id`,`user_id`);

--
-- Indexes for table `tg_pre_checkout_query`
--
ALTER TABLE `tg_pre_checkout_query`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tg_request_limiter`
--
ALTER TABLE `tg_request_limiter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tg_shipping_query`
--
ALTER TABLE `tg_shipping_query`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tg_telegram_update`
--
ALTER TABLE `tg_telegram_update`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`),
  ADD KEY `chat_message_id` (`chat_id`,`message_id`),
  ADD KEY `edited_message_id` (`edited_message_id`),
  ADD KEY `channel_post_id` (`channel_post_id`),
  ADD KEY `edited_channel_post_id` (`edited_channel_post_id`),
  ADD KEY `inline_query_id` (`inline_query_id`),
  ADD KEY `chosen_inline_result_id` (`chosen_inline_result_id`),
  ADD KEY `callback_query_id` (`callback_query_id`),
  ADD KEY `shipping_query_id` (`shipping_query_id`),
  ADD KEY `pre_checkout_query_id` (`pre_checkout_query_id`),
  ADD KEY `poll_id` (`poll_id`),
  ADD KEY `poll_answer_poll_id` (`poll_answer_poll_id`),
  ADD KEY `my_chat_member_updated_id` (`my_chat_member_updated_id`),
  ADD KEY `chat_member_updated_id` (`chat_member_updated_id`),
  ADD KEY `chat_join_request_id` (`chat_join_request_id`),
  ADD KEY `chat_id` (`chat_id`,`channel_post_id`);

--
-- Indexes for table `tg_user`
--
ALTER TABLE `tg_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `tg_user_chat`
--
ALTER TABLE `tg_user_chat`
  ADD PRIMARY KEY (`user_id`,`chat_id`),
  ADD KEY `chat_id` (`chat_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sw_account_admin`
--
ALTER TABLE `sw_account_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sw_account_user`
--
ALTER TABLE `sw_account_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sw_admin_permission`
--
ALTER TABLE `sw_admin_permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sw_log_admin`
--
ALTER TABLE `sw_log_admin`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_log_api`
--
ALTER TABLE `sw_log_api`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_log_cronjob`
--
ALTER TABLE `sw_log_cronjob`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_log_user`
--
ALTER TABLE `sw_log_user`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_network_sponsor`
--
ALTER TABLE `sw_network_sponsor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sw_permission_template`
--
ALTER TABLE `sw_permission_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sw_permission_warehouse`
--
ALTER TABLE `sw_permission_warehouse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=290;

--
-- AUTO_INCREMENT for table `sw_reward_record`
--
ALTER TABLE `sw_reward_record`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_setting_announcement`
--
ALTER TABLE `sw_setting_announcement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_setting_attribute`
--
ALTER TABLE `sw_setting_attribute`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sw_setting_blockchain_network`
--
ALTER TABLE `sw_setting_blockchain_network`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sw_setting_coin`
--
ALTER TABLE `sw_setting_coin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sw_setting_deposit`
--
ALTER TABLE `sw_setting_deposit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sw_setting_gacha`
--
ALTER TABLE `sw_setting_gacha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sw_setting_gacha_item`
--
ALTER TABLE `sw_setting_gacha_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `sw_setting_general`
--
ALTER TABLE `sw_setting_general`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `sw_setting_item`
--
ALTER TABLE `sw_setting_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `sw_setting_item_attribute`
--
ALTER TABLE `sw_setting_item_attribute`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sw_setting_lang`
--
ALTER TABLE `sw_setting_lang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sw_setting_level`
--
ALTER TABLE `sw_setting_level`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `sw_setting_mission`
--
ALTER TABLE `sw_setting_mission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `sw_setting_nft`
--
ALTER TABLE `sw_setting_nft`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sw_setting_operator`
--
ALTER TABLE `sw_setting_operator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `sw_setting_payment`
--
ALTER TABLE `sw_setting_payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sw_setting_pet`
--
ALTER TABLE `sw_setting_pet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sw_setting_pet_attribute`
--
ALTER TABLE `sw_setting_pet_attribute`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `sw_setting_pet_rank`
--
ALTER TABLE `sw_setting_pet_rank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `sw_setting_reward`
--
ALTER TABLE `sw_setting_reward`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_setting_reward_attribute`
--
ALTER TABLE `sw_setting_reward_attribute`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_setting_wallet`
--
ALTER TABLE `sw_setting_wallet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sw_setting_wallet_attribute`
--
ALTER TABLE `sw_setting_wallet_attribute`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_setting_withdraw`
--
ALTER TABLE `sw_setting_withdraw`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sw_user_battle`
--
ALTER TABLE `sw_user_battle`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_user_deposit`
--
ALTER TABLE `sw_user_deposit`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_user_gacha`
--
ALTER TABLE `sw_user_gacha`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_user_inventory`
--
ALTER TABLE `sw_user_inventory`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_user_level`
--
ALTER TABLE `sw_user_level`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sw_user_market`
--
ALTER TABLE `sw_user_market`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_user_mission`
--
ALTER TABLE `sw_user_mission`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sw_user_nft`
--
ALTER TABLE `sw_user_nft`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_user_pet`
--
ALTER TABLE `sw_user_pet`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_user_point`
--
ALTER TABLE `sw_user_point`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_user_remark`
--
ALTER TABLE `sw_user_remark`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_user_stamina`
--
ALTER TABLE `sw_user_stamina`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sw_user_withdraw`
--
ALTER TABLE `sw_user_withdraw`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_wallet_transaction`
--
ALTER TABLE `sw_wallet_transaction`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sw_wallet_transaction_detail`
--
ALTER TABLE `sw_wallet_transaction_detail`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tg_chat_join_request`
--
ALTER TABLE `tg_chat_join_request`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for this entry';

--
-- AUTO_INCREMENT for table `tg_chat_member_updated`
--
ALTER TABLE `tg_chat_member_updated`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for this entry';

--
-- AUTO_INCREMENT for table `tg_chosen_inline_result`
--
ALTER TABLE `tg_chosen_inline_result`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for this entry';

--
-- AUTO_INCREMENT for table `tg_conversation`
--
ALTER TABLE `tg_conversation`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for this entry';

--
-- AUTO_INCREMENT for table `tg_edited_message`
--
ALTER TABLE `tg_edited_message`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for this entry';

--
-- AUTO_INCREMENT for table `tg_request_limiter`
--
ALTER TABLE `tg_request_limiter`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for this entry';

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tg_callback_query`
--
ALTER TABLE `tg_callback_query`
  ADD CONSTRAINT `tg_callback_query_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tg_user` (`id`),
  ADD CONSTRAINT `tg_callback_query_ibfk_2` FOREIGN KEY (`chat_id`,`message_id`) REFERENCES `tg_message` (`chat_id`, `id`);

--
-- Constraints for table `tg_chat_join_request`
--
ALTER TABLE `tg_chat_join_request`
  ADD CONSTRAINT `tg_chat_join_request_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `tg_chat` (`id`),
  ADD CONSTRAINT `tg_chat_join_request_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tg_user` (`id`);

--
-- Constraints for table `tg_chat_member_updated`
--
ALTER TABLE `tg_chat_member_updated`
  ADD CONSTRAINT `tg_chat_member_updated_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `tg_chat` (`id`),
  ADD CONSTRAINT `tg_chat_member_updated_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tg_user` (`id`);

--
-- Constraints for table `tg_chosen_inline_result`
--
ALTER TABLE `tg_chosen_inline_result`
  ADD CONSTRAINT `tg_chosen_inline_result_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tg_user` (`id`);

--
-- Constraints for table `tg_conversation`
--
ALTER TABLE `tg_conversation`
  ADD CONSTRAINT `tg_conversation_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tg_user` (`id`),
  ADD CONSTRAINT `tg_conversation_ibfk_2` FOREIGN KEY (`chat_id`) REFERENCES `tg_chat` (`id`);

--
-- Constraints for table `tg_edited_message`
--
ALTER TABLE `tg_edited_message`
  ADD CONSTRAINT `tg_edited_message_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `tg_chat` (`id`),
  ADD CONSTRAINT `tg_edited_message_ibfk_2` FOREIGN KEY (`chat_id`,`message_id`) REFERENCES `tg_message` (`chat_id`, `id`),
  ADD CONSTRAINT `tg_edited_message_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `tg_user` (`id`);

--
-- Constraints for table `tg_inline_query`
--
ALTER TABLE `tg_inline_query`
  ADD CONSTRAINT `tg_inline_query_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tg_user` (`id`);

--
-- Constraints for table `tg_message`
--
ALTER TABLE `tg_message`
  ADD CONSTRAINT `tg_message_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tg_user` (`id`),
  ADD CONSTRAINT `tg_message_ibfk_2` FOREIGN KEY (`chat_id`) REFERENCES `tg_chat` (`id`),
  ADD CONSTRAINT `tg_message_ibfk_3` FOREIGN KEY (`forward_from`) REFERENCES `tg_user` (`id`),
  ADD CONSTRAINT `tg_message_ibfk_4` FOREIGN KEY (`forward_from_chat`) REFERENCES `tg_chat` (`id`),
  ADD CONSTRAINT `tg_message_ibfk_5` FOREIGN KEY (`reply_to_chat`,`reply_to_message`) REFERENCES `tg_message` (`chat_id`, `id`),
  ADD CONSTRAINT `tg_message_ibfk_6` FOREIGN KEY (`via_bot`) REFERENCES `tg_user` (`id`),
  ADD CONSTRAINT `tg_message_ibfk_7` FOREIGN KEY (`left_chat_member`) REFERENCES `tg_user` (`id`);

--
-- Constraints for table `tg_poll_answer`
--
ALTER TABLE `tg_poll_answer`
  ADD CONSTRAINT `tg_poll_answer_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `tg_poll` (`id`);

--
-- Constraints for table `tg_pre_checkout_query`
--
ALTER TABLE `tg_pre_checkout_query`
  ADD CONSTRAINT `tg_pre_checkout_query_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tg_user` (`id`);

--
-- Constraints for table `tg_shipping_query`
--
ALTER TABLE `tg_shipping_query`
  ADD CONSTRAINT `tg_shipping_query_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tg_user` (`id`);

--
-- Constraints for table `tg_telegram_update`
--
ALTER TABLE `tg_telegram_update`
  ADD CONSTRAINT `tg_telegram_update_ibfk_1` FOREIGN KEY (`chat_id`,`message_id`) REFERENCES `tg_message` (`chat_id`, `id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_10` FOREIGN KEY (`poll_id`) REFERENCES `tg_poll` (`id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_11` FOREIGN KEY (`poll_answer_poll_id`) REFERENCES `tg_poll_answer` (`poll_id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_12` FOREIGN KEY (`my_chat_member_updated_id`) REFERENCES `tg_chat_member_updated` (`id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_13` FOREIGN KEY (`chat_member_updated_id`) REFERENCES `tg_chat_member_updated` (`id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_14` FOREIGN KEY (`chat_join_request_id`) REFERENCES `tg_chat_join_request` (`id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_2` FOREIGN KEY (`edited_message_id`) REFERENCES `tg_edited_message` (`id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_3` FOREIGN KEY (`chat_id`,`channel_post_id`) REFERENCES `tg_message` (`chat_id`, `id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_4` FOREIGN KEY (`edited_channel_post_id`) REFERENCES `tg_edited_message` (`id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_5` FOREIGN KEY (`inline_query_id`) REFERENCES `tg_inline_query` (`id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_6` FOREIGN KEY (`chosen_inline_result_id`) REFERENCES `tg_chosen_inline_result` (`id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_7` FOREIGN KEY (`callback_query_id`) REFERENCES `tg_callback_query` (`id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_8` FOREIGN KEY (`shipping_query_id`) REFERENCES `tg_shipping_query` (`id`),
  ADD CONSTRAINT `tg_telegram_update_ibfk_9` FOREIGN KEY (`pre_checkout_query_id`) REFERENCES `tg_pre_checkout_query` (`id`);

--
-- Constraints for table `tg_user_chat`
--
ALTER TABLE `tg_user_chat`
  ADD CONSTRAINT `tg_user_chat_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tg_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tg_user_chat_ibfk_2` FOREIGN KEY (`chat_id`) REFERENCES `tg_chat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
