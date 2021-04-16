package tom.mediabc.search.vo.cc;

import com.fasterxml.jackson.annotation.JsonInclude;

import lombok.Data;

@Data
@JsonInclude(JsonInclude.Include.NON_NULL)
public class ArtworkFileVO {

	private String title;
	private String fileName;
	private long fileSize;
	
	private String rep;
	private long height;
	private long width;
	private String format;
	
	private String base64Image;
}
