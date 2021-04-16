package tom.mediabc.search.restapi;

import com.fasterxml.jackson.annotation.JsonInclude;

import lombok.Data;

@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class DefResponseVO {

	public static final String SUCCESS = "Success";
	public static final String ERROR   = "Error";
	
	private String status;
	private String result;
	private String resultMessage;
}
