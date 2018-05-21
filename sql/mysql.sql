CREATE TABLE `tad_sitemap` (
  `mid` smallint(6) unsigned NOT NULL COMMENT '模組編號',
  `name` varchar(255) NOT NULL default '' COMMENT '項目名稱',
  `url` varchar(150) NOT NULL default '' COMMENT '連結位置',
  `description` varchar(255) NOT NULL default '' COMMENT '相關說明',
  `last_update` datetime NOT NULL COMMENT '最後更新',
  `sort` tinyint(3) unsigned NOT NULL default '0' COMMENT '排序',
PRIMARY KEY  (`mid`,`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

