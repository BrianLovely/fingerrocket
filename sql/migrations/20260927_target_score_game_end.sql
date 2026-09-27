ALTER TABLE `gamehandler`
  ADD COLUMN `targetScore` int(11) NOT NULL DEFAULT 1000,
  ADD COLUMN `winnerId` varchar(25) DEFAULT NULL,
  ADD COLUMN `gameStatus` varchar(20) NOT NULL DEFAULT 'active';

UPDATE `gamehandler` AS g
LEFT JOIN `fortress` AS f1 ON g.f1 = f1.id
LEFT JOIN `fortress` AS f2 ON g.f2 = f2.id
SET g.targetScore = GREATEST(COALESCE(f1.points, 0), COALESCE(f2.points, 0)) + 1000
WHERE g.gameStatus = 'active';