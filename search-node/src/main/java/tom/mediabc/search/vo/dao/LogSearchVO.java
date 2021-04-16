package tom.mediabc.search.vo.dao;

import java.util.Date;

import lombok.Data;

@Data
public class LogSearchVO {

	private int    logSearchId;
	private String clientIp;
	private String clientAgent;
	private String searchKeyword;
	private String searchApi;
	private int    resultCount;
	private Date   searchDate;

}