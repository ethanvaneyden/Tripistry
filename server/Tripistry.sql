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


-- Dumping database structure for triptest
DROP DATABASE IF EXISTS `tripistry`;
CREATE DATABASE IF NOT EXISTS `tripistry` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci */;
USE `tripistry`;
e
-- Dumping structure for table triptest.accommodation
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.accommodation: ~21 rows (approximately)
INSERT INTO `accommodation` (`AccommodationID`, `Name`, `Type`, `StarRating`, `AveragePricePerNight`, `Description`, `Street`, `City`, `Country`) VALUES
	(2, 'Hout Bay Beachfront Hotel', 'Hotel', 4, 1850.00, 'Luxury beachfront hotel with panoramic ocean views and a rooftop pool.', '12 Beach Road', 'Cape Town', 'South Africa'),
	(3, 'Kruger Bush Lodge', 'Lodge', 5, 3200.00, 'Exclusive five-star safari lodge on the banks of the Sabie River.', '1 Sabie River Drive', 'Hoedspruit', 'South Africa'),
	(4, 'Berg & Valley Guesthouse', 'Guesthouse', 3, 980.00, 'Cosy guesthouse at the foot of the Drakensberg with mountain views.', '7 Amphitheatre Lane', 'Bergville', 'South Africa'),
	(5, 'Durban Beachside Inn', 'Hotel', 3, 750.00, 'Affordable hotel steps from the Golden Mile beach and uShaka Marine World.', '45 Marine Parade', 'Durban', 'South Africa'),
	(6, 'Stellenbosch Manor', 'Boutique Hotel', 4, 2100.00, 'Historic Cape Dutch manor converted into an intimate boutique hotel.', '3 Dorp Street', 'Stellenbosch', 'South Africa'),
	(7, 'Soweto Backpackers', 'Hostel', 2, 320.00, 'Social hostel in the heart of Soweto, offering cultural walking tours.', '8 Vilakazi Street', 'Soweto', 'South Africa'),
	(8, 'Ubud Jungle Retreat', 'Resort', 5, 4500.00, 'Secluded jungle resort with private pool villas and daily yoga sessions.', '22 Monkey Forest Road', 'Ubud', 'Indonesia'),
	(9, 'Hotel Le Marais Paris', 'Hotel', 4, 6200.00, 'Chic boutique hotel in the historic Le Marais district, close to the Louvre.', '14 Rue de Bretagne', 'Paris', 'France'),
	(10, 'Masai Mara Tented Camp', 'Tented Camp', 5, 8900.00, 'Luxury tented camp overlooking the Mara River with guided game drives included.', 'Mara River, Narok', 'Narok', 'Kenya'),
	(11, 'Grand Baie Beach Resort', 'Resort', 5, 5500.00, 'All-inclusive luxury resort on a pristine lagoon beach in northern Mauritius.', '1 Coastal Road', 'Grand Baie', 'Mauritius'),
	(12, 'The Capital Menlyn Maine', 'Hotel', 4, 1200.00, 'Award-winning hotel in the heart of Pretoria\'s Menlyn precinct.', '1 Lemon Tree Street', 'Pretoria', 'South Africa'),
	(13, 'George Garden Route Lodge', 'Lodge', 3, 890.00, 'Tranquil garden lodge near the famous Garden Route National Park.', '5 Montagu Pass Road', 'George', 'South Africa'),
	(14, 'Knysna Quays Hotel', 'Hotel', 4, 1650.00, 'Contemporary hotel overlooking the Knysna Lagoon with a private jetty.', '1 Waterfront Drive', 'Knysna', 'South Africa'),
	(15, 'Addo Elephant Lodge', 'Lodge', 4, 2800.00, 'Boutique safari lodge on the border of Addo Elephant National Park.', '3 Addo Main Road', 'Addo', 'South Africa'),
	(16, 'Clifton Clifftop Villa', 'Villa', 5, 9500.00, 'Spectacular cliffside villa with direct access to Clifton 4th Beach.', '10 Clifton Road', 'Cape Town', 'South Africa'),
	(17, 'Pilanesberg Safari Camp', 'Tented Camp', 3, 1800.00, 'Comfortable tented camp within the Pilanesberg Game Reserve near Sun City.', 'Pilanesberg Reserve', 'Rustenburg', 'South Africa'),
	(18, 'uKhahlamba Mountain Cabins', 'Cabin', 3, 920.00, 'Rustic timber cabins in the Royal Natal National Park with fireplace and deck.', 'Royal Natal Park Road', 'Bergville', 'South Africa'),
	(19, 'Zanzibar Beach Bungalows', 'Bungalow', 4, 3100.00, 'Thatched beachfront bungalows on a quiet stretch of Zanzibar\'s east coast.', 'Paje Beach Road', 'Zanzibar', 'Tanzania'),
	(20, 'Victoria Falls Safari Lodge', 'Lodge', 5, 7200.00, 'Award-winning lodge with views of the Zambezi spray forest and elephant pool.', 'Zambezi Drive', 'Victoria Falls', 'Zimbabwe'),
	(21, 'Hermanus Whale Watch Inn', 'Guesthouse', 3, 870.00, 'Charming guesthouse in the whale-watching capital of the world on Walker Bay.', '5 Marine Drive', 'Hermanus', 'South Africa'),
	(22, 'Joburg Sandton Suites', 'Aparthotel', 4, 1450.00, 'Fully serviced corporate suites in the heart of Sandton\'s business and shopping hub.', '15 Maude Street', 'Johannesburg', 'South Africa');

-- Dumping structure for table triptest.accommodationimage
DROP TABLE IF EXISTS `accommodationimage`;
CREATE TABLE IF NOT EXISTS `accommodationimage` (
  `ImageID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AccommodationID` int(10) unsigned NOT NULL,
  `ImageURL` varchar(500) NOT NULL,
  PRIMARY KEY (`ImageID`),
  KEY `fk_accomimage_accom` (`AccommodationID`),
  CONSTRAINT `fk_accomimage_accom` FOREIGN KEY (`AccommodationID`) REFERENCES `accommodation` (`AccommodationID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.accommodationimage: ~21 rows (approximately)
INSERT INTO `accommodationimage` (`ImageID`, `AccommodationID`, `ImageURL`) VALUES
	(1, 1, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800'),
	(2, 2, 'https://images.unsplash.com/photo-1510798831971-661eb04b3739?w=800'),
	(3, 3, 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800'),
	(4, 4, 'https://images.unsplash.com/photo-1551882547-ff40c63fe2e2?w=800'),
	(5, 5, 'https://images.unsplash.com/photo-1570213489059-0aac6626cade?w=800'),
	(6, 6, 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800'),
	(7, 7, 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800'),
	(8, 8, 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800'),
	(9, 9, 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=800'),
	(10, 10, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800'),
	(11, 11, 'https://images.unsplash.com/photo-1551882547-ff40c63fe2e2?w=800'),
	(12, 12, 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800'),
	(13, 14, 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800'),
	(14, 15, 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=800'),
	(15, 16, 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=800'),
	(16, 17, 'https://images.unsplash.com/photo-1589182373726-e4f658ab50f0?w=800'),
	(17, 18, 'https://images.unsplash.com/photo-1504198322253-cfa87a0ff25f?w=800'),
	(18, 19, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800'),
	(19, 20, 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800'),
	(20, 21, 'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?w=800'),
	(21, 22, 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800');

-- Dumping structure for table triptest.agency
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.agency: ~11 rows (approximately)
INSERT INTO `agency` (`AgencyID`, `Name`, `Email`, `Password`, `Phone`, `JoinDate`, `Street`, `City`, `Country`) VALUES
	(2, 'Wanderlust SA Ltd', 'info@wanderlust.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+27123456789', '2024-01-10', '12 Long Street', 'Cape Town', 'South Africa'),
	(3, 'Safari Specialists', 'bookings@safarispec.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+27114567890', '2024-02-14', '5 Rissik Street', 'Johannesburg', 'South Africa'),
	(4, 'EuroQuest Travels', 'hello@euroquest.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+27312345678', '2024-03-01', '88 Marine Parade', 'Durban', 'South Africa'),
	(5, 'Pacific Dream Tours', 'tours@pacificdream.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+27124561234', '2024-03-20', '22 Lynnwood Road', 'Pretoria', 'South Africa'),
	(6, 'Horizon Africa Travel', 'info@horizonafrica.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+27219876543', '2024-04-05', '3 Adderley Street', 'Cape Town', 'South Africa'),
	(7, 'Cape Getaways', 'info@capegetaways.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+27214001122', '2024-05-10', '77 Loop Street', 'Cape Town', 'South Africa'),
	(8, 'Savanna Escapes', 'hello@savannaescapes.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+27113009988', '2024-06-01', '14 Jan Smuts Avenue', 'Johannesburg', 'South Africa'),
	(9, 'Indian Ocean Holidays', 'book@indianoceanholidays.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+27317771234', '2024-07-15', '2 Marine Drive', 'Durban', 'South Africa'),
	(10, 'Mountain High Tours', 'tours@mountainhigh.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+27335552244', '2024-08-20', '9 Church Street', 'Pietermaritzburg', 'South Africa'),
	(11, 'Global Roamers', 'support@globalroamers.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+27124430011', '2024-09-05', '45 Burnett Street', 'Pretoria', 'South Africa'),
	(12, 'Desert Sun Travel', 'info@desertsun.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+27545553344', '2024-10-01', '22 Kalahari Road', 'Upington', 'South Africa');

-- Dumping structure for table triptest.agencyreview
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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.agencyreview: ~22 rows (approximately)
INSERT INTO `agencyreview` (`ReviewID`, `TravellerID`, `AgencyID`, `Rating`, `Comment`, `CreatedAt`) VALUES
	(2, 1, 1, 5, 'Wanderlust SA arranged everything perfectly. Communication was prompt and professional throughout.', '2026-06-24 10:00:00'),
	(3, 2, 2, 5, 'Safari Specialists know their craft. They matched us with the ideal lodge for our travel style.', '2026-07-08 09:00:00'),
	(4, 3, 3, 4, 'EuroQuest handled our Paris trip well. A minor transfer delay but they resolved it quickly.', '2026-09-17 14:00:00'),
	(5, 4, 4, 5, 'Pacific Dream Tours made our Bali experience seamless from booking to checkout.', '2026-08-30 11:00:00'),
	(6, 5, 1, 4, 'The Stellenbosch package was beautifully put together. Will definitely book with Wanderlust again.', '2026-08-17 08:30:00'),
	(7, 6, 2, 5, 'Safari Specialists went above and beyond organising our Drakensberg permits and guide.', '2026-10-19 16:00:00'),
	(8, 7, 5, 4, 'Horizon Africa Travel offered great local knowledge and flexible scheduling.', '2026-07-21 13:00:00'),
	(9, 8, 3, 5, 'EuroQuest\'s Kenya package was the trip of a lifetime. Every detail was thoughtfully arranged.', '2026-10-09 10:30:00'),
	(10, 9, 4, 5, 'Pacific Dream Tours\' Mauritius package was flawless. Already planning our next trip with them.', '2026-11-19 09:30:00'),
	(11, 10, 5, 4, 'Horizon Africa\'s Soweto tour was authentic and respectful of the community. Highly recommend.', '2026-09-10 15:30:00'),
	(12, 12, 7, 5, 'Cape Getaways handled every detail of our Garden Route trip. Truly professional and responsive.', '2026-07-17 09:00:00'),
	(13, 13, 7, 4, 'Great communication from Cape Getaways. Minor delay on the ferry booking but fixed quickly.', '2026-08-09 10:00:00'),
	(14, 14, 8, 5, 'Savanna Escapes made our Joburg itinerary feel personal. The Apartheid Museum timing was perfect.', '2026-09-06 11:00:00'),
	(15, 15, 8, 4, 'Savanna Escapes gave great value. The Pilanesberg lodge exceeded expectations for the price.', '2026-10-08 09:30:00'),
	(16, 16, 9, 5, 'Indian Ocean Holidays really know Durban. We felt like locals. Will absolutely book again.', '2026-07-26 14:00:00'),
	(17, 17, 9, 4, 'The KZN combo was a bold itinerary and it delivered. Agency staff were knowledgeable and friendly.', '2026-10-24 16:00:00'),
	(18, 18, 10, 5, 'Mountain High Tours had the best hiking guides we have ever encountered. Pure passion for the berg.', '2026-09-27 10:00:00'),
	(19, 19, 10, 5, 'Hermanus at whale season with Mountain High was a dream. Every recommendation was spot on.', '2026-09-17 13:00:00'),
	(20, 20, 11, 5, 'Global Roamers turned Victoria Falls into the most well-organised trip of our lives.', '2026-08-22 08:00:00'),
	(21, 21, 11, 4, 'Zanzibar was incredible. Global Roamers\' local contacts made a real difference to the experience.', '2026-11-10 12:00:00'),
	(22, 22, 12, 5, 'Desert Sun Travel unlocked a side of South Africa we never knew existed. Stars in the Kalahari!', '2026-07-31 20:00:00'),
	(23, 23, 12, 4, 'Addo exceeded all expectations. Desert Sun did a great job pairing it with a PE city day.', '2026-08-15 15:00:00');

-- Dumping structure for table triptest.airport
DROP TABLE IF EXISTS `airport`;
CREATE TABLE IF NOT EXISTS `airport` (
  `AirportID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Code` char(3) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `City` varchar(100) NOT NULL,
  `Country` varchar(100) NOT NULL,
  PRIMARY KEY (`AirportID`),
  UNIQUE KEY `Code` (`Code`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.airport: ~10 rows (approximately)
INSERT INTO `airport` (`AirportID`, `Code`, `Name`, `City`, `Country`) VALUES
	(3, 'CPT', 'Cape Town International Airport', 'Cape Town', 'South Africa'),
	(4, 'JNB', 'O.R. Tambo International Airport', 'Johannesburg', 'South Africa'),
	(5, 'DUR', 'King Shaka International Airport', 'Durban', 'South Africa'),
	(6, 'PLZ', 'Chief Dawid Stuurman International Airport', 'Port Elizabeth', 'South Africa'),
	(7, 'GRJ', 'George Airport', 'George', 'South Africa'),
	(8, 'CDG', 'Charles de Gaulle Airport', 'Paris', 'France'),
	(9, 'DXB', 'Dubai International Airport', 'Dubai', 'UAE'),
	(10, 'DPS', 'Ngurah Rai International Airport', 'Bali', 'Indonesia'),
	(11, 'NBO', 'Jomo Kenyatta International Airport', 'Nairobi', 'Kenya'),
	(12, 'MRU', 'Sir Seewoosagur Ramgoolam International', 'Mauritius', 'Mauritius');

-- Dumping structure for table triptest.attraction
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.attraction: ~21 rows (approximately)
INSERT INTO `attraction` (`AttractionID`, `Name`, `Description`, `EntranceFee`, `OpeningHours`, `Street`, `City`, `Country`) VALUES
	(2, 'Table Mountain Cableway', 'Iconic rotating cable car to the top of Table Mountain with 360° views.', 420.00, '08:00 - 19:00', 'Tafelberg Road', 'Cape Town', 'South Africa'),
	(3, 'Kruger Game Drive', 'Guided open-vehicle game drive into the heart of Kruger National Park.', 850.00, '05:30 - 09:30', 'Paul Kruger Gate', 'Hoedspruit', 'South Africa'),
	(4, 'Tugela Falls Hike', 'Challenging hike to the second-tallest waterfall in the world.', 0.00, '06:00 - 18:00', 'Royal Natal Park Gate', 'Bergville', 'South Africa'),
	(5, 'uShaka Marine World', 'One of Africa\'s largest aquariums and water parks on Durban\'s beachfront.', 280.00, '09:00 - 17:00', '1 King Shaka Ave', 'Durban', 'South Africa'),
	(6, 'Franschhoek Wine Tram', 'Hop-on hop-off tram tour linking the finest wine estates in Franschhoek valley.', 350.00, '10:00 - 17:00', '32 Huguenot Road', 'Franschhoek', 'South Africa'),
	(7, 'Hector Pieterson Museum', 'Powerful museum commemorating the 1976 Soweto Uprising.', 80.00, '08:00 - 17:00', '8372 Khumalo Street', 'Soweto', 'South Africa'),
	(8, 'Tegalalang Rice Terraces', 'Stunning UNESCO-listed rice terraces offering photo stops and cultural insight.', 50.00, '07:00 - 18:00', 'Jalan Raya Tegalalang', 'Ubud', 'Indonesia'),
	(9, 'Eiffel Tower', 'Paris\'s most iconic landmark offering breathtaking views over the city.', 400.00, '09:00 - 23:45', 'Champ de Mars', 'Paris', 'France'),
	(10, 'Masai Village Experience', 'Immersive cultural visit to an authentic Masai village with traditional ceremonies.', 200.00, '09:00 - 16:00', 'Mara Reserve Road', 'Narok', 'Kenya'),
	(11, 'Blue Bay Snorkelling', 'Guided snorkelling in Mauritius\'s pristine Blue Bay Marine Park.', 180.00, '08:00 - 16:00', 'Blue Bay Road', 'Mahebourg', 'Mauritius'),
	(12, 'Robben Island Ferry', 'UNESCO-listed island where Nelson Mandela was imprisoned; guided by former inmates.', 450.00, '09:00 - 15:00', 'Clock Tower Precinct', 'Cape Town', 'South Africa'),
	(13, 'Two Oceans Aquarium', 'Award-winning aquarium at the V&A Waterfront showcasing Indian and Atlantic Ocean life.', 260.00, '09:00 - 18:00', 'Dock Road, V&A Waterfront', 'Cape Town', 'South Africa'),
	(14, 'Boulders Beach Penguins', 'Colony of African penguins on a sheltered beach near Simon\'s Town.', 160.00, '08:00 - 17:00', 'Boulders Road', 'Simon\'s Town', 'South Africa'),
	(15, 'Apartheid Museum', 'Comprehensive museum telling the story of apartheid and South Africa\'s journey to democracy.', 200.00, '09:00 - 17:00', 'Northern Parkway', 'Johannesburg', 'South Africa'),
	(16, 'Addo Elephant Park Drive', 'Self-drive through Africa\'s third-largest national park teeming with elephants.', 232.00, '07:00 - 19:00', 'Addo Main Road', 'Addo', 'South Africa'),
	(17, 'Tsitsikamma Canopy Tour', 'Thrilling zipline tour through the ancient Tsitsikamma forest canopy.', 850.00, '07:30 - 15:30', 'Storms River Village', 'Tsitsikamma', 'South Africa'),
	(18, 'Pilanesberg Game Drive', 'Guided early-morning game drive in the Big Five malaria-free Pilanesberg reserve.', 600.00, '05:30 - 09:00', 'Pilanesberg Reserve', 'Rustenburg', 'South Africa'),
	(19, 'Victoria Falls Tour', 'Walk the rainforest trail beside the world\'s largest waterfall curtain.', 30.00, '06:00 - 18:00', 'Zambezi Drive', 'Victoria Falls', 'Zimbabwe'),
	(20, 'Zanzibar Spice Farm Tour', 'Guided tour of a traditional clove, cinnamon and vanilla spice plantation.', 25.00, '08:00 - 17:00', 'Kidichi Road', 'Zanzibar', 'Tanzania'),
	(21, 'Hermanus Whale Watching', 'Boat-based whale watching during southern right whale season (Jul–Nov).', 800.00, '08:00 - 16:00', 'New Harbour', 'Hermanus', 'South Africa'),
	(22, 'Gold Reef City Theme Park', 'Johannesburg\'s heritage theme park built on a working gold mine, with rides and shows.', 200.00, '09:30 - 17:00', 'Northern Parkway', 'Johannesburg', 'South Africa');

-- Dumping structure for table triptest.attractionimage
DROP TABLE IF EXISTS `attractionimage`;
CREATE TABLE IF NOT EXISTS `attractionimage` (
  `ImageID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AttractionID` int(10) unsigned NOT NULL,
  `ImageURL` varchar(500) NOT NULL,
  PRIMARY KEY (`ImageID`),
  KEY `fk_attrimage_attr` (`AttractionID`),
  CONSTRAINT `fk_attrimage_attr` FOREIGN KEY (`AttractionID`) REFERENCES `attraction` (`AttractionID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table triptest.attractionimage: ~21 rows (approximately)
INSERT INTO `attractionimage` (`ImageID`, `AttractionID`, `ImageURL`) VALUES
	(1, 1, 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=800'),
	(2, 2, 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=800'),
	(3, 3, 'https://images.unsplash.com/photo-1589182373726-e4f658ab50f0?w=800'),
	(4, 4, 'https://images.unsplash.com/photo-1504198322253-cfa87a0ff25f?w=800'),
	(5, 5, 'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?w=800'),
	(6, 6, 'https://images.unsplash.com/photo-1584647929572-4d1e9d0b1a73?w=800'),
	(7, 7, 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800'),
	(8, 8, 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800'),
	(9, 9, 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=800'),
	(10, 10, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800'),
	(11, 11, 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=800'),
	(12, 12, 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=800'),
	(13, 14, 'https://images.unsplash.com/photo-1570213489059-0aac6626cade?w=800'),
	(14, 15, 'https://images.unsplash.com/photo-1584647929572-4d1e9d0b1a73?w=800'),
	(15, 16, 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=800'),
	(16, 17, 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800'),
	(17, 18, 'https://images.unsplash.com/photo-1589182373726-e4f658ab50f0?w=800'),
	(18, 19, 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800'),
	(19, 20, 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800'),
	(20, 21, 'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?w=800'),
	(21, 22, 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=800');

-- Dumping structure for table triptest.booking
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.booking: ~29 rows (approximately)
INSERT INTO `booking` (`BookingID`, `TravellerID`, `PackageID`, `Date`, `NumberOfPeople`, `Status`, `TotalPrice`) VALUES
	(2, 1, 1, '2026-05-01 10:00:00', 2, 'Confirmed', 29000.00),
	(3, 2, 2, '2026-05-03 11:30:00', 1, 'Confirmed', 22000.00),
	(4, 3, 3, '2026-05-10 09:00:00', 2, 'Pending', 70000.00),
	(5, 4, 4, '2026-06-01 14:00:00', 1, 'Confirmed', 18500.00),
	(6, 5, 5, '2026-06-10 08:30:00', 2, 'Confirmed', 37800.00),
	(7, 6, 6, '2026-06-15 16:00:00', 3, 'Pending', 34500.00),
	(8, 7, 7, '2026-06-20 12:00:00', 4, 'Confirmed', 31200.00),
	(9, 8, 8, '2026-07-01 10:00:00', 2, 'Confirmed', 76000.00),
	(10, 9, 9, '2026-07-05 09:00:00', 2, 'Pending', 59000.00),
	(11, 10, 10, '2026-07-10 15:00:00', 5, 'Confirmed', 31000.00),
	(12, 1, 11, '2026-06-01 11:00:00', 2, 'Completed', 19600.00),
	(13, 2, 12, '2026-06-05 13:00:00', 1, 'Completed', 16500.00),
	(14, 12, 14, '2026-06-10 09:00:00', 2, 'Confirmed', 24800.00),
	(15, 13, 15, '2026-06-15 11:00:00', 1, 'Confirmed', 10800.00),
	(16, 14, 16, '2026-07-01 10:30:00', 3, 'Confirmed', 22500.00),
	(17, 15, 17, '2026-07-05 14:00:00', 2, 'Pending', 22400.00),
	(18, 16, 18, '2026-06-20 08:00:00', 2, 'Confirmed', 27000.00),
	(19, 17, 19, '2026-07-10 09:30:00', 1, 'Pending', 17800.00),
	(20, 18, 20, '2026-07-15 16:00:00', 4, 'Confirmed', 38400.00),
	(21, 19, 21, '2026-07-20 10:00:00', 2, 'Confirmed', 17800.00),
	(22, 20, 22, '2026-06-25 13:00:00', 2, 'Confirmed', 53000.00),
	(23, 21, 23, '2026-07-30 11:30:00', 2, 'Pending', 44000.00),
	(24, 22, 24, '2026-06-18 09:00:00', 1, 'Confirmed', 14200.00),
	(25, 23, 25, '2026-06-22 15:00:00', 2, 'Confirmed', 27200.00),
	(26, 12, 22, '2026-07-01 10:00:00', 1, 'Cancelled', 26500.00),
	(27, 14, 23, '2026-08-01 08:00:00', 2, 'Confirmed', 44000.00),
	(28, 16, 14, '2026-06-28 12:00:00', 1, 'Completed', 12400.00),
	(29, 18, 16, '2026-08-10 09:00:00', 2, 'Pending', 15000.00),
	(30, 20, 17, '2026-08-15 14:30:00', 3, 'Confirmed', 33600.00);

-- Dumping structure for table triptest.destination
DROP TABLE IF EXISTS `destination`;
CREATE TABLE IF NOT EXISTS `destination` (
  `DestinationID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(200) NOT NULL,
  `City` varchar(100) NOT NULL,
  `Region` varchar(100) DEFAULT NULL,
  `Country` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  PRIMARY KEY (`DestinationID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.destination: ~10 rows (approximately)
INSERT INTO `destination` (`DestinationID`, `Name`, `City`, `Region`, `Country`, `Description`) VALUES
	(2, 'Cape Town Waterfront', 'Cape Town', 'Western Cape', 'South Africa', 'The V&A Waterfront and surrounding Table Mountain area, iconic for coastal scenery and wine routes.'),
	(3, 'Kruger National Park', 'Hoedspruit', 'Limpopo', 'South Africa', 'One of Africa\'s largest game reserves, home to the Big Five and diverse ecosystems.'),
	(4, 'Drakensberg Mountains', 'Bergville', 'KwaZulu-Natal', 'South Africa', 'A UNESCO World Heritage Site featuring dramatic mountain scenery, hiking and San rock art.'),
	(5, 'Durban Golden Mile', 'Durban', 'KwaZulu-Natal', 'South Africa', 'A vibrant beachfront strip with warm Indian Ocean waters, markets and cultural diversity.'),
	(6, 'Stellenbosch Winelands', 'Stellenbosch', 'Western Cape', 'South Africa', 'South Africa\'s premier wine region with historic Cape Dutch architecture and renowned estates.'),
	(7, 'Soweto Heritage District', 'Soweto', 'Gauteng', 'South Africa', 'A culturally rich township that shaped South Africa\'s history, home to the Hector Pieterson Museum.'),
	(8, 'Bali Cultural Triangle', 'Ubud', 'Bali', 'Indonesia', 'The spiritual and artistic heart of Bali, surrounded by terraced rice paddies and ancient temples.'),
	(9, 'Paris City Centre', 'Paris', 'Île-de-France', 'France', 'The City of Light, renowned for the Eiffel Tower, the Louvre, haute cuisine and fashion.'),
	(10, 'Masai Mara Reserve', 'Narok', 'Rift Valley', 'Kenya', 'Famous for the Great Wildebeest Migration and exceptional big-cat sightings year-round.'),
	(11, 'Mauritius North Coast', 'Grand Baie', 'Rivière du Rempart', 'Mauritius', 'Crystal-clear lagoons, coral reefs and luxury beach resorts on the northern tip of Mauritius.');

-- Dumping structure for table triptest.flight
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.flight: ~14 rows (approximately)
INSERT INTO `flight` (`FlightID`, `FlightNumber`, `Airline`, `DepartureDateTime`, `ArrivalDateTime`, `BaseCost`, `OriginAirportID`, `DestinationAirportID`) VALUES
	(2, 'FA201', 'FlySafair', '2026-06-15 06:00:00', '2026-06-15 08:10:00', 1100.00, 2, 1),
	(3, 'FA301', 'FlySafair', '2026-06-22 16:00:00', '2026-06-22 18:10:00', 1100.00, 1, 2),
	(4, 'SA401', 'South African Airways', '2026-07-01 07:30:00', '2026-07-01 09:00:00', 1350.00, 2, 3),
	(5, 'SA402', 'South African Airways', '2026-07-06 14:00:00', '2026-07-06 15:30:00', 1350.00, 3, 2),
	(6, 'MN501', 'Mango Airlines', '2026-09-10 05:00:00', '2026-09-10 19:30:00', 14200.00, 2, 6),
	(7, 'MN502', 'Mango Airlines', '2026-09-15 21:00:00', '2026-09-16 11:30:00', 14200.00, 6, 2),
	(8, 'EK601', 'Emirates', '2026-08-20 22:00:00', '2026-08-21 10:00:00', 11500.00, 2, 8),
	(9, 'EK602', 'Emirates', '2026-08-28 14:00:00', '2026-08-29 02:00:00', 11500.00, 8, 2),
	(10, 'KQ701', 'Kenya Airways', '2026-10-01 08:00:00', '2026-10-01 13:30:00', 5800.00, 2, 9),
	(11, 'KQ702', 'Kenya Airways', '2026-10-07 15:00:00', '2026-10-07 20:30:00', 5800.00, 9, 2),
	(12, 'MK801', 'Air Mauritius', '2026-11-10 20:00:00', '2026-11-11 01:30:00', 6200.00, 2, 10),
	(13, 'MK802', 'Air Mauritius', '2026-11-17 03:00:00', '2026-11-17 08:30:00', 6200.00, 10, 2),
	(14, 'FA101', 'FlySafair', '2026-07-15 07:00:00', '2026-07-15 08:30:00', 950.00, 2, 5),
	(15, 'FA102', 'FlySafair', '2026-07-20 15:00:00', '2026-07-20 16:30:00', 950.00, 5, 2);

-- Dumping structure for table triptest.flightclass
DROP TABLE IF EXISTS `flightclass`;
CREATE TABLE IF NOT EXISTS `flightclass` (
  `FlightID` int(10) unsigned NOT NULL,
  `ClassName` enum('Economy','Business','First Class') NOT NULL,
  PRIMARY KEY (`FlightID`,`ClassName`),
  CONSTRAINT `fk_flightclass_flight` FOREIGN KEY (`FlightID`) REFERENCES `flight` (`FlightID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.flightclass: ~32 rows (approximately)
INSERT INTO `flightclass` (`FlightID`, `ClassName`) VALUES
	(1, 'Economy'),
	(1, 'Business'),
	(2, 'Economy'),
	(2, 'Business'),
	(3, 'Economy'),
	(3, 'Business'),
	(4, 'Economy'),
	(4, 'Business'),
	(5, 'Economy'),
	(5, 'Business'),
	(5, 'First Class'),
	(6, 'Economy'),
	(6, 'Business'),
	(6, 'First Class'),
	(7, 'Economy'),
	(7, 'Business'),
	(7, 'First Class'),
	(8, 'Economy'),
	(8, 'Business'),
	(8, 'First Class'),
	(9, 'Economy'),
	(9, 'Business'),
	(10, 'Economy'),
	(10, 'Business'),
	(11, 'Economy'),
	(11, 'Business'),
	(11, 'First Class'),
	(12, 'Economy'),
	(12, 'Business'),
	(12, 'First Class'),
	(13, 'Economy'),
	(14, 'Economy');

-- Dumping structure for table triptest.grouptrip
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.grouptrip: ~11 rows (approximately)
INSERT INTO `grouptrip` (`GroupTripID`, `AgencyID`, `MaxCapacity`, `Status`, `StartDate`, `EndDate`) VALUES
	(1, 1, 10, 'Open', '2026-06-15', '2026-06-22'),
	(2, 2, 8, 'Open', '2026-07-01', '2026-07-06'),
	(3, 3, 12, 'Open', '2026-09-10', '2026-09-15'),
	(4, 4, 15, 'Full', '2026-08-20', '2026-08-28'),
	(5, 5, 20, 'Completed', '2026-05-01', '2026-05-05'),
	(6, 7, 12, 'Open', '2026-07-10', '2026-07-15'),
	(7, 8, 16, 'Open', '2026-09-01', '2026-09-04'),
	(8, 9, 10, 'Open', '2026-07-20', '2026-07-24'),
	(9, 10, 8, 'Open', '2026-09-20', '2026-09-25'),
	(10, 11, 12, 'Full', '2026-08-15', '2026-08-20'),
	(11, 12, 6, 'Completed', '2026-07-25', '2026-07-29');

-- Dumping structure for table triptest.grouptripmember
DROP TABLE IF EXISTS `grouptripmember`;
CREATE TABLE IF NOT EXISTS `grouptripmember` (
  `GroupTripID` int(10) unsigned NOT NULL,
  `TravellerID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`GroupTripID`,`TravellerID`),
  KEY `fk_gtmember_traveller` (`TravellerID`),
  CONSTRAINT `fk_gtmember_traveller` FOREIGN KEY (`TravellerID`) REFERENCES `traveller` (`TravellerID`),
  CONSTRAINT `fk_gtmember_trip` FOREIGN KEY (`GroupTripID`) REFERENCES `grouptrip` (`GroupTripID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.grouptripmember: ~52 rows (approximately)
INSERT INTO `grouptripmember` (`GroupTripID`, `TravellerID`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(2, 4),
	(2, 5),
	(3, 6),
	(3, 7),
	(4, 1),
	(4, 2),
	(4, 3),
	(4, 4),
	(4, 5),
	(4, 6),
	(4, 7),
	(4, 8),
	(5, 8),
	(5, 9),
	(5, 10),
	(6, 12),
	(6, 13),
	(6, 15),
	(6, 17),
	(7, 14),
	(7, 16),
	(7, 18),
	(7, 20),
	(7, 22),
	(8, 13),
	(8, 19),
	(8, 21),
	(9, 15),
	(9, 17),
	(9, 23),
	(9, 24),
	(10, 12),
	(10, 13),
	(10, 14),
	(10, 15),
	(10, 16),
	(10, 17),
	(10, 18),
	(10, 19),
	(10, 20),
	(10, 21),
	(10, 22),
	(10, 23),
	(11, 12),
	(11, 14),
	(11, 22),
	(11, 23),
	(11, 24),
	(11, 25);

-- Dumping structure for table triptest.package
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.package: ~24 rows (approximately)
INSERT INTO `package` (`PackageID`, `AgencyID`, `Title`, `Description`, `StartDate`, `EndDate`, `MaxParticipants`, `TotalPrice`) VALUES
	(2, 1, 'Ultimate Cape Town Coastal Escape', 'A 7-day coastal getaway with wine tasting, Table Mountain and V&A Waterfront dining.', '2026-06-15', '2026-06-22', 12, 14500.00),
	(3, 2, 'Wild Kruger Luxury Safari', 'Experience the Big Five with 5 nights in a luxury lodge and expert guided game drives.', '2026-07-01', '2026-07-06', 8, 22000.00),
	(4, 3, 'Parisian Romance & Culture', 'A curated 5-day tour of the Louvre, Eiffel Tower and hidden bistros in Montmartre.', '2026-09-10', '2026-09-15', 10, 35000.00),
	(5, 4, 'Ubud Zen & Wellness Escape', 'Rejuvenate with daily yoga, spa treatments and rice terrace walks over 8 days in Bali.', '2026-08-20', '2026-08-28', 15, 18500.00),
	(6, 1, 'Winelands Wellness & Spa Retreat', 'A 5-day luxury escape in Stellenbosch with vineyard tours, spa treatments and fine dining.', '2026-08-10', '2026-08-15', 6, 18900.00),
	(7, 2, 'Drakensberg Royal Natal Hike', 'A 5-day guided hiking expedition to Tugela Falls and the Amphitheatre.', '2026-10-12', '2026-10-17', 10, 11500.00),
	(8, 5, 'Durban Culture & Coast', 'Explore Durban\'s Golden Mile, taste authentic bunny chow and dive into Zulu heritage over 4 days.', '2026-07-15', '2026-07-19', 20, 7800.00),
	(9, 3, 'Masai Mara Migration Safari', 'Witness the Great Wildebeest Migration on a 7-day Kenya safari with luxury tented camp stays.', '2026-10-01', '2026-10-07', 10, 38000.00),
	(10, 4, 'Mauritius Tropical Paradise', 'An all-inclusive 7-day retreat on the pristine lagoons of northern Mauritius.', '2026-11-10', '2026-11-17', 16, 29500.00),
	(11, 5, 'Soweto & Joburg Heritage Tour', 'A 3-day cultural immersion covering the Hector Pieterson Museum, Vilakazi Street and Gold Reef City.', '2026-09-05', '2026-09-08', 25, 6200.00),
	(12, 1, 'Garden Route Grand Drive', 'A 6-day self-drive adventure from Cape Town through Knysna to Port Elizabeth along the Garden Route.', '2026-07-15', '2026-07-21', 14, 9800.00),
	(13, 2, 'Cape Town & Winelands Combo', 'Combine two iconic Western Cape experiences: Cape Town city and the Stellenbosch Winelands in 5 days.', '2026-08-01', '2026-08-06', 12, 16500.00),
	(14, 7, 'Knysna Lagoon & Garden Route', 'A relaxed 5-day stay in Knysna with lagoon cruises, forest hikes and seafood feasts.', '2026-07-10', '2026-07-15', 10, 12400.00),
	(15, 7, 'Cape Peninsula Full Explorer', 'Discover Cape Point, Boulders Beach penguins and Clifton on this 4-day Cape adventure.', '2026-08-03', '2026-08-07', 12, 10800.00),
	(16, 8, 'Joburg City & Soweto Immersion', 'A 3-day urban experience covering the Apartheid Museum, Gold Reef City and authentic township dining.', '2026-09-01', '2026-09-04', 20, 7500.00),
	(17, 8, 'Pilanesberg Big Five Weekend', 'A 3-day malaria-free Big Five safari from Johannesburg, perfect for families.', '2026-10-03', '2026-10-06', 16, 11200.00),
	(18, 9, 'Durban Luxury Beachfront Break', 'Indulge in 4 days on Durban\'s Golden Mile with spa treatments and fine coastal dining.', '2026-07-20', '2026-07-24', 8, 13500.00),
	(19, 9, 'KZN Mountains & Coast Combo', 'Combine the uKhahlamba Drakensberg peaks with Durban\'s warm Indian Ocean in one 7-day itinerary.', '2026-10-15', '2026-10-22', 14, 17800.00),
	(20, 10, 'Drakensberg Peaks & Valleys', 'Five days of guided trails, abseiling and stargazing in the Southern Berg.', '2026-09-20', '2026-09-25', 10, 9600.00),
	(21, 10, 'Hermanus Whale Season Getaway', 'A 3-day coastal escape timed for peak southern right whale sightings on Walker Bay.', '2026-09-12', '2026-09-15', 8, 8900.00),
	(22, 11, 'Victoria Falls Adventure', 'Experience the might of the falls, a sunset Zambezi cruise and optional white-water rafting over 5 days.', '2026-08-15', '2026-08-20', 12, 26500.00),
	(23, 11, 'Zanzibar Island Escape', 'A 7-day tropical retreat combining Stone Town culture with spice farm tours and beach relaxation.', '2026-11-01', '2026-11-08', 10, 22000.00),
	(24, 12, 'Kalahari Desert & Stargazing', 'Explore the red dunes of the Kalahari and witness unrivalled night skies on this 4-day desert adventure.', '2026-07-25', '2026-07-29', 8, 14200.00),
	(25, 12, 'Addo Elephant & PE City Break', 'Three nights in Addo Elephant Park paired with a day exploring Port Elizabeth\'s historic city centre.', '2026-08-10', '2026-08-13', 12, 13600.00);

-- Dumping structure for table triptest.packageaccommodation
DROP TABLE IF EXISTS `packageaccommodation`;
CREATE TABLE IF NOT EXISTS `packageaccommodation` (
  `PackageID` int(10) unsigned NOT NULL,
  `AccommodationID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`PackageID`,`AccommodationID`),
  KEY `fk_pkgaccom_accom` (`AccommodationID`),
  CONSTRAINT `fk_pkgaccom_accom` FOREIGN KEY (`AccommodationID`) REFERENCES `accommodation` (`AccommodationID`),
  CONSTRAINT `fk_pkgaccom_pkg` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.packageaccommodation: ~25 rows (approximately)
INSERT INTO `packageaccommodation` (`PackageID`, `AccommodationID`) VALUES
	(1, 1),
	(2, 2),
	(3, 8),
	(4, 7),
	(5, 5),
	(6, 3),
	(7, 4),
	(8, 9),
	(9, 10),
	(10, 6),
	(11, 12),
	(12, 1),
	(14, 14),
	(15, 16),
	(16, 22),
	(17, 17),
	(18, 5),
	(19, 5),
	(19, 18),
	(20, 18),
	(21, 21),
	(22, 20),
	(23, 19),
	(24, 4),
	(25, 15);

-- Dumping structure for table triptest.packageattraction
DROP TABLE IF EXISTS `packageattraction`;
CREATE TABLE IF NOT EXISTS `packageattraction` (
  `PackageID` int(10) unsigned NOT NULL,
  `AttractionID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`PackageID`,`AttractionID`),
  KEY `fk_pkgattr_attr` (`AttractionID`),
  CONSTRAINT `fk_pkgattr_attr` FOREIGN KEY (`AttractionID`) REFERENCES `attraction` (`AttractionID`),
  CONSTRAINT `fk_pkgattr_pkg` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.packageattraction: ~34 rows (approximately)
INSERT INTO `packageattraction` (`PackageID`, `AttractionID`) VALUES
	(1, 1),
	(1, 11),
	(1, 12),
	(2, 2),
	(3, 8),
	(4, 7),
	(5, 5),
	(6, 3),
	(7, 4),
	(8, 9),
	(9, 10),
	(10, 6),
	(11, 1),
	(11, 12),
	(12, 1),
	(12, 5),
	(14, 13),
	(14, 17),
	(15, 2),
	(15, 12),
	(15, 14),
	(16, 7),
	(16, 15),
	(16, 22),
	(17, 18),
	(18, 5),
	(19, 4),
	(19, 5),
	(20, 4),
	(21, 21),
	(22, 19),
	(23, 20),
	(24, 3),
	(25, 16);

-- Dumping structure for table triptest.packagedestination
DROP TABLE IF EXISTS `packagedestination`;
CREATE TABLE IF NOT EXISTS `packagedestination` (
  `PackageID` int(10) unsigned NOT NULL,
  `DestinationID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`PackageID`,`DestinationID`),
  KEY `fk_pkgdest_dest` (`DestinationID`),
  CONSTRAINT `fk_pkgdest_dest` FOREIGN KEY (`DestinationID`) REFERENCES `destination` (`DestinationID`),
  CONSTRAINT `fk_pkgdest_pkg` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.packagedestination: ~26 rows (approximately)
INSERT INTO `packagedestination` (`PackageID`, `DestinationID`) VALUES
	(1, 1),
	(2, 2),
	(3, 8),
	(4, 7),
	(5, 5),
	(6, 3),
	(7, 4),
	(8, 9),
	(9, 10),
	(10, 6),
	(11, 1),
	(12, 1),
	(12, 5),
	(14, 2),
	(15, 2),
	(16, 7),
	(17, 7),
	(18, 5),
	(19, 4),
	(19, 5),
	(20, 4),
	(21, 2),
	(22, 10),
	(23, 11),
	(24, 7),
	(25, 3);

-- Dumping structure for table triptest.packageflight
DROP TABLE IF EXISTS `packageflight`;
CREATE TABLE IF NOT EXISTS `packageflight` (
  `PackageID` int(10) unsigned NOT NULL,
  `FlightID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`PackageID`,`FlightID`),
  KEY `fk_pkgflight_flight` (`FlightID`),
  CONSTRAINT `fk_pkgflight_flight` FOREIGN KEY (`FlightID`) REFERENCES `flight` (`FlightID`),
  CONSTRAINT `fk_pkgflight_pkg` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.packageflight: ~48 rows (approximately)
INSERT INTO `packageflight` (`PackageID`, `FlightID`) VALUES
	(1, 1),
	(1, 2),
	(2, 3),
	(2, 4),
	(3, 5),
	(3, 6),
	(4, 7),
	(4, 8),
	(5, 1),
	(5, 2),
	(6, 3),
	(6, 4),
	(7, 13),
	(7, 14),
	(8, 9),
	(8, 10),
	(9, 11),
	(9, 12),
	(10, 1),
	(10, 2),
	(11, 13),
	(11, 14),
	(12, 1),
	(12, 2),
	(14, 14),
	(14, 15),
	(15, 2),
	(15, 3),
	(16, 2),
	(16, 3),
	(17, 2),
	(17, 3),
	(18, 2),
	(18, 3),
	(19, 2),
	(19, 3),
	(20, 2),
	(20, 3),
	(21, 2),
	(21, 3),
	(22, 10),
	(22, 11),
	(23, 12),
	(23, 13),
	(24, 2),
	(24, 3),
	(25, 4),
	(25, 5);

-- Dumping structure for table triptest.packageimage
DROP TABLE IF EXISTS `packageimage`;
CREATE TABLE IF NOT EXISTS `packageimage` (
  `ImageID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PackageID` int(10) unsigned NOT NULL,
  `ImageURL` varchar(500) NOT NULL,
  PRIMARY KEY (`ImageID`),
  KEY `fk_packageimage_pkg` (`PackageID`),
  CONSTRAINT `fk_packageimage_pkg` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.packageimage: ~24 rows (approximately)
INSERT INTO `packageimage` (`ImageID`, `PackageID`, `ImageURL`) VALUES
	(1, 1, 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=800'),
	(2, 2, 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=800'),
	(3, 3, 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800'),
	(4, 4, 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800'),
	(5, 5, 'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?w=800'),
	(6, 6, 'https://images.unsplash.com/photo-1589182373726-e4f658ab50f0?w=800'),
	(7, 7, 'https://images.unsplash.com/photo-1504198322253-cfa87a0ff25f?w=800'),
	(8, 8, 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=800'),
	(9, 9, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800'),
	(10, 10, 'https://images.unsplash.com/photo-1584647929572-4d1e9d0b1a73?w=800'),
	(11, 11, 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800'),
	(12, 12, 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=800'),
	(13, 14, 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800'),
	(14, 15, 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=800'),
	(15, 16, 'https://images.unsplash.com/photo-1584647929572-4d1e9d0b1a73?w=800'),
	(16, 17, 'https://images.unsplash.com/photo-1589182373726-e4f658ab50f0?w=800'),
	(17, 18, 'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?w=800'),
	(18, 19, 'https://images.unsplash.com/photo-1504198322253-cfa87a0ff25f?w=800'),
	(19, 20, 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=800'),
	(20, 21, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800'),
	(21, 22, 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800'),
	(22, 23, 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800'),
	(23, 24, 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800'),
	(24, 25, 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=800');

-- Dumping structure for table triptest.packagerestaurant
DROP TABLE IF EXISTS `packagerestaurant`;
CREATE TABLE IF NOT EXISTS `packagerestaurant` (
  `PackageID` int(10) unsigned NOT NULL,
  `RestaurantID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`PackageID`,`RestaurantID`),
  KEY `fk_pkgrest_rest` (`RestaurantID`),
  CONSTRAINT `fk_pkgrest_pkg` FOREIGN KEY (`PackageID`) REFERENCES `package` (`PackageID`) ON DELETE CASCADE,
  CONSTRAINT `fk_pkgrest_rest` FOREIGN KEY (`RestaurantID`) REFERENCES `restaurant` (`RestaurantID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.packagerestaurant: ~33 rows (approximately)
INSERT INTO `packagerestaurant` (`PackageID`, `RestaurantID`) VALUES
	(1, 1),
	(1, 3),
	(2, 2),
	(3, 8),
	(4, 7),
	(5, 5),
	(6, 2),
	(7, 4),
	(7, 12),
	(8, 9),
	(9, 10),
	(10, 6),
	(11, 1),
	(11, 11),
	(12, 1),
	(12, 5),
	(14, 3),
	(14, 13),
	(15, 1),
	(15, 15),
	(16, 6),
	(16, 21),
	(17, 16),
	(18, 4),
	(18, 12),
	(19, 4),
	(19, 17),
	(20, 17),
	(21, 20),
	(22, 19),
	(23, 18),
	(24, 2),
	(25, 14);

-- Dumping structure for table triptest.packagereview
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.packagereview: ~24 rows (approximately)
INSERT INTO `packagereview` (`ReviewID`, `TravellerID`, `PackageID`, `Rating`, `Comment`, `CreatedAt`) VALUES
	(2, 1, 1, 5, 'Absolutely incredible! Table Mountain at sunset was breathtaking and the hotel was world-class.', '2026-06-23 10:00:00'),
	(3, 2, 2, 5, 'The safari exceeded every expectation. Saw all Big Five on day two. The lodge food was outstanding.', '2026-07-07 09:30:00'),
	(4, 3, 3, 4, 'Paris was magical. A small hiccup with airport transfers but the itinerary itself was flawless.', '2026-09-16 14:00:00'),
	(5, 4, 4, 5, 'The Bali retreat was transformative. The yoga instructors were exceptional and the villa was stunning.', '2026-08-29 11:00:00'),
	(6, 5, 5, 4, 'Stellenbosch wine tour was superb. Would have loved one more vineyard stop but overall fantastic.', '2026-08-16 08:00:00'),
	(7, 6, 6, 5, 'The Drakensberg hike was tough but so rewarding. Our guide Sipho was knowledgeable and motivating.', '2026-10-18 16:30:00'),
	(8, 7, 7, 4, 'Durban surprised me. The food markets alone were worth the trip. Great value for money.', '2026-07-20 12:00:00'),
	(9, 8, 8, 5, 'Witnessing the Great Migration was a life-changing moment. Worth every rand.', '2026-10-08 10:00:00'),
	(10, 9, 9, 5, 'Mauritius is paradise. The resort staff were exceptional and the snorkelling was world-class.', '2026-11-18 09:00:00'),
	(11, 10, 10, 4, 'Soweto tour was deeply moving and educational. Our guide was a former resident which made it special.', '2026-09-09 15:00:00'),
	(12, 1, 11, 5, 'Garden Route is stunning. Every stop was well timed and the lodge in George was a hidden gem.', '2026-07-22 11:00:00'),
	(13, 2, 12, 4, 'Great combo package. Cape Town and Stellenbosch work perfectly together. Highly recommend.', '2026-08-07 13:30:00'),
	(14, 12, 14, 5, 'Knysna was beyond beautiful. The lagoon cruise at sunset was the highlight of our trip.', '2026-07-16 10:00:00'),
	(15, 13, 15, 4, 'Boulders Beach alone was worth it. Table Mountain on a clear day is something else entirely.', '2026-08-08 14:00:00'),
	(16, 14, 16, 5, 'The Apartheid Museum was deeply moving. Gold Reef City gave a completely different side of Joburg.', '2026-09-05 09:30:00'),
	(17, 15, 17, 4, 'Saw lions and elephants on day one. Malaria-free is a huge plus for families with kids.', '2026-10-07 11:00:00'),
	(18, 16, 18, 5, 'Durban is underrated as a destination. The spa and the beach made this a perfect reset.', '2026-07-25 08:30:00'),
	(19, 17, 19, 4, 'Combining the mountains and the coast in one trip was a great idea. Logistics were seamless.', '2026-10-23 15:00:00'),
	(20, 18, 20, 5, 'The Southern Berg guides were exceptional. Stargazing from the cabin was unforgettable.', '2026-09-26 20:00:00'),
	(21, 19, 21, 5, 'Watched four whales from the shore. The restaurant recommendation was spot on too.', '2026-09-16 12:00:00'),
	(22, 20, 22, 5, 'Victoria Falls is humbling. The Zambezi sunset cruise sealed it as my best trip ever.', '2026-08-21 19:00:00'),
	(23, 21, 23, 4, 'Stone Town was a cultural revelation. The beach days were blissfully relaxed.', '2026-11-09 11:30:00'),
	(24, 22, 24, 5, 'The Kalahari night sky blew my mind. No light pollution, infinite stars. Truly special.', '2026-07-30 21:00:00'),
	(25, 23, 25, 4, 'The elephants came within metres of our vehicle. Addo is extraordinary and so close to PE.', '2026-08-14 16:00:00');

-- Dumping structure for table triptest.restaurant
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.restaurant: ~21 rows (approximately)
INSERT INTO `restaurant` (`RestaurantID`, `Name`, `Cuisine`, `PriceRange`, `ContactNumber`, `Street`, `City`, `Country`) VALUES
	(1, 'The Mariner\'s Wharf', 'Seafood', '$$$', '+27214385865', '1 Harbour Road', 'Cape Town', 'South Africa'),
	(2, 'Braai & Biltong House', 'South African', '$$', '+27118803344', '10 Commissioner Street', 'Johannesburg', 'South Africa'),
	(3, 'Harbour House', 'Seafood', '$$$$', '+27214385550', 'V&A Waterfront', 'Cape Town', 'South Africa'),
	(4, 'Moyo Durban', 'African Fusion', '$$$', '+27313124099', '6 Durban Club Place', 'Durban', 'South Africa'),
	(5, '96 Winery Road', 'Contemporary', '$$$', '+27218428646', '96 Winery Road, Zandvliet', 'Stellenbosch', 'South Africa'),
	(6, 'Sakhumzi Restaurant', 'Traditional African', '$$', '+27115368076', '6980 Vilakazi Street', 'Soweto', 'South Africa'),
	(7, 'Locavore Nusantara', 'Indonesian', '$$$', '+62361977733', 'Jalan Dewi Sita', 'Ubud', 'Indonesia'),
	(8, 'Le Jules Verne', 'French Fine Dining', '$$$$', '+33145556183', 'Avenue Gustave Eiffel', 'Paris', 'France'),
	(9, 'Carnivore Restaurant', 'African Game Meats', '$$$', '+254205605', 'Langata Road', 'Nairobi', 'Kenya'),
	(10, 'La Palmeraie', 'Creole Seafood', '$$$', '+23052694929', 'Royal Road', 'Grand Baie', 'Mauritius'),
	(11, 'Loading Bay Café', 'Café', '$', '+27214191108', '30 Hudson Street', 'Cape Town', 'South Africa'),
	(12, 'Nambitha', 'Zulu Traditional', '$$', '+27315613260', '150 Old Fort Road', 'Durban', 'South Africa'),
	(13, 'Ile de Pain', 'Artisan Bakery Café', '$', '+27445825706', '3 Waterfront Drive', 'Knysna', 'South Africa'),
	(14, 'Cattle Baron Addo', 'South African Grill', '$$', '+27426400678', '1 Addo Main Road', 'Addo', 'South Africa'),
	(15, 'Bungalow Restaurant', 'Mediterranean', '$$$', '+27214380567', '3 Victoria Road', 'Cape Town', 'South Africa'),
	(16, 'Sun City Grill', 'International', '$$$', '+27147571000', 'Palace of the Lost City', 'Rustenburg', 'South Africa'),
	(17, 'Sani Pass Hotel Dining', 'Traditional SA', '$$', '+27364381105', 'Sani Pass Road', 'Himeville', 'South Africa'),
	(18, 'The Rock Restaurant', 'Swahili Seafood', '$$$', '+255777123456', 'Paje Beach', 'Zanzibar', 'Tanzania'),
	(19, 'The Boma Dinner & Drum', 'African Buffet', '$$$', '+263132322', 'Victoria Falls Hotel', 'Victoria Falls', 'Zimbabwe'),
	(20, 'Burgundy Restaurant', 'Contemporary SA', '$$$', '+27284120878', '16 Harbour Road', 'Hermanus', 'South Africa'),
	(21, 'Butcher Shop & Grill SA', 'Steakhouse', '$$$', '+27118840440', 'Nelson Mandela Square', 'Johannesburg', 'South Africa');

-- Dumping structure for table triptest.restaurantimage
DROP TABLE IF EXISTS `restaurantimage`;
CREATE TABLE IF NOT EXISTS `restaurantimage` (
  `ImageID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RestaurantID` int(10) unsigned NOT NULL,
  `ImageURL` varchar(500) NOT NULL,
  PRIMARY KEY (`ImageID`),
  KEY `fk_restimage_rest` (`RestaurantID`),
  CONSTRAINT `fk_restimage_rest` FOREIGN KEY (`RestaurantID`) REFERENCES `restaurant` (`RestaurantID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dumping data for table triptest.restaurantimage: ~21 rows (approximately)
INSERT INTO `restaurantimage` (`ImageID`, `RestaurantID`, `ImageURL`) VALUES
	(1, 1, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'),
	(2, 2, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800'),
	(3, 3, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'),
	(4, 4, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800'),
	(5, 5, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'),
	(6, 6, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800'),
	(7, 7, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'),
	(8, 8, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'),
	(9, 9, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800'),
	(10, 10, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800'),
	(11, 11, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'),
	(12, 12, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800'),
	(13, 13, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'),
	(14, 14, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800'),
	(15, 15, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'),
	(16, 16, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800'),
	(17, 17, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'),
	(18, 18, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800'),
	(19, 19, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800'),
	(20, 20, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'),
	(21, 21, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800');

-- Dumping structure for table triptest.traveller
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table triptest.traveller: ~24 rows (approximately)
INSERT INTO `traveller` (`TravellerID`, `FirstName`, `Surname`, `Email`, `Password`, `Phone`, `Nationality`, `DateOfBirth`, `JoinDate`) VALUES
	(2, 'Alice', 'Dlamini', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0821112222', 'South African', '1995-03-14', '2024-05-01'),
	(3, 'Brendan', 'Nkosi', 'brendan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0833334444', 'South African', '1998-07-22', '2024-05-03'),
	(4, 'Carla', 'Visser', 'carla@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0845556666', 'South African', '1990-11-05', '2024-05-10'),
	(5, 'David', 'Mokoena', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0857778888', 'South African', '2000-01-30', '2024-06-01'),
	(6, 'Elena', 'Botha', 'elena@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0869990000', 'South African', '1993-09-18', '2024-06-15'),
	(7, 'Fatima', 'Osman', 'fatima@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0871231234', 'South African', '1997-04-25', '2024-07-01'),
	(8, 'George', 'Pretorius', 'george@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0824564567', 'South African', '1985-12-12', '2024-07-20'),
	(9, 'Hannah', 'van der Berg', 'hannah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0837897890', 'South African', '2001-06-08', '2024-08-05'),
	(10, 'Ivan', 'Sithole', 'ivan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0840120123', 'South African', '1992-02-17', '2024-08-22'),
	(11, 'Julia', 'Hendricks', 'julia@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0853453456', 'South African', '1999-10-03', '2024-09-01'),
	(12, 'Liam', 'Fourie', 'liam@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0861112233', 'South African', '1994-08-14', '2024-09-10'),
	(13, 'Naledi', 'Khumalo', 'naledi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0872223344', 'South African', '2002-03-29', '2024-09-15'),
	(14, 'Pieter', 'van Wyk', 'pieter@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0843334455', 'South African', '1988-11-07', '2024-09-20'),
	(15, 'Simone', 'Jacobs', 'simone@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0854445566', 'South African', '1996-06-22', '2024-10-01'),
	(16, 'Themba', 'Zwane', 'themba@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0865556677', 'South African', '1991-01-15', '2024-10-05'),
	(17, 'Roxy', 'Daniels', 'roxy@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0876667788', 'South African', '2000-09-03', '2024-10-12'),
	(18, 'Sipho', 'Mthembu', 'sipho@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0887778899', 'South African', '1987-04-18', '2024-11-01'),
	(19, 'Annika', 'du Plessis', 'annika@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0898889900', 'South African', '1999-07-11', '2024-11-08'),
	(20, 'Marcus', 'Dlamin', 'marcus@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0801234567', 'South African', '1993-12-25', '2024-11-15'),
	(21, 'Liesl', 'Cronje', 'liesl@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0812345678', 'South African', '1997-05-30', '2024-12-01'),
	(22, 'Wandile', 'Ngcobo', 'wandile@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0823456789', 'South African', '1985-02-14', '2025-01-10'),
	(23, 'Chantal', 'Meyer', 'chantal@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0834567890', 'South African', '2001-10-08', '2025-01-20'),
	(24, 'Deon', 'Lombard', 'deon@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0845678901', 'South African', '1990-03-19', '2025-02-05'),
	(25, 'Zanele', 'Moyo', 'zanele@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0856789012', 'South African', '1998-08-27', '2025-02-18');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
