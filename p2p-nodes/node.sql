CREATE TABLE IF NOT EXISTS `node` (
  `id` int(11) NOT NULL,
  `ip` varchar(128) NOT NULL,
  `country` varchar(128) NOT NULL,
  `hash` varchar(128) NOT NULL,
  `uptime` varchar(128) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `node`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `ip` (`ip`);

ALTER TABLE `node`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
