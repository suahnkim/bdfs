package tom.mediabc.search.vo.cc;

import java.util.ArrayList;
import java.util.List;

import com.fasterxml.jackson.annotation.JsonProperty;

import lombok.Data;

@Data
public class ManifestVO {

	
	
	@JsonProperty("basic-meta")
	private List<FileInfoVO> basicMeta;
	@JsonProperty("extended-meta")
	private List<FileInfoVO> extendedMeta;
	private List<FileInfoVO> contents;
	@JsonProperty("derivedContents")
	private List<FileInfoVO> derivedContents;
	
}
