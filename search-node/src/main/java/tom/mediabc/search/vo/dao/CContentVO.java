package tom.mediabc.search.vo.dao;

import java.util.Date;
import java.util.HashMap;

import lombok.Data;

@Data
public class CContentVO {

	public CContentVO() {
	}
	public CContentVO(String ccid, String version) {
		this.ccid = ccid;
		this.version = version;
	}
	public CContentVO(String ccid, String version, String status) {
		this.ccid = ccid;
		this.version = version;
		this.status = status;
	}
	
	
	public static final String CLASS_BASIC     = "basic";
	public static final String CLASS_EXTENSION = "ext";
	
	public static final String STATUS_INDEX   = "index";
	public static final String STATUS_READY   = "ready";
	public static final String STATUS_SERVICE = "service";
	public static final HashMap<String, String> MAP_STATUS = new HashMap<String, String>();
	static {
		MAP_STATUS.put(STATUS_INDEX, STATUS_INDEX);
		MAP_STATUS.put(STATUS_READY, STATUS_READY);
		MAP_STATUS.put(STATUS_SERVICE, STATUS_SERVICE);
	}
	
	
	
	
	private String ccid;
	private String version;
	private String ownerId;
	private Date   ownerRegDate;
	private String status;
	private Date   regDate;
}
