

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nickname` varchar(30) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  `skills` varchar(100) DEFAULT NULL,
  `membership` varchar(20) DEFAULT NULL,
  `lastvisit` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `index_email` (`email`) USING BTREE,
  ADD UNIQUE KEY `index_fullname` (`fullname`) USING BTREE,
  ADD UNIQUE KEY `index_nickname` (`nickname`) USING BTREE;
--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;


