-- phpMyAdmin SQL Dump
-- version 2.9.1.1-Debian-3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: May 13, 2007 at 08:04 PM
-- Server version: 5.0.32
-- PHP Version: 4.4.4-8+etch2
-- 
-- Database: `domain_hunter`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `monitors`
-- 

CREATE TABLE `monitors` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `register` varchar(255) NOT NULL default '',
  `domain` varchar(64) NOT NULL default '',
  `whois_serv` varchar(100) NOT NULL default '',
  `ref_url` varchar(100) NOT NULL default '',
  `nameserv1` varchar(100) NOT NULL default '',
  `nameserv2` varchar(100) NOT NULL default '',
  `nameserv3` varchar(100) NOT NULL default '',
  `nameserv4` varchar(100) NOT NULL default '',
  `nameserv5` varchar(100) NOT NULL default '',
  `status1` varchar(100) NOT NULL,
  `status2` varchar(100) NOT NULL,
  `status3` varchar(100) NOT NULL,
  `create_date` date NOT NULL default '0000-00-00',
  `update_date` date NOT NULL default '0000-00-00',
  `expirate_date` date NOT NULL default '0000-00-00',
  `hunter_update` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

