-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Lun 16 Janvier 2017 à 10:12
-- Version du serveur :  5.6.17
-- Version de PHP :  5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `sgp_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `error`
--

CREATE TABLE IF NOT EXISTS `error` (
  `errorReportId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `cadbNumber` varchar(13) COLLATE utf32_unicode_ci NOT NULL,
  `problem` varchar(255) COLLATE utf32_unicode_ci NOT NULL,
  `reporterNetworkId` varchar(2) COLLATE utf32_unicode_ci NOT NULL,
  `submissionDateTime` timestamp NOT NULL,
  `nrnNetworkId` varchar(2) COLLATE utf32_unicode_ci DEFAULT NULL,
  `nrnRoutingNumber` varchar(8) COLLATE utf32_unicode_ci DEFAULT NULL,
  `routingChangeDateTime` timestamp NULL DEFAULT NULL,
  `processType` varchar(8) COLLATE utf32_unicode_ci DEFAULT NULL,
  `notificationMailSendStatus` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `notificationMailSendDateTime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`errorReportId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `logId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(20) COLLATE utf32_unicode_ci NOT NULL,
  `actionPerformed` varchar(255) COLLATE utf32_unicode_ci NOT NULL,
  `actionDateTime` timestamp NOT NULL,
  PRIMARY KEY (`logId`),
  KEY `FKLog960861` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `numberreturn`
--

CREATE TABLE IF NOT EXISTS `numberreturn` (
  `returnId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `openDateTime` timestamp NOT NULL,
  `ownerNetworkId` varchar(2) COLLATE utf32_unicode_ci NOT NULL,
  `ownerRoutingNumber` varchar(8) COLLATE utf32_unicode_ci NOT NULL,
  `primaryOwnerNetworkId` varchar(2) COLLATE utf32_unicode_ci NOT NULL,
  `primaryOwnerRoutingNumber` varchar(8) COLLATE utf32_unicode_ci NOT NULL,
  `returnMSISDN` varchar(13) COLLATE utf32_unicode_ci NOT NULL,
  `returnNumberState` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `notificationMailSendStatus` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `notificationMailSendDateTime` timestamp NULL DEFAULT NULL,
  `numberReturnSubmissionId` int(10) DEFAULT NULL,
  PRIMARY KEY (`returnId`),
  KEY `FKNumberRetu598213` (`numberReturnSubmissionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `numberreturnstateevolution`
--

CREATE TABLE IF NOT EXISTS `numberreturnstateevolution` (
  `numberReturnStateEvolutionId` int(10) NOT NULL AUTO_INCREMENT,
  `returnNumberState` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `lastChangeDateTime` timestamp NOT NULL,
  `returnId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  PRIMARY KEY (`numberReturnStateEvolutionId`),
  KEY `FKNumberRetu871647` (`returnId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `numberreturnsubmission`
--

CREATE TABLE IF NOT EXISTS `numberreturnsubmission` (
  `numberReturnSubmissionId` int(10) NOT NULL AUTO_INCREMENT,
  `primaryOwnerNetworkId` varchar(2) COLLATE utf32_unicode_ci NOT NULL,
  `primaryOwnerNetworkNumber` varchar(8) COLLATE utf32_unicode_ci NOT NULL,
  `returnMSISDN` varchar(13) COLLATE utf32_unicode_ci NOT NULL,
  `submissionState` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `submissionDateTime` timestamp NOT NULL,
  `userId` varchar(20) COLLATE utf32_unicode_ci NOT NULL,
  PRIMARY KEY (`numberReturnSubmissionId`),
  KEY `FKNumberRetu899864` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `porting`
--

CREATE TABLE IF NOT EXISTS `porting` (
  `portingId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `recipientNetworkId` varchar(2) COLLATE utf32_unicode_ci NOT NULL,
  `recipientRoutingNumber` varchar(8) COLLATE utf32_unicode_ci NOT NULL,
  `donorNetworkId` varchar(2) COLLATE utf32_unicode_ci NOT NULL,
  `donorRoutingNumber` varchar(8) COLLATE utf32_unicode_ci NOT NULL,
  `recipientSubmissionDateTime` timestamp NOT NULL,
  `portingDateTime` timestamp NULL DEFAULT NULL,
  `rio` varchar(12) COLLATE utf32_unicode_ci NOT NULL,
  `startMSISDN` varchar(13) COLLATE utf32_unicode_ci NOT NULL,
  `endMSISDN` varchar(13) COLLATE utf32_unicode_ci NOT NULL,
  `physicalPersonFirstName` varchar(30) COLLATE utf32_unicode_ci DEFAULT NULL,
  `physicalPersonLastName` varchar(30) COLLATE utf32_unicode_ci DEFAULT NULL,
  `physicalPersonIdNumber` int(11) DEFAULT NULL,
  `legalPersonName` varchar(30) COLLATE utf32_unicode_ci DEFAULT NULL,
  `legalPersonTin` varchar(10) COLLATE utf32_unicode_ci DEFAULT NULL,
  `contactNumber` varchar(13) COLLATE utf32_unicode_ci DEFAULT NULL,
  `cadbOrderDateTime` timestamp NOT NULL,
  `lastChangeDateTime` timestamp NOT NULL,
  `portingState` varchar(50) COLLATE utf32_unicode_ci NOT NULL,
  `contractId` varchar(25) COLLATE utf32_unicode_ci NOT NULL,
  `language` varchar(2) COLLATE utf32_unicode_ci NOT NULL,
  `notificationMailSendStatus` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `notificationMailSendDateTime` timestamp NULL DEFAULT NULL,
  `portingSubmissionId` int(10) DEFAULT NULL,
  PRIMARY KEY (`portingId`),
  KEY `FKPorting459809` (`portingSubmissionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `portingdenyrejectionabandon`
--

CREATE TABLE IF NOT EXISTS `portingdenyrejectionabandon` (
  `portingDenyRejectionAbandonedId` int(10) NOT NULL AUTO_INCREMENT,
  `denyRejectionReason` varchar(255) COLLATE utf32_unicode_ci DEFAULT NULL,
  `cause` varchar(255) COLLATE utf32_unicode_ci NOT NULL,
  `portingId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  PRIMARY KEY (`portingDenyRejectionAbandonedId`),
  KEY `FKPortingDen964000` (`portingId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci COMMENT='Contains reasons for porting denial / rejection / abandoned which occurs either during DENY, REJECT or ABANDONED states. If process abandoned, cause will represent the abandoned cause. In this case, rejectionReason might be NULL. Otherwise, it can''t be NULL' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `portingsmsnotification`
--

CREATE TABLE IF NOT EXISTS `portingsmsnotification` (
  `portingSmsNotificationId` int(10) NOT NULL AUTO_INCREMENT,
  `portingId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `smsType` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `creationDateTime` timestamp NOT NULL,
  `sendDateTime` timestamp NULL DEFAULT NULL,
  `status` varchar(25) COLLATE utf32_unicode_ci NOT NULL,
  `attemptCount` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`portingSmsNotificationId`),
  KEY `FKPortingSms948116` (`portingId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `portingstateevolution`
--

CREATE TABLE IF NOT EXISTS `portingstateevolution` (
  `portingStateEvolutionId` int(10) NOT NULL AUTO_INCREMENT,
  `lastChangeDateTime` timestamp NOT NULL,
  `portingState` varchar(50) COLLATE utf32_unicode_ci NOT NULL,
  `isAutoReached` tinyint(1) NOT NULL,
  `portingId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  PRIMARY KEY (`portingStateEvolutionId`),
  KEY `FKPortingSta815839` (`portingId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `portingsubmission`
--

CREATE TABLE IF NOT EXISTS `portingsubmission` (
  `portingSubmissionId` int(10) NOT NULL AUTO_INCREMENT,
  `donorNetworkId` varchar(2) COLLATE utf32_unicode_ci NOT NULL,
  `donorRoutingNumber` varchar(8) COLLATE utf32_unicode_ci NOT NULL,
  `subscriberSubmissionDateTime` timestamp NOT NULL,
  `portingDateTime` timestamp NOT NULL,
  `rio` varchar(12) COLLATE utf32_unicode_ci NOT NULL,
  `documentType` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `portingMSISDN` varchar(13) COLLATE utf32_unicode_ci NOT NULL,
  `physicalPersonIdNumber` int(11) DEFAULT NULL,
  `physicalPersonFirstName` varchar(30) COLLATE utf32_unicode_ci DEFAULT NULL,
  `physicalPersonLastName` varchar(30) COLLATE utf32_unicode_ci DEFAULT NULL,
  `legalPersonName` varchar(60) COLLATE utf32_unicode_ci DEFAULT NULL,
  `legalPersonTin` varchar(10) COLLATE utf32_unicode_ci DEFAULT NULL,
  `contactNumber` varchar(10) COLLATE utf32_unicode_ci DEFAULT NULL,
  `contractId` varchar(25) COLLATE utf32_unicode_ci NOT NULL,
  `language` varchar(2) COLLATE utf32_unicode_ci NOT NULL,
  `temporalMSISDN` varchar(13) COLLATE utf32_unicode_ci NOT NULL,
  `submissionState` varchar(255) COLLATE utf32_unicode_ci NOT NULL DEFAULT 'STARTED' COMMENT 'Holds the current (at the time of consultation) state of the submission. Its values can be STARTED, ORDERED or ABORTED',
  `orderedDateTime` timestamp NULL DEFAULT NULL,
  `userId` varchar(20) COLLATE utf32_unicode_ci NOT NULL,
  PRIMARY KEY (`portingSubmissionId`),
  KEY `FKPortingSub695331` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci COMMENT='Table filled when a submission is made by OPR agent. It holds the submission date and time, the last ordered date time, the subscriber''s MSISDN, the Id number if physical person or Tin if legal person.\r\n\r\nThe Physical person Id or Tin is used to link with the Porting table row involved (this is within 2 months).' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `provisioning`
--

CREATE TABLE IF NOT EXISTS `provisioning` (
  `processId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `endNetworkId` varchar(2) COLLATE utf32_unicode_ci NOT NULL,
  `endRoutingNumber` varchar(8) COLLATE utf32_unicode_ci NOT NULL,
  `subscriberMSISDN` varchar(13) COLLATE utf32_unicode_ci NOT NULL,
  `routingChangeDateTime` timestamp NOT NULL,
  `processType` varchar(8) COLLATE utf32_unicode_ci NOT NULL,
  `provisionState` varchar(8) COLLATE utf32_unicode_ci NOT NULL,
  PRIMARY KEY (`processId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci COMMENT='end% info will correspond to OPR in porting scenario or primary owner in return scenario or OPD in rollback scenario.\r\nIn these scenarios, all other actors take proper actions only at the reception of notifyRoutingData message.';

-- --------------------------------------------------------

--
-- Structure de la table `returnrejection`
--

CREATE TABLE IF NOT EXISTS `returnrejection` (
  `returnRejectionId` int(10) NOT NULL AUTO_INCREMENT,
  `cause` varchar(255) COLLATE utf32_unicode_ci NOT NULL,
  `returnId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  PRIMARY KEY (`returnRejectionId`),
  KEY `FKReturnReje600376` (`returnId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `rollback`
--

CREATE TABLE IF NOT EXISTS `rollback` (
  `rollbackId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `originalPortingId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `donorSubmissionDateTime` timestamp NOT NULL,
  `preferredRollbackDateTime` timestamp NOT NULL,
  `rollbackDateTime` timestamp NULL DEFAULT NULL,
  `cadbOpenDateTime` timestamp NOT NULL,
  `lastChangeDateTime` timestamp NOT NULL,
  `rollbackState` varchar(50) COLLATE utf32_unicode_ci NOT NULL,
  `notificationMailSendStatus` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `notificationMailSendDateTime` timestamp NULL DEFAULT NULL,
  `rollbackSubmissionId` int(10) DEFAULT NULL,
  PRIMARY KEY (`rollbackId`),
  KEY `FKRollback555103` (`rollbackSubmissionId`),
  KEY `FKRollback311841` (`originalPortingId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rollbackrejectionabandon`
--

CREATE TABLE IF NOT EXISTS `rollbackrejectionabandon` (
  `rollbackRejectionAbandonedId` int(10) NOT NULL AUTO_INCREMENT,
  `rejectionReason` varchar(255) COLLATE utf32_unicode_ci DEFAULT NULL,
  `cause` varchar(255) COLLATE utf32_unicode_ci NOT NULL,
  `rollbackId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  PRIMARY KEY (`rollbackRejectionAbandonedId`),
  KEY `FKRollbackRe746895` (`rollbackId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `rollbacksmsnotification`
--

CREATE TABLE IF NOT EXISTS `rollbacksmsnotification` (
  `rollbackSmsNotificationId` int(10) NOT NULL AUTO_INCREMENT,
  `rollbackId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `smsType` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `creationDateTime` timestamp NOT NULL,
  `sendDateTime` timestamp NULL DEFAULT NULL,
  `status` varchar(25) COLLATE utf32_unicode_ci NOT NULL,
  `attemptCount` int(3) NOT NULL,
  PRIMARY KEY (`rollbackSmsNotificationId`),
  KEY `FKRollbackSm427866` (`rollbackId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `rollbackstateevolution`
--

CREATE TABLE IF NOT EXISTS `rollbackstateevolution` (
  `rollbackStatevolutionId` int(10) NOT NULL AUTO_INCREMENT,
  `rollbackState` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `lastChangeDateTime` timestamp NOT NULL,
  `isAutoReached` tinyint(1) NOT NULL,
  `rollbackId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  PRIMARY KEY (`rollbackStatevolutionId`),
  KEY `FKRollbackSt629899` (`rollbackId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `rollbacksubmission`
--

CREATE TABLE IF NOT EXISTS `rollbacksubmission` (
  `rollbackSubmissionId` int(10) NOT NULL AUTO_INCREMENT,
  `originalPortingId` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `preferredRollbackDateTime` timestamp NOT NULL,
  `submissionState` varchar(8) COLLATE utf32_unicode_ci NOT NULL,
  `openedDateTime` timestamp NULL DEFAULT NULL,
  `contractId` varchar(25) COLLATE utf32_unicode_ci NOT NULL,
  `language` varchar(2) COLLATE utf32_unicode_ci NOT NULL,
  `temporalMSISDN` varchar(13) COLLATE utf32_unicode_ci NOT NULL,
  `userId` varchar(20) COLLATE utf32_unicode_ci NOT NULL,
  PRIMARY KEY (`rollbackSubmissionId`),
  KEY `FKRollbackSu314861` (`originalPortingId`),
  KEY `FKRollbackSu109941` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userId` varchar(20) COLLATE utf32_unicode_ci NOT NULL,
  `firstName` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `lastName` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `email` varchar(30) COLLATE utf32_unicode_ci NOT NULL,
  `role` int(2) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `creationDateTime` timestamp NOT NULL,
  `lastModifiedDateTime` timestamp NOT NULL,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ussdsmsnotification`
--

CREATE TABLE IF NOT EXISTS `ussdsmsnotification` (
  `ussdSmsNotificationId` int(11) NOT NULL AUTO_INCREMENT,
  `message` varchar(255) COLLATE utf32_unicode_ci NOT NULL,
  `msisdn` varchar(13) COLLATE utf32_unicode_ci NOT NULL,
  `creationDateTime` timestamp NOT NULL,
  `sendDateTime` timestamp NULL DEFAULT NULL,
  `status` varchar(25) COLLATE utf32_unicode_ci NOT NULL,
  `attemptCount` int(3) NOT NULL,
  PRIMARY KEY (`ussdSmsNotificationId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_unicode_ci AUTO_INCREMENT=1 ;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `FKLog960861` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`);

--
-- Contraintes pour la table `numberreturn`
--
ALTER TABLE `numberreturn`
  ADD CONSTRAINT `FKNumberRetu598213` FOREIGN KEY (`numberReturnSubmissionId`) REFERENCES `numberreturnsubmission` (`numberReturnSubmissionId`);

--
-- Contraintes pour la table `numberreturnstateevolution`
--
ALTER TABLE `numberreturnstateevolution`
  ADD CONSTRAINT `FKNumberRetu871647` FOREIGN KEY (`returnId`) REFERENCES `numberreturn` (`returnId`);

--
-- Contraintes pour la table `numberreturnsubmission`
--
ALTER TABLE `numberreturnsubmission`
  ADD CONSTRAINT `FKNumberRetu899864` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`);

--
-- Contraintes pour la table `porting`
--
ALTER TABLE `porting`
  ADD CONSTRAINT `FKPorting459809` FOREIGN KEY (`portingSubmissionId`) REFERENCES `portingsubmission` (`portingSubmissionId`);

--
-- Contraintes pour la table `portingdenyrejectionabandon`
--
ALTER TABLE `portingdenyrejectionabandon`
  ADD CONSTRAINT `FKPortingDen964000` FOREIGN KEY (`portingId`) REFERENCES `porting` (`portingId`);

--
-- Contraintes pour la table `portingsmsnotification`
--
ALTER TABLE `portingsmsnotification`
  ADD CONSTRAINT `FKPortingSms948116` FOREIGN KEY (`portingId`) REFERENCES `porting` (`portingId`);

--
-- Contraintes pour la table `portingstateevolution`
--
ALTER TABLE `portingstateevolution`
  ADD CONSTRAINT `FKPortingSta815839` FOREIGN KEY (`portingId`) REFERENCES `porting` (`portingId`);

--
-- Contraintes pour la table `portingsubmission`
--
ALTER TABLE `portingsubmission`
  ADD CONSTRAINT `FKPortingSub695331` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`);

--
-- Contraintes pour la table `returnrejection`
--
ALTER TABLE `returnrejection`
  ADD CONSTRAINT `FKReturnReje600376` FOREIGN KEY (`returnId`) REFERENCES `numberreturn` (`returnId`);

--
-- Contraintes pour la table `rollback`
--
ALTER TABLE `rollback`
  ADD CONSTRAINT `FKRollback311841` FOREIGN KEY (`originalPortingId`) REFERENCES `porting` (`portingId`),
  ADD CONSTRAINT `FKRollback555103` FOREIGN KEY (`rollbackSubmissionId`) REFERENCES `rollbacksubmission` (`rollbackSubmissionId`);

--
-- Contraintes pour la table `rollbackrejectionabandon`
--
ALTER TABLE `rollbackrejectionabandon`
  ADD CONSTRAINT `FKRollbackRe746895` FOREIGN KEY (`rollbackId`) REFERENCES `rollback` (`rollbackId`);

--
-- Contraintes pour la table `rollbacksmsnotification`
--
ALTER TABLE `rollbacksmsnotification`
  ADD CONSTRAINT `FKRollbackSm427866` FOREIGN KEY (`rollbackId`) REFERENCES `rollback` (`rollbackId`);

--
-- Contraintes pour la table `rollbackstateevolution`
--
ALTER TABLE `rollbackstateevolution`
  ADD CONSTRAINT `FKRollbackSt629899` FOREIGN KEY (`rollbackId`) REFERENCES `rollback` (`rollbackId`);

--
-- Contraintes pour la table `rollbacksubmission`
--
ALTER TABLE `rollbacksubmission`
  ADD CONSTRAINT `FKRollbackSu109941` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`),
  ADD CONSTRAINT `FKRollbackSu314861` FOREIGN KEY (`originalPortingId`) REFERENCES `porting` (`portingId`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
