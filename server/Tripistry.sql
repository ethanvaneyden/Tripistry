-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               12.2.2-MariaDB - MariaDB Server
-- Server OS:                    Win64
-- HeidiSQL Version:             12.15.0.7171
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for tripistry
DROP DATABASE IF EXISTS `tripistry`;
CREATE DATABASE IF NOT EXISTS `tripistry` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci */;
USE `tripistry`;

-- Dumping structure for table tripistry.accommodation
DROP TABLE IF EXISTS `accommodation`;
CREATE TABLE IF NOT EXISTS `accommodation` (
  `AccommodationID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(200) NOT NULL,
  `Type` varchar(100) NOT NULL,
  `StarRating` tinyint(3) unsigned DEFAULT NULL CHECK (`StarRating` between 1 and 5),
  `AveragePricePerNight` decimal(10,2) NOT NULL CHECK (`AveragePricePerNight` >= 0),
  `Description` text DEFAULT NULL,
  `Street` varchar(200) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Country` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`AccommodationID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.accommodationimage
DROP TABLE IF EXISTS `accommodationimage`;
CREATE TABLE IF NOT EXISTS `accommodationimage` (
  `ImageID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AccommodationID` int(10) unsigned NOT NULL,
  `ImageURL` varchar(500) NOT NULL,
  PRIMARY KEY (`ImageID`),
  KEY `fk_accomimage_accom` (`AccommodationID`),
  CONSTRAINT `fk_accomimage_accom` FOREIGN KEY (`AccommodationID`) REFERENCES `accommodation` (`AccommodationID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.agency
DROP TABLE IF EXISTS `agency`;
CREATE TABLE IF NOT EXISTS `agency` (
  `AgencyID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(150) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `JoinDate` date NOT NULL DEFAULT curdate(),
  `Street` varchar(200) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Country` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`AgencyID`),
  UNIQUE KEY `Email` (`Email`),
  CONSTRAINT `chk_agency_email` CHECK (`Email` like '%@%.%')
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.agencyreview
DROP TABLE IF EXISTS `agencyreview`;
CREATE TABLE IF NOT EXISTS `agencyreview` (
  `ReviewID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TravellerID` int(10) unsigned NOT NULL,
  `AgencyID` int(10) unsigned NOT NULL,
  `Rating` tinyint(3) unsigned NOT NULL CHECK (`Rating` between 1 and 5),
  `Comment` text DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ReviewID`),
  UNIQUE KEY `uq_agency_review` (`TravellerID`,`AgencyID`),
  KEY `fk_agencyreview_agency` (`AgencyID`),
  CONSTRAINT `fk_agencyreview_agency` FOREIGN KEY (`AgencyID`) REFERENCES `agency` (`AgencyID`) ON DELETE CASCADE,
  CONSTRAINT `fk_agencyreview_traveller` FOREIGN KEY (`TravellerID`) REFERENCES `traveller` (`TravellerID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.airport
DROP TABLE IF EXISTS `airport`;
CREATE TABLE IF NOT EXISTS `airport` (
  `AirportID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Code` char(3) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `City` varchar(100) NOT NULL,
  `Country` varchar(100) NOT NULL,
  PRIMARY KEY (`AirportID`),
  UNIQUE KEY `Code` (`Code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.attraction
DROP TABLE IF EXISTS `attraction`;
CREATE TABLE IF NOT EXISTS `attraction` (
  `AttractionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(200) NOT NULL,
  `Description` text DEFAULT NULL,
  `EntranceFee` decimal(8,2) NOT NULL DEFAULT 0.00 CHECK (`EntranceFee` >= 0),
  `OpeningHours` varchar(200) DEFAULT NULL,
  `Street` varchar(200) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Country` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`AttractionID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.attractionimage
DROP TABLE IF EXISTS `attractionimage`;
CREATE TABLE IF NOT EXISTS `attractionimage` (
  `ImageID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AttractionID` int(10) unsigned NOT NULL,
  `ImageURL` varchar(500) NOT NULL,
  PRIMARY KEY (`ImageID`),
  KEY `fk_attrimage_attr` (`AttractionID`),
  CONSTRAINT `fk_attrimage_attr` FOREIGN KEY (`AttractionID`) REFERENCES `attraction` (`AttractionID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.booking
DROP TABLE IF EXISTS `booking`;
CREATE TABLE IF NOT EXISTS `booking` (
  `BookingID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TravellerID` int(10) unsigned NOT NULL,
  `PackageID` int(10) unsigned NOT NULL,
  `Date` datetime NOT NULL DEFAULT current_timestamp(),
  `NumberOfPeople` tinyint(3) unsigned NOT NULL DEFAULT 1 CHECK (`NumberOfPeople` >= 1),
  `Status` enum('Pending','Confirmed','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
  `TotalPrice` decimal(12,2) NOT NULL CHECK (`TotalPrice` >= 0),
  PRIMARY KEY (`BookingID`),
  KEY `fk_booking_traveller` (`TravellerID`),
  KEY `fk_booking_package` (`PackageID`),
  CONSTRAINT `fk_booking_package` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`),
  CONSTRAINT `fk_booking_traveller` FOREIGN KEY (`TravellerID`) REFERENCES `traveller` (`TravellerID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.destination
DROP TABLE IF EXISTS `destination`;
CREATE TABLE IF NOT EXISTS `destination` (
  `DestinationID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(200) NOT NULL,
  `City` varchar(100) NOT NULL,
  `Region` varchar(100) DEFAULT NULL,
  `Country` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  PRIMARY KEY (`DestinationID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.flight
DROP TABLE IF EXISTS `flight`;
CREATE TABLE IF NOT EXISTS `flight` (
  `FlightID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FlightNumber` varchar(20) NOT NULL,
  `Airline` varchar(150) NOT NULL,
  `DepartureDateTime` datetime NOT NULL,
  `ArrivalDateTime` datetime NOT NULL,
  `BaseCost` decimal(10,2) NOT NULL CHECK (`BaseCost` >= 0),
  `OriginAirportID` int(10) unsigned NOT NULL,
  `DestinationAirportID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`FlightID`),
  KEY `fk_flight_origin` (`OriginAirportID`),
  KEY `fk_flight_destination` (`DestinationAirportID`),
  CONSTRAINT `fk_flight_destination` FOREIGN KEY (`DestinationAirportID`) REFERENCES `airport` (`AirportID`),
  CONSTRAINT `fk_flight_origin` FOREIGN KEY (`OriginAirportID`) REFERENCES `airport` (`AirportID`),
  CONSTRAINT `chk_flight_dates` CHECK (`ArrivalDateTime` > `DepartureDateTime`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.flightclass
DROP TABLE IF EXISTS `flightclass`;
CREATE TABLE IF NOT EXISTS `flightclass` (
  `FlightID` int(10) unsigned NOT NULL,
  `ClassName` enum('Economy','Business','First Class') NOT NULL,
  PRIMARY KEY (`FlightID`,`ClassName`),
  CONSTRAINT `fk_flightclass_flight` FOREIGN KEY (`FlightID`) REFERENCES `flight` (`FlightID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.grouptrip
DROP TABLE IF EXISTS `grouptrip`;
CREATE TABLE IF NOT EXISTS `grouptrip` (
  `GroupTripID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AgencyID` int(10) unsigned NOT NULL,
  `MaxCapacity` int(10) unsigned NOT NULL,
  `Status` enum('Open','Full','Completed','Cancelled') NOT NULL DEFAULT 'Open',
  `StartDate` date NOT NULL,
  `EndDate` date NOT NULL,
  PRIMARY KEY (`GroupTripID`),
  KEY `fk_grouptrip_agency` (`AgencyID`),
  CONSTRAINT `fk_grouptrip_agency` FOREIGN KEY (`AgencyID`) REFERENCES `agency` (`AgencyID`),
  CONSTRAINT `chk_grouptrip_dates` CHECK (`EndDate` > `StartDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.grouptripmember
DROP TABLE IF EXISTS `grouptripmember`;
CREATE TABLE IF NOT EXISTS `grouptripmember` (
  `GroupTripID` int(10) unsigned NOT NULL,
  `TravellerID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`GroupTripID`,`TravellerID`),
  KEY `fk_gtmember_traveller` (`TravellerID`),
  CONSTRAINT `fk_gtmember_traveller` FOREIGN KEY (`TravellerID`) REFERENCES `traveller` (`TravellerID`),
  CONSTRAINT `fk_gtmember_trip` FOREIGN KEY (`GroupTripID`) REFERENCES `grouptrip` (`GroupTripID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.package
DROP TABLE IF EXISTS `package`;
CREATE TABLE IF NOT EXISTS `package` (
  `PackageID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AgencyID` int(10) unsigned NOT NULL,
  `Title` varchar(300) NOT NULL,
  `Description` text DEFAULT NULL,
  `StartDate` date NOT NULL,
  `EndDate` date NOT NULL,
  `MaxParticipants` int(10) unsigned NOT NULL DEFAULT 1,
  `TotalPrice` decimal(10,2) NOT NULL CHECK (`TotalPrice` >= 0),
  PRIMARY KEY (`PackageID`),
  KEY `fk_package_agency` (`AgencyID`),
  CONSTRAINT `fk_package_agency` FOREIGN KEY (`AgencyID`) REFERENCES `agency` (`AgencyID`) ON DELETE CASCADE,
  CONSTRAINT `chk_package_dates` CHECK (`EndDate` > `StartDate`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.packageaccommodation
DROP TABLE IF EXISTS `packageaccommodation`;
CREATE TABLE IF NOT EXISTS `packageaccommodation` (
  `PackageID` int(10) unsigned NOT NULL,
  `AccommodationID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`PackageID`,`AccommodationID`),
  KEY `fk_pkgaccom_accom` (`AccommodationID`),
  CONSTRAINT `fk_pkgaccom_accom` FOREIGN KEY (`AccommodationID`) REFERENCES `accommodation` (`AccommodationID`),
  CONSTRAINT `fk_pkgaccom_pkg` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.packageattraction
DROP TABLE IF EXISTS `packageattraction`;
CREATE TABLE IF NOT EXISTS `packageattraction` (
  `PackageID` int(10) unsigned NOT NULL,
  `AttractionID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`PackageID`,`AttractionID`),
  KEY `fk_pkgattr_attr` (`AttractionID`),
  CONSTRAINT `fk_pkgattr_attr` FOREIGN KEY (`AttractionID`) REFERENCES `attraction` (`AttractionID`),
  CONSTRAINT `fk_pkgattr_pkg` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.packagedestination
DROP TABLE IF EXISTS `packagedestination`;
CREATE TABLE IF NOT EXISTS `packagedestination` (
  `PackageID` int(10) unsigned NOT NULL,
  `DestinationID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`PackageID`,`DestinationID`),
  KEY `fk_pkgdest_dest` (`DestinationID`),
  CONSTRAINT `fk_pkgdest_dest` FOREIGN KEY (`DestinationID`) REFERENCES `destination` (`DestinationID`),
  CONSTRAINT `fk_pkgdest_pkg` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.packageflight
DROP TABLE IF EXISTS `packageflight`;
CREATE TABLE IF NOT EXISTS `packageflight` (
  `PackageID` int(10) unsigned NOT NULL,
  `FlightID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`PackageID`,`FlightID`),
  KEY `fk_pkgflight_flight` (`FlightID`),
  CONSTRAINT `fk_pkgflight_flight` FOREIGN KEY (`FlightID`) REFERENCES `flight` (`FlightID`),
  CONSTRAINT `fk_pkgflight_pkg` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.packageimage
DROP TABLE IF EXISTS `packageimage`;
CREATE TABLE IF NOT EXISTS `packageimage` (
  `ImageID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PackageID` int(10) unsigned NOT NULL,
  `ImageURL` varchar(500) NOT NULL,
  PRIMARY KEY (`ImageID`),
  KEY `fk_packageimage_pkg` (`PackageID`),
  CONSTRAINT `fk_packageimage_pkg` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.packagerestaurant
DROP TABLE IF EXISTS `packagerestaurant`;
CREATE TABLE IF NOT EXISTS `packagerestaurant` (
  `PackageID` int(10) unsigned NOT NULL,
  `RestaurantID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`PackageID`,`RestaurantID`),
  KEY `fk_pkgrest_rest` (`RestaurantID`),
  CONSTRAINT `fk_pkgrest_pkg` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE,
  CONSTRAINT `fk_pkgrest_rest` FOREIGN KEY (`RestaurantID`) REFERENCES `restaurant` (`RestaurantID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.packagereview
DROP TABLE IF EXISTS `packagereview`;
CREATE TABLE IF NOT EXISTS `packagereview` (
  `ReviewID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TravellerID` int(10) unsigned NOT NULL,
  `PackageID` int(10) unsigned NOT NULL,
  `Rating` tinyint(3) unsigned NOT NULL CHECK (`Rating` between 1 and 5),
  `Comment` text DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ReviewID`),
  UNIQUE KEY `uq_pkg_review` (`TravellerID`,`PackageID`),
  KEY `fk_pkgreview_package` (`PackageID`),
  CONSTRAINT `fk_pkgreview_package` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE,
  CONSTRAINT `fk_pkgreview_traveller` FOREIGN KEY (`TravellerID`) REFERENCES `traveller` (`TravellerID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.restaurant
DROP TABLE IF EXISTS `restaurant`;
CREATE TABLE IF NOT EXISTS `restaurant` (
  `RestaurantID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(200) NOT NULL,
  `Cuisine` varchar(100) DEFAULT NULL,
  `PriceRange` varchar(10) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `Street` varchar(200) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Country` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`RestaurantID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.restaurantimage
DROP TABLE IF EXISTS `restaurantimage`;
CREATE TABLE IF NOT EXISTS `restaurantimage` (
  `ImageID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RestaurantID` int(10) unsigned NOT NULL,
  `ImageURL` varchar(500) NOT NULL,
  PRIMARY KEY (`ImageID`),
  KEY `fk_restimage_rest` (`RestaurantID`),
  CONSTRAINT `fk_restimage_rest` FOREIGN KEY (`RestaurantID`) REFERENCES `restaurant` (`RestaurantID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table tripistry.traveller
DROP TABLE IF EXISTS `traveller`;
CREATE TABLE IF NOT EXISTS `traveller` (
  `TravellerID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(100) NOT NULL,
  `Surname` varchar(100) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Nationality` varchar(100) DEFAULT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `JoinDate` date NOT NULL DEFAULT curdate(),
  PRIMARY KEY (`TravellerID`),
  UNIQUE KEY `Email` (`Email`),
  CONSTRAINT `chk_traveller_email` CHECK (`Email` like '%@%.%')
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
