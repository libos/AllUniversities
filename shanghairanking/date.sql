DROP TABLE IF EXISTS `fu`.`country`;
DROP TABLE IF EXISTS `fu`.`shanghairanking`;
DROP TABLE IF EXISTS `fu`.`countryranking`;
DROP TABLE IF EXISTS `fu`.`school`;

-- INSERT INTO `fu`.`country` (id,name,region,flag,created)
-- VALUES (NULL,"{$_country_name}","{$_country_region}","{$_flag}","now()")
create table IF NOT EXISTS `fu`.`country`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`name` varchar(64) NOT NULL,
	`region` varchar(8) NOT NULL,
	`flag` varchar(255) NOT NULL,
	`created` datetime NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- INSERT INTO `fu`.`shanghairanking` (id,type,year,world_rank,school_name,school_engname,school_id,country_name,country_id,country_rank,sum_score,alumni_score,award_score,hici_score,ns_score,pub_score,pcb_score,created)
-- 	VALUES (NULL,"$url_type[$index]","2013",$_world_rank)
create table IF NOT EXISTS `fu`.`shanghairanking`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`type` enum('academic','sci','ei','life','med','soc','math','phy','chem','computer','eb'),
	`year` int(10),
	`world_rank` varchar(16),
	`school_name` varchar(64),
	`school_engname` varchar(128),
	`school_id` int(11) unsigned,
	`country_name` varchar(64),
	`country_id` int(11) unsigned,
	`country_rank` varchar(16),
	`sum_score` decimal(4,2),
	`alumni_score` decimal(4,2),
	`award_score` decimal(4,2),
	`hici_score` decimal(4,2),
	`ns_score` decimal(4,2),
	`pub_score` decimal(4,2),
	`pcb_score` decimal(4,2),
	`created` datetime NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- INSERT INTO `fu`.`countryranking` (id,country_name,country_id,countryrank,school_name,school_engname,school_id,world_rank,created)
-- VALUES (NULL,"{$_country_name}","{$_country_id}","{$_country_rank}","{$_school_name}","{$_school_engname}","{$_school_id}","{$_world_rank}",now());
create table IF NOT EXISTS `fu`.`countryranking`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`country_name` varchar(16),
	`country_id` int(11) unsigned,
	`countryrank` varchar(16),
	`school_name` varchar(64),
	`school_engname` varchar(128),
	`school_id` int(11) unsigned,
	`world_rank` varchar(16),
	`created` datetime NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- INSERT INTO `fu`.`school` (id,uuid,name,eng_name,country_id,country_region,country_name,buid_at,addr,addr_eng,website,stu_num,depart,major,xuefei,basic_desc,baidu_info,nick_name,motto,badgeicon,created)
-- VALUES (NULL,NULL);
create table IF NOT EXISTS `fu`.`school`(
	`id` int(11) unsigned primary key AUTO_INCREMENT UNIQUE,
	`uuid` varchar(64),
	`name` varchar(64),
	`eng_name` varchar(128),
	`country_id` int(11) unsigned,
	`country_region` varchar(8),
	`country_name` varchar(64),
	`buid_at` varchar(8),
	`addr` varchar(255),
	`addr_eng` varchar(255),
	`website` varchar(255),
	`stu_num` varchar(255),
	`depart` text,
	`major` text,
	`xuefei` varchar(255),
	`basic_desc` text,
	`baidu_info` text,
	`nick_name` varchar(64),
	`motto` varchar(255),
	`badgeicon` varchar(255),
	`created` datetime NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8;