package tom.mediabc.search.catalog;

import lombok.Data;

@Data
public class LoginInfo {

	public static final String SESSION_USER = "USER_SESSION";
	
	private String userId;
	private String password;
	//private String hashedPassword;
}
