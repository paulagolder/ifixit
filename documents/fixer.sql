

CREATE TABLE `fixer` (
  `fixerid` int(11) NOT NULL,
  `nickname` varchar(30) NOT NULL,
  `fullname` varchar(40) NOT NULL,
  `email` varchar(60) NOT NULL,
  `skills` varchar(100) DEFAULT NULL,
  `password` varchar(80) DEFAULT NULL,
  `rolestr` varchar(50) DEFAULT NULL,
  `membership` varchar(20) DEFAULT NULL,
  `lastvisit` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fixer`
--
ALTER TABLE `fixer`
  ADD PRIMARY KEY (`fixerid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fixer`
--
ALTER TABLE `fixer`
  MODIFY `fixerid` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
