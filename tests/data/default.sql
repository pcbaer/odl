TRUNCATE TABLE `gammascout`;
TRUNCATE TABLE `measurement`;
TRUNCATE TABLE `station`;

INSERT INTO `station` (`id`, `odl_id`, `zip`, `city`, `created_at`, `updated_at`, `altitude`, `latitude`, `longitude`, `status`, `last`) VALUES
(1, '064110003', '64295', 'Darmstadt', '2020-04-02 22:01:26', '2020-04-02 22:01:26', 138, 49.84, 8.59, 1, 0.086);

INSERT INTO `measurement` (`station_id`, `time`, `dosage`, `rain`, `abnormality`) VALUES
(1, '2020-03-25 00:00:00', 0.088, 0.0, 0.0),
(1, '2020-03-25 01:00:00', 0.087, 0.0, 0.0),
(1, '2020-03-25 02:00:00', 0.086, 0.0, 0.0);

INSERT INTO `gammascout` (`time`, `dosage`) VALUES
('2020-03-25 00:00:00', 0.10892),
('2020-03-25 01:00:00', 0.10403),
('2020-03-25 02:00:00', 0.10556);
