CREATE TABLE `ccontent` (
  `ccid` varchar(60) NOT NULL,
  `version` varchar(60) NOT NULL,
  `owner_id` varchar(45) NOT NULL,
  `owner_reg_date` timestamp NOT NULL,
  `status` varchar(10) NOT NULL COMMENT '컨텐츠의 상태..\n  - 수집완료     -  00\n  - 서비스 가능 -  01\n  - 서비스 중    -  02',
  `reg_date` timestamp NOT NULL,
  PRIMARY KEY (`ccid`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `ccontent_file` (
  `content_file_seq` int(11) NOT NULL AUTO_INCREMENT,
  `ccid` varchar(60) NOT NULL,
  `version` varchar(60) NOT NULL,
  `content_path` varchar(200) NOT NULL,
  `content_type` varchar(30) NOT NULL,
  `content_size` bigint(20) NOT NULL,
  `content_class` varchar(20) NOT NULL COMMENT 'basic-기본컨텐츠\next - 확장컨텐츠',
  PRIMARY KEY (`content_file_seq`),
  KEY `cfiles_fk_1` (`ccid`,`version`),
  CONSTRAINT `cfiles_fk_1` FOREIGN KEY (`ccid`, `version`) REFERENCES `ccontent` (`ccid`, `version`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1353 DEFAULT CHARSET=utf8;


CREATE TABLE `log_register` (
  `log_reg_id` int(11) NOT NULL AUTO_INCREMENT,
  `ccid` varchar(60) NOT NULL,
  `version` varchar(60) NOT NULL,
  `category1` varchar(50) DEFAULT NULL,
  `category2` varchar(50) DEFAULT NULL,
  `job_proc` varchar(10) NOT NULL,
  `job_proc_status` varchar(10) NOT NULL,
  `job_assign_date` timestamp NULL DEFAULT NULL,
  `job_finish_date` timestamp NULL DEFAULT NULL,
  `job_progress` text,
  `reg_date` timestamp NOT NULL,
  PRIMARY KEY (`log_reg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `log_search` (
  `log_search_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_ip` varchar(20) NOT NULL,
  `client_agent` varchar(200) NOT NULL,
  `search_keyword` varchar(100) NOT NULL,
  `search_api` varchar(20) NOT NULL,
  `result_count` int(11) NOT NULL,
  `search_date` timestamp NOT NULL,
  PRIMARY KEY (`log_search_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `metadata` (
  `meta_seq` int(11) NOT NULL AUTO_INCREMENT,
  `ccid` varchar(60) NOT NULL,
  `version` varchar(60) NOT NULL,
  `meta_path` varchar(200) NOT NULL,
  `metadata_original` text NOT NULL,
  `metadata_service` text,
  `meta_type` varchar(20) NOT NULL COMMENT '메타데이터 타입\nbasic-movie.v1',
  `meta_class` varchar(20) NOT NULL COMMENT 'basic-기본\next - 확장',
  `title` varchar(200) NOT NULL,
  `content_type` varchar(20) NOT NULL,
  `last_modify` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`meta_seq`),
  KEY `ccid` (`ccid`,`version`),
  CONSTRAINT `metadata_ibfk_1` FOREIGN KEY (`ccid`, `version`) REFERENCES `ccontent` (`ccid`, `version`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=551 DEFAULT CHARSET=utf8;


CREATE TABLE `metadata_file` (
  `meta_file_seq` int(11) NOT NULL AUTO_INCREMENT,
  `ccid` varchar(60) NOT NULL,
  `version` varchar(60) NOT NULL,
  `meta_path` varchar(200) NOT NULL,
  `meta_type` varchar(30) NOT NULL,
  `meta_size` bigint(20) NOT NULL,
  `meta_class` varchar(20) NOT NULL COMMENT 'basic-기본\next - 확장',
  `file_status` varchar(5) NOT NULL COMMENT '추기상태.        - INIT\n추가된 파일인 - ADD\n삭제된 파일.   - DEL\n',
  PRIMARY KEY (`meta_file_seq`),
  KEY `ccid` (`ccid`,`version`),
  CONSTRAINT `metadata_file_ibfk_1` FOREIGN KEY (`ccid`, `version`) REFERENCES `ccontent` (`ccid`, `version`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1663 DEFAULT CHARSET=utf8;



CREATE TABLE `metadata_hist` (
  `meta_hist_id` int(11) NOT NULL AUTO_INCREMENT,
  `meta_seq` int(11) NOT NULL,
  `metadata` text NOT NULL,
  `update_date` timestamp NOT NULL,
  PRIMARY KEY (`meta_hist_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;


CREATE TABLE `register_queue` (
  `ccid` varchar(60) NOT NULL,
  `version` varchar(60) NOT NULL,
  `category1` varchar(50) DEFAULT NULL,
  `category2` varchar(50) DEFAULT NULL,
  `owner_id` varchar(45) NOT NULL,
  `owner_reg_date` timestamp NOT NULL,
  `job_proc` varchar(10) NOT NULL COMMENT 'S       —> Standby\nD    —> 다운로드중\nDD —> 다운로드완료\nI       —> 인덱싱중\nID  —> 인덱싱 완료\n\n',
  `job_proc_status` varchar(10) NOT NULL COMMENT 'S - Success\nE - Error',
  `job_download_start` timestamp NULL DEFAULT NULL,
  `job_download_end` timestamp NULL DEFAULT NULL,
  `job_index_start` timestamp NULL DEFAULT NULL,
  `job_index_end` timestamp NULL DEFAULT NULL,
  `job_progress` text,
  `reg_date` timestamp NOT NULL,
  PRIMARY KEY (`ccid`,`version`),
  KEY `queue_idx` (`job_proc`,`reg_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `user` (
  `user_num` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(45) NOT NULL,
  `password` varchar(200) NOT NULL,
  `reg_date` timestamp NOT NULL,
  PRIMARY KEY (`user_num`),
  UNIQUE KEY `user_id_UNIQUE` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
