package tom.mediabc.search.vo.dao;

import lombok.Data;

@Data
public class MetadataFileVO {

	public static String STATUS_INIT = "INIT";
	public static String STATUS_ADD  = "ADD";
	public static String STATUS_DEL  = "DEL";
	
	private int    metaFileSeq;
	private String ccid;
	private String version;
	private String metaPath;
	private String metaType;
	private long   metaSize;
	private String metaClass;
	private String fileStatus;

	
}
