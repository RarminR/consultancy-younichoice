-- Create table for storing Google Calendar access tokens
CREATE TABLE IF NOT EXISTS `user_google_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `accessToken` text NOT NULL,
  `refreshToken` text,
  `expiresAt` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add Google Calendar event ID column to meetings table
ALTER TABLE `meetings` ADD COLUMN `googleEventId` varchar(255) DEFAULT NULL AFTER `meetingActivities`;
ALTER TABLE `meetings` ADD COLUMN `googleMeetLink` text DEFAULT NULL AFTER `googleEventId`;
ALTER TABLE `meetings` ADD COLUMN `googleCalendarLink` text DEFAULT NULL AFTER `googleMeetLink`;

-- Add index for better performance
CREATE INDEX `idx_google_event_id` ON `meetings` (`googleEventId`); 