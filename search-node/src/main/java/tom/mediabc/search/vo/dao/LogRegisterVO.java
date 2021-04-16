package tom.mediabc.search.vo.dao;

import java.util.Date;

import lombok.Data;

@Data
public class LogRegisterVO {

	
	private int    logRegId;
	private String ccid;
	private String version;
	private String category1;
	private String category2;
	private String jobProc;
	private String jobProcStatus;
	private Date   jobAssignDate;
	private Date   jobFinishDate;
	private String jobProgress;
	private Date   regDate;
}
