package tom.mediabc.search.vo.dao;

import java.util.Date;

import lombok.Data;

@Data
public class MetadataVO {

	
	public static final String METATYPE_BASIC_MOVIEv01 = "basic-movie.v1";
	
	private int metaSeq;
	private String ccid;
	private String version;
	private String metaPath;
	private String metadataOriginal;
	private String metadataService;
	private String metaType;
	private String metaClass;
	private String title;
	private String contentType;
	private Date   lastModify;
	

	
}
