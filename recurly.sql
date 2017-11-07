-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 07, 2017 at 04:50 PM
-- Server version: 5.5.44-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `iterate_poc`
--

-- --------------------------------------------------------

--
-- Table structure for table `recurly_responses`
--

CREATE TABLE IF NOT EXISTS `recurly_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_master_id` int(11) DEFAULT NULL,
  `recurly_data` text,
  `uuid` varchar(100) DEFAULT NULL COMMENT 'subscription id',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Recurly data' AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `recurly_subscriptions`
--

CREATE TABLE IF NOT EXISTS `recurly_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_plan_detail_id` int(11) DEFAULT NULL COMMENT 'entity plan detail id',
  `user_master_id` int(11) DEFAULT NULL,
  `recurly_account_code` varchar(50) DEFAULT NULL COMMENT 'account id of recurly',
  `uuid` varchar(100) DEFAULT NULL COMMENT 'subscription id',
  `state` varchar(50) DEFAULT NULL COMMENT 'state of subscription',
  `quantity` int(11) DEFAULT NULL COMMENT 'subscription quantity',
  `unit_amount_in_cents` int(11) DEFAULT NULL COMMENT 'total subscription amount in cents',
  `activated_at` datetime DEFAULT NULL COMMENT 'GMT',
  `canceled_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL COMMENT 'GMT',
  `current_period_started_at` datetime DEFAULT NULL COMMENT 'GMT',
  `current_period_ends_at` datetime DEFAULT NULL COMMENT 'GMT',
  `collection_method` varchar(50) DEFAULT NULL,
  `iterate_status` int(2) DEFAULT '1' COMMENT '1=active,2=cancelled',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='user subscription data.' AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `recurly_webhook_accounts`
--

CREATE TABLE IF NOT EXISTS `recurly_webhook_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `account_code` varchar(100) DEFAULT NULL COMMENT 'recurly account code',
  `username` varchar(50) DEFAULT NULL COMMENT 'recurly account username',
  `email` varchar(100) DEFAULT NULL COMMENT 'recurly account email',
  `first_name` varchar(100) DEFAULT NULL COMMENT 'recurly account first name',
  `last_name` varchar(100) DEFAULT NULL COMMENT 'recurly account last name',
  `company_name` varchar(100) DEFAULT NULL COMMENT 'recurly account company name',
  `whole_request` text COMMENT 'whole request that we get from recurly webhook',
  `iterate_status` tinyint(4) DEFAULT '1' COMMENT 'status maintain by us',
  `cron_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'flag set to 1 if cron has proceed it',
  `cron_group_id` int(11) DEFAULT '0' COMMENT 'group of records to process for one cron',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Notifications related to accounts that  we get from recurly' AUTO_INCREMENT=508 ;

-- --------------------------------------------------------

--
-- Table structure for table `recurly_webhook_invoices`
--

CREATE TABLE IF NOT EXISTS `recurly_webhook_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `account_code` varchar(100) DEFAULT NULL COMMENT 'recurly account code',
  `username` varchar(50) DEFAULT NULL COMMENT 'recurly account username',
  `email` varchar(100) DEFAULT NULL COMMENT 'recurly account email',
  `first_name` varchar(100) DEFAULT NULL COMMENT 'recurly account first name',
  `last_name` varchar(100) DEFAULT NULL COMMENT 'recurly account last name',
  `company_name` varchar(100) DEFAULT NULL COMMENT 'recurly account company name',
  `uuid` varchar(100) DEFAULT NULL COMMENT 'invoice id',
  `subscription_id` varchar(100) DEFAULT NULL COMMENT 'id of subscription',
  `state` varchar(50) DEFAULT NULL COMMENT 'state of subscription',
  `invoice_number_prefix` varchar(100) DEFAULT NULL,
  `invoice_number` int(11) DEFAULT NULL COMMENT 'recurly invoice number',
  `po_number` varchar(50) DEFAULT NULL,
  `vat_number` varchar(50) DEFAULT NULL,
  `total_in_cents` int(11) DEFAULT NULL COMMENT 'total amount in cents',
  `currency` varchar(20) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `net_terms` int(11) DEFAULT '0',
  `collection_method` varchar(50) DEFAULT NULL,
  `whole_request` text COMMENT 'whole request that we get from recurly webhook',
  `iterate_status` tinyint(4) DEFAULT '1' COMMENT 'status maintain by us',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Notifications related to invoice that  we get from recurly' AUTO_INCREMENT=737 ;

-- --------------------------------------------------------

--
-- Table structure for table `recurly_webhook_payments`
--

CREATE TABLE IF NOT EXISTS `recurly_webhook_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `account_code` varchar(100) DEFAULT NULL COMMENT 'recurly account code',
  `username` varchar(50) DEFAULT NULL COMMENT 'recurly account username',
  `email` varchar(100) DEFAULT NULL COMMENT 'recurly account email',
  `first_name` varchar(100) DEFAULT NULL COMMENT 'recurly account first name',
  `last_name` varchar(100) DEFAULT NULL COMMENT 'recurly account last name',
  `company_name` varchar(100) DEFAULT NULL COMMENT 'recurly account company name',
  `transaction_id` varchar(100) DEFAULT NULL COMMENT 'id of recurly transaction',
  `invoice_id` varchar(100) DEFAULT NULL COMMENT 'recurly invoice id',
  `invoice_number_prefix` varchar(100) DEFAULT NULL,
  `invoice_number` int(11) DEFAULT NULL COMMENT 'recurly invoice number',
  `subscription_id` varchar(100) DEFAULT NULL COMMENT 'id of recurly subscription',
  `action` varchar(50) DEFAULT NULL COMMENT 'recurly payment or transaction action',
  `date` datetime DEFAULT NULL,
  `amount_in_cents` int(11) DEFAULT NULL COMMENT 'transaction amount in cent',
  `status` varchar(50) DEFAULT NULL COMMENT 'recurly payment status',
  `message` varchar(255) DEFAULT NULL COMMENT 'message or reason from recurly',
  `reference` varchar(50) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL,
  `cvv_result` varchar(50) DEFAULT NULL,
  `avs_result` varchar(50) DEFAULT NULL,
  `avs_result_street` varchar(100) DEFAULT NULL,
  `avs_result_postal` varchar(100) DEFAULT NULL,
  `test` tinyint(1) DEFAULT NULL,
  `voidable` tinyint(1) DEFAULT NULL,
  `refundable` tinyint(1) DEFAULT NULL,
  `manually_entered` tinyint(1) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `gateway` varchar(50) DEFAULT NULL COMMENT 'related to fail payment. not in use for use currently',
  `gateway_error_codes` varchar(50) DEFAULT NULL COMMENT 'related to fail payment. not in use for use currently',
  `failure_type` varchar(255) DEFAULT NULL COMMENT 'related to fail payment. not in use for use currently',
  `whole_request` text COMMENT 'whole request that we get from recurly webhook',
  `iterate_status` tinyint(4) DEFAULT '1' COMMENT 'status maintain by us',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Notifications related to payment that  we get from recurly' AUTO_INCREMENT=282 ;

-- --------------------------------------------------------

--
-- Table structure for table `recurly_webhook_subscriptions`
--

CREATE TABLE IF NOT EXISTS `recurly_webhook_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `account_code` varchar(100) DEFAULT NULL COMMENT 'recurly account code',
  `username` varchar(50) DEFAULT NULL COMMENT 'recurly account username',
  `email` varchar(100) DEFAULT NULL COMMENT 'recurly account email',
  `first_name` varchar(100) DEFAULT NULL COMMENT 'recurly account first name',
  `last_name` varchar(100) DEFAULT NULL COMMENT 'recurly account last name',
  `company_name` varchar(100) DEFAULT NULL COMMENT 'recurly account company name',
  `plan_code` varchar(50) DEFAULT NULL COMMENT 'recurly plan code',
  `plan_name` varchar(50) DEFAULT NULL COMMENT 'name of plan',
  `uuid` varchar(100) DEFAULT NULL COMMENT 'subscription id',
  `state` varchar(50) DEFAULT NULL COMMENT 'state of subscription',
  `quantity` int(11) DEFAULT NULL COMMENT 'subscription quantity',
  `total_amount_in_cents` int(11) DEFAULT NULL COMMENT 'total subscription amount in cents',
  `activated_at` datetime DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `current_period_started_at` datetime DEFAULT NULL,
  `current_period_ends_at` datetime DEFAULT NULL,
  `trial_started_at` datetime DEFAULT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `collection_method` varchar(50) DEFAULT NULL,
  `whole_request` text COMMENT 'whole request that we get from recurly webhook',
  `iterate_status` tinyint(4) DEFAULT '1' COMMENT 'status maintain by us',
  `cron_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'flag set to 1 if cron has proceed it',
  `cron_group_id` int(11) DEFAULT '0' COMMENT 'group of records to process for one cron',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Notifications related to subscription that  we get from recu' AUTO_INCREMENT=369 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
