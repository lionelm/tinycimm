--
-- Table structure for table `asset`
--

DROP TABLE IF EXISTS `asset`;
CREATE TABLE IF NOT EXISTS `asset` (
	`id` int(5) NOT NULL auto_increment,
	`user_id` smallint(4) NOT NULL default '1',
	`folder_id` smallint(4) NOT NULL,
	`name` varchar(255) collate latin1_general_ci NOT NULL,
	`filename` varchar(255) collate latin1_general_ci NOT NULL,
	`description` varchar(255) collate latin1_general_ci NOT NULL,
	`extension` varchar(5) collate latin1_general_ci NOT NULL,
	`mimetype` varchar(255) collate latin1_general_ci NOT NULL,
	`dateadded` timestamp NOT NULL default CURRENT_TIMESTAMP,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;


--
-- Table structure for table `asset_folder`
--

DROP TABLE IF EXISTS `asset_folder`;
CREATE TABLE IF NOT EXISTS `asset_folder` (
	`id` int(3) NOT NULL auto_increment,
	`user_id` smallint(3) NOT NULL default '1',
	`name` varchar(255) collate latin1_general_ci NOT NULL,
	`dateadded` timestamp NOT NULL default CURRENT_TIMESTAMP,
	UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;
