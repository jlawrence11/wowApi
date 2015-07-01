-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2015 at 06:03 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `wowapi`
--

-- --------------------------------------------------------

--
-- Table structure for table `lw_itemcache`
--

CREATE TABLE IF NOT EXISTS `lw_itemcache` (
  `id` int(10) unsigned NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `stackable` int(11) DEFAULT NULL,
  `itemBind` int(11) DEFAULT NULL,
  `buyPrice` int(11) DEFAULT NULL,
  `itemClass` int(11) DEFAULT NULL,
  `itemSubClass` int(11) DEFAULT NULL,
  `containerSlots` int(11) DEFAULT NULL,
  `inventoryType` int(11) DEFAULT NULL,
  `equippable` tinyint(1) DEFAULT NULL,
  `itemLevel` int(11) DEFAULT NULL,
  `maxCount` int(11) DEFAULT NULL,
  `maxDurability` int(11) DEFAULT NULL,
  `minFactionId` int(11) DEFAULT NULL,
  `minReputation` int(11) DEFAULT NULL,
  `quality` int(11) DEFAULT NULL,
  `sellPrice` int(11) DEFAULT NULL,
  `requiredSkill` int(11) DEFAULT NULL,
  `requiredLevel` int(11) DEFAULT NULL,
  `requiredSkillRank` int(11) DEFAULT NULL,
  `baseArmor` int(11) DEFAULT NULL,
  `hasSockets` tinyint(1) DEFAULT NULL,
  `isAuctionable` tinyint(1) DEFAULT NULL,
  `armor` int(11) DEFAULT NULL,
  `displayInfoId` int(11) DEFAULT NULL,
  `nameDescription` varchar(50) DEFAULT NULL,
  `nameDescriptionColor` varchar(10) DEFAULT NULL,
  `upgradable` tinyint(1) DEFAULT NULL,
  `lastUpdate` int(11) DEFAULT NULL,
  `fullJSON` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lw_itemclass`
--

CREATE TABLE IF NOT EXISTS `lw_itemclass` (
  `itemClass` int(10) unsigned NOT NULL,
  `className` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`itemClass`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lw_itemquality`
--

CREATE TABLE IF NOT EXISTS `lw_itemquality` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(20) NOT NULL,
  `color` varchar(9) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lw_itemsubclass`
--

CREATE TABLE IF NOT EXISTS `lw_itemsubclass` (
  `itemClass` int(10) unsigned NOT NULL,
  `itemSubClass` int(10) unsigned NOT NULL,
  `subClassName` varchar(50) NOT NULL,
  `subClassFullName` varchar(50) DEFAULT NULL,
  KEY `itemClass` (`itemClass`,`itemSubClass`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lw_wowauctions`
--

CREATE TABLE IF NOT EXISTS `lw_wowauctions` (
  `auc` bigint(20) NOT NULL,
  `item` int(11) NOT NULL,
  `owner` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ownerRealm` varchar(50) CHARACTER SET latin1 NOT NULL,
  `bid` int(11) NOT NULL,
  `buyout` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `timeLeft` varchar(20) CHARACTER SET latin1 NOT NULL,
  `rand` int(11) NOT NULL,
  `seed` bigint(20) NOT NULL,
  `petSpeciesId` int(11) NOT NULL,
  `petBreedId` int(11) NOT NULL,
  `petLevel` int(11) NOT NULL,
  `petQualityId` int(11) NOT NULL,
  `player` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`auc`),
  KEY `item` (`item`,`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `lw_wowcache`
--

CREATE TABLE IF NOT EXISTS `lw_wowcache` (
  `slug` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lastUpdate` int(11) NOT NULL,
  `json` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lw_wowcharacters`
--

CREATE TABLE IF NOT EXISTS `lw_wowcharacters` (
  `id` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `realmId` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `realm` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `battlegroup` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `withTitle` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `titleId` int(11) NOT NULL,
  `titleFormat` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `class` int(11) NOT NULL,
  `race` int(11) NOT NULL,
  `gender` tinyint(1) NOT NULL,
  `level` int(11) NOT NULL,
  `achievementPoints` int(11) NOT NULL,
  `thumbnail` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `specName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `specRole` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `specIcon` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `guildId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `guildName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `guildRank` int(11) NOT NULL,
  `averageItemLevel` int(11) NOT NULL,
  `averageItemLevelEquipped` int(11) NOT NULL,
  `totalHonorableKills` int(11) NOT NULL,
  `lastUpdate` int(11) NOT NULL,
  `fullJSON` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lw_wowguilds`
--

CREATE TABLE IF NOT EXISTS `lw_wowguilds` (
  `guildId` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `realmId` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `realm` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `battlegroup` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `level` int(11) NOT NULL,
  `side` tinyint(4) NOT NULL,
  `achievementPoints` int(11) NOT NULL,
  `lastUpdate` int(11) NOT NULL,
  `fullJSON` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`guildId`),
  KEY `realmId` (`realmId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
