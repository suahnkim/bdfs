package tom.mediabc.search.vo.dao;

import java.util.Date;

import lombok.Data;

@Data
public class RegisterQueueVO {

	
	public static final String JOB_STANDBY       = "S";
	public static final String JOB_DOWNLOAD      = "D";
	public static final String JOB_DOWNLOAD_DONE = "DD";
	public static final String JOB_INDEXING      = "I";
	public static final String JOB_INDEXING_DONE = "ID";
	
	public static final String STATUS_SUCCESS = "S";
	public static final String STATUS_ERROR = "E";
	
	
	public static final String JOB_TYPE_INSERT = "i";
	public static final String JOB_TYPE_MODIFY = "m";
	public static final String JOB_TYPE_DELETE = "d";
	
	
	private Integer queueId;
	private String  ccid;
	private String  version;
	private String  category1;
	private String  category2;
	private String  ownerId;
	private Date    ownerRegDate;
	private String  jobType;
	
	
	private String jobProc;
	private String jobProcStatus;
	private Date   jobDownloadStart;
	private Date   jobDownloadEnd;
	private Date   jobIndexStart;
	private Date   jobIndexEnd;
	private String jobProgress;
	private Date   regDate;
}
