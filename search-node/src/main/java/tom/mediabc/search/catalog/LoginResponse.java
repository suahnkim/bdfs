package tom.mediabc.search.catalog;

import lombok.Data;

@Data
public class LoginResponse {

	public static final String STATUS_NOT_FOUND_USER = "notFoundUser";
	public static final String STATUS_MISSMATCH_PW = "missmatchPw";
	public static final String STATUS_SUCCESS = "success";
	
	
	private String status;
	private String message;
	private LoginInfo loginInfo;
}
