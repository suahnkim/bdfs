package tom.mediabc.search.vo.dao;

import lombok.Data;

@Data
public class CContentFileVO {
	
	private int    contentFileSeq;
	private String ccid;
	private String version;
	private String contentPath;
	private String contentType;
	private long   contentSize;
	private String contentClass;
}
