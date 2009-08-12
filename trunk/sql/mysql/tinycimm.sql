-- --------------------------------------------------------

--
-- Table structure for table `asset`
--

CREATE TABLE IF NOT EXISTS `asset` (
  `id` int(5) NOT NULL auto_increment,
  `folder_id` int(5) NOT NULL,
  `name` varchar(255) collate latin1_general_ci NOT NULL,
  `filename` varchar(255) collate latin1_general_ci NOT NULL,
  `description` varchar(255) collate latin1_general_ci NOT NULL,
  `extension` varchar(5) collate latin1_general_ci NOT NULL,
  `mimetype` varchar(255) collate latin1_general_ci NOT NULL,
  `filesize` int(11) NOT NULL default '0',
  `dateadded` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=143 ;

-- --------------------------------------------------------

--
-- Table structure for table `asset_folder`
--

CREATE TABLE IF NOT EXISTS `asset_folder` (
  `id` int(5) NOT NULL auto_increment,
  `user_id` int(5) NOT NULL default '1',
  `type_id` int(11) NOT NULL,
  `name` varchar(255) collate latin1_general_ci NOT NULL,
  `dateadded` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=70 ;

-- --------------------------------------------------------

--
-- Table structure for table `type`
--

CREATE TABLE IF NOT EXISTS `type` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  `dateadded` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

INSERT INTO `type` (`id`, `name`, `dateadded`) VALUES
(1, 'image', ''),
(2, 'media', ''),
(3, 'file', '');

