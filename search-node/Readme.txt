검색서버

1. 개발 & 실행 환경
	Java : 1.8.0_261
	IDE  : Spring Tool Suite 3 3.9.14.RELEASE
	Spring : 4.1.5.RELEASE
	Apache Tomcat : 9.0.5
	ElasticSearch : 7.2.0 (with nori plugin)
	Database : Mysql 5.7.32
	
	
    IPFS & chainlink & listener
    
    
2.Db Schema
   
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
  `reg_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`log_reg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `log_search` (
  `log_search_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_ip` varchar(20) NOT NULL,
  `client_agent` varchar(200) NOT NULL,
  `search_keyword` varchar(100) NOT NULL,
  `search_api` varchar(20) NOT NULL,
  `result_count` int(11) NOT NULL,
  `search_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_search_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

   
CREATE TABLE `ccontent` (
  `ccid` varchar(60) NOT NULL,
  `version` varchar(60) NOT NULL,
  `owner_id` varchar(45) NOT NULL,
  `owner_reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(10) NOT NULL COMMENT '컨텐츠의 상태..\n  - 수집완료     -  00\n  - 서비스 가능 -  01\n  - 서비스 중    -  02',
  `reg_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `metadata_hist` (
  `meta_hist_id` int(11) NOT NULL AUTO_INCREMENT,
  `meta_seq` int(11) NOT NULL,
  `metadata` text NOT NULL,
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`meta_hist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `register_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `ccid` varchar(60) NOT NULL,
  `version` varchar(60) NOT NULL,
  `category1` varchar(50) DEFAULT NULL,
  `category2` varchar(50) DEFAULT NULL,
  `owner_id` varchar(45) NOT NULL,
  `owner_reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `job_type` varchar(45) DEFAULT NULL,
  `job_proc` varchar(10) NOT NULL COMMENT 'S       —> Standby\nD    —> 다운로드중\nDD —> 다운로드완료\nI       —> 인덱싱중\nID  —> 인덱싱 완료\n\n',
  `job_proc_status` varchar(10) NOT NULL COMMENT 'S - Success\nE - Error',
  `job_download_start` timestamp NULL DEFAULT NULL,
  `job_download_end` timestamp NULL DEFAULT NULL,
  `job_index_start` timestamp NULL DEFAULT NULL,
  `job_index_end` timestamp NULL DEFAULT NULL,
  `job_progress` text,
  `reg_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`queue_id`),
  KEY `queue_idx` (`job_proc`,`reg_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE `user` (
  `user_num` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(45) NOT NULL,
  `password` varchar(200) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_num`),
  UNIQUE KEY `user_id_UNIQUE` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



3. ElasticSearch Schema

curl -H 'Content-Type: application/json' -XPUT 'http://localhost:9200/ccontents_service' -d '
{
"settings": {
  "index": {
    "analysis": {
      "normalizer": {
        "my_normalizer": {
          "type": "custom",
          "char_filter": [],
          "filter": ["lowercase", "asciifolding"]
        }
      }
    }
  }
},
  "mappings" : {
      "properties" : {
      	  "ccid" :                    { "type" : "keyword" },
          "version" :                 { "type" : "keyword" },
          "status" :                  { "type" : "keyword" },
          "owner_id" :                { "type" : "keyword" },
          "owner_reg_date" :          { "type" : "date", "format": "yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"},
          "meta_container" : {
          	"properties" : {
				  "meta_seq" :                { "type" : "long" },
		          "content-type" :            { "type" : "keyword"},
          		  "meta-type" :               { "type" : "keyword"},
          		  "meta_class" :              { "type" : "keyword"},
          		  "target" :                  { "type" : "keyword"},
          		  "last_modify" :             { "type" : "date", "format": "yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"},
          		  "metadata" : {
          		  	  "properties" : {
	          		  	  "vender_id" :               { "type" : "keyword" },
				          "theatrical_release_date" : { "type" : "keyword" },
				          "rating" :                  { "type" : "keyword" },
						  "country" :                 { "type" : "keyword", "normalizer": "my_normalizer"},
				          "original_spoken_locale" :  { "type" : "keyword", "normalizer": "my_normalizer"},
						  "genre" :                   { "type" : "keyword", "normalizer": "my_normalizer"},
						  
				          "production_company" :      { "type" : "text", "analyzer": "nori"},
				          "copyright_cline" :         { "type" : "text", "analyzer": "nori"},
				          "title" :                   {
				          		"type" : "text", 
				          		"fielddata" : true,
				          		"fields": {
						            "korean_field": {
						              "analyzer": "nori",
						              "type": "text"
						            },
						            "english_field": {
						              "analyzer": "english",
						              "type": "text"
						            }
				        		}
				          },
				          "synopsis" :                { 
				        		"type" : "text",
				        		"fields": {
						            "korean_field": {
						              "analyzer": "nori",
						              "type": "text"
						            },
						            "english_field": {
						              "analyzer": "english",
						              "type": "text"
						            }
				        		}
				          },
				
				          "cast" : {
				        	"properties" : {
				        		"artist_id" :         { "type" : "keyword" },
				        		"name" :              { "type" : "text", "analyzer": "nori"},
				        		"cast_name" :         { "type" : "text", "analyzer": "nori"}
				        	}
				          },
				          "crew" : {
				        	"properties" : {
				        		"artist_id" :         { "type" : "keyword" },
				        		"name" :              { "type" : "text", "analyzer": "nori"},
				        		"role" :              { "type" : "text", "analyzer": "nori"}
				        	}
				          },
				          "artwork" : {
				        	"properties" : {
				        		"title" :                  { "type" : "keyword" },
				        		"file_name" :              { "type" : "keyword"},
				        		"file_size" :              { "type" : "long"},
				        		"rep" :                    { "type" : "keyword"},
				        		"height" :                 { "type" : "long"},
				        		"width" :                  { "type" : "long"},
				        		"format" :                 { "type" : "keyword"}
				        	}
				          }
          		  	  }
          		  }
          	}
          }
      }
  }
}'

curl -H 'Content-Type: application/json' -XPUT 'http://localhost:9200/ccontents_original' -d '
{
"settings": {
  "index": {
    "analysis": {
      "normalizer": {
        "my_normalizer": {
          "type": "custom",
          "char_filter": [],
          "filter": ["lowercase", "asciifolding"]
        }
      }
    }
  }
},
  "mappings" : {
      "properties" : {
      	  "ccid" :                    { "type" : "keyword" },
          "version" :                 { "type" : "keyword" },
          "status" :                  { "type" : "keyword" },
          "owner_id" :                { "type" : "keyword" },
          "owner_reg_date" :          { "type" : "date", "format": "yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"},
          "meta_container" : {
          	"properties" : {
				  "meta_seq" :                { "type" : "long" },
		          "content-type" :            { "type" : "keyword"},
          		  "meta-type" :               { "type" : "keyword"},
          		  "meta_class" :              { "type" : "keyword"},
          		  "target" :                  { "type" : "keyword"},
          		  "last_modify" :             { "type" : "date", "format": "yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"},
          		  "metadata" : {
          		  	  "properties" : {
	          		  	  "vender_id" :               { "type" : "keyword" },
				          "theatrical_release_date" : { "type" : "keyword" },
				          "rating" :                  { "type" : "keyword" },
						  "country" :                 { "type" : "keyword", "normalizer": "my_normalizer"},
				          "original_spoken_locale" :  { "type" : "keyword", "normalizer": "my_normalizer"},
						  "genre" :                   { "type" : "keyword", "normalizer": "my_normalizer"},
						  
				          "production_company" :      { "type" : "text", "analyzer": "nori"},
				          "copyright_cline" :         { "type" : "text", "analyzer": "nori"},
				          "title" :                   {
				          		"type" : "text", 
				          		"fielddata" : true,
				          		"fields": {
						            "korean_field": {
						              "analyzer": "nori",
						              "type": "text"
						            },
						            "english_field": {
						              "analyzer": "english",
						              "type": "text"
						            }
				        		}
				          },
				          "synopsis" :                { 
				        		"type" : "text",
				        		"fields": {
						            "korean_field": {
						              "analyzer": "nori",
						              "type": "text"
						            },
						            "english_field": {
						              "analyzer": "english",
						              "type": "text"
						            }
				        		}
				          },
				
				          "cast" : {
				        	"properties" : {
				        		"artist_id" :         { "type" : "keyword" },
				        		"name" :              { "type" : "text", "analyzer": "nori"},
				        		"cast_name" :         { "type" : "text", "analyzer": "nori"}
				        	}
				          },
				          "crew" : {
				        	"properties" : {
				        		"artist_id" :         { "type" : "keyword" },
				        		"name" :              { "type" : "text", "analyzer": "nori"},
				        		"role" :              { "type" : "text", "analyzer": "nori"}
				        	}
				          },
				          "artwork" : {
				        	"properties" : {
				        		"title" :                  { "type" : "keyword" },
				        		"file_name" :              { "type" : "keyword"},
				        		"file_size" :              { "type" : "long"},
				        		"rep" :                    { "type" : "keyword"},
				        		"height" :                 { "type" : "long"},
				        		"width" :                  { "type" : "long"},
				        		"format" :                 { "type" : "keyword"}
				        	}
				          }
          		  	  }
          		  }
          	}
          }
      }
  }
}'



4. Configuration 설정

설정 파일은 src/main/webapp/WEB-INF/config/config.xml 이며 내용은 아래롸 같다.

<?xml version="1.0" encoding="UTF-8"?>
<tomahawk>
	<serverName>MediaBlockChain SearchServer</serverName>
	<extras>
		<!-- 인덱싱 작업 수행시 동시작업 쓰레드 -->
		<field name="batch.index.thread" type="integer">5</field>
		
		<!-- 다운로드 작업 수행시 동시작업 쓰레드 -->
		<field name="batch.download.thread" type="integer">5</field>
		
		
		
		<!-- elasticsearch 접속 주소 -->
		<field name="elasticsearch.host"   type="string">localhost</field>
		<field name="elasticsearch.port"   type="integer">9200</field>
		<field name="elasticsearch.schema" type="string">http</field>
		
		
		<!-- 복합컨텐츠 다운로드 경로-->
		<field name="cc.basedir" type="string">/home/user/datastore/cc</field>
		
		<!-- IPFS 경로-->
		<field name="ipfs.path" type="string">/usr/local/go/bin/ipfs</field>
		
		
		
		<!-- 다운로드 완료 노티 주소 (체인링크)-->
		<field name="download_complate_url"  type="string">http://localhost:55441/product/setSearchNode</field>
	</extras>
</tomahawk>


데이터 베이스 설정은 src/main/webapp/WEB-INF/spring/root-context.xml 이며 애래 내용을 수정한다.,

아래의 host, user, password를 수정한다.	
	
	<bean id="dataSource" class="org.apache.commons.dbcp.BasicDataSource" destroy-method="close">
		<property name="driverClassName"	value="com.mysql.jdbc.Driver" />	
		<property name="url"            	value="jdbc:mysql://host:3306/mediabc_search?characterEncoding=utf-8&amp;useUnicode=true&amp;zeroDateTimeBehavior=convertToNull&amp;allowMultiQueries=true" />
		<property name="username"       	value="user" />
		<property name="password"       	value="password" />
		
		<property name="defaultAutoCommit" 	value="true" />
		<property name="maxActive" 			value="100" />
		<property name="maxIdle" 			value="50" />
		<property name="maxWait" 			value="30000" />
		<property name="validationQuery" 	value="SELECT 1 FROM DUAL" />
		<property name="connectionInitSqls" value="SET time_zone = '+09:00'"/>
	</bean>

5. Admin 계정 생성

package tom.mediabc.search.test.MakeUserInfo 클래스를 제공.
MakeUserInfo 클래스에 userId, password 를 설정후 싱핼하면 SQL 제공 


INSERT INTO user(user_id, password) VALUES('user01', 'EbSseFuhEpXxwR1m/BBtP8yyMqO7eN0wSQH1FIzDq/dWEVpQ') 


6. Build
 배포된 프로젝트를 STS에 임포트 한다.
 프로젝트 Context Menu Open -> Run As -> Maven install
 
 
 

