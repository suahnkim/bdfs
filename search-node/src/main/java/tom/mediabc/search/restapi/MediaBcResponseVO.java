package tom.mediabc.search.restapi;

import com.fasterxml.jackson.annotation.JsonInclude;

import lombok.Data;

@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class MediaBcResponseVO {

	private int result;
	private String desc;
}
