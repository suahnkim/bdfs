package tom.mediabc.search.restapi;

import java.util.ArrayList;

import com.fasterxml.jackson.annotation.JsonInclude;

import lombok.Data;

@Data
public class CcStatusUpdateRequestVO {
	private ArrayList<CcStatus> ccStatusList;
	
}

@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
class CcStatus {
	
	
	private String ccid;
	private String version;
	private String status;
	
	private String result;
	private String resultMessage;
	
	
}
