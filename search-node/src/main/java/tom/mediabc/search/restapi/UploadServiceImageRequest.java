package tom.mediabc.search.restapi;

import java.util.ArrayList;
import java.util.List;

import com.fasterxml.jackson.annotation.JsonInclude;

import lombok.Data;
import tom.mediabc.search.vo.cc.ArtworkFileVO;

@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class UploadServiceImageRequest {
	
	private String ccid = null;
	private String version = null;
	private List<ArtworkFileVO> imageList = new ArrayList<ArtworkFileVO>();
}

