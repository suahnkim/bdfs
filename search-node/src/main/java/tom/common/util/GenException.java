package tom.common.util;

public class GenException extends Exception {
	
	private static final long serialVersionUID = -3165639298370787488L;
	
	public static final String SUCCESS_MESSAGE = "Success";
	public static final int SUCCESS = 0;

	//CONFIGURATION ERROR 90010000 ~
	public static final int CONF_ERROR                 = 90010000;
	public static final int CONF_INVALID_TYPE          = 90010001;
	public static final int CONF_VALUD_IS_NULL         = 90010002;
	
	
	//INTERFACE ERROR 90020000 ~
	public static final int IF_PARAM_IS_NULL	       = 90020000;
	public static final int IF_INVALID_VALUE           = 90020001;
	public static final int IF_PARSING_ERROR           = 90020002;
	public static final int IF_BUILDING_ERROR          = 90020003;
	
	
	
	//IO ERROR 90030000 ~
	public static final int IO_STREAM_READ_ERROR	   = 90030000;
	public static final int IO_STREAM_IS_NULL          = 90030001;
	public static final int IO_NOT_FOUND_FILE          = 90030100;
	public static final int IO_MKDIR_ERROR             = 90030101;
	public static final int IO_REMOVE_FILE_ERROR       = 90030102;
	
	
	//DATABASE ERROR 90040000 ~
	public static final int DB_ERROR                   = 90040000;
	public static final int DB_SELECT_ERROR            = 90040001;
	public static final int DB_INSERT_ERROR            = 90040002;
	public static final int DB_UPDATE_ERROR            = 90040003;
	public static final int DB_DELETE_ERROR            = 90040004;
	
	//INTERNAL ERROR 90050000 ~
	public static final int INTERNAL_ERROR             = 90050000;
	
	
	//EXTERNAL PROGRAM ERROR 90060000 ~
	public static final int EXTERNAL_PROGRAM_ERROR     = 90060000;
	
	
	//EXTERNAL PROGRAM ERROR 90060000 ~
	public static final int USER_LOGIN_FAILURE         = 90061001;
	
	
	private int errorCode = -1;
	
	public GenException(int errorCode, String message) {
		super(message);
		this.errorCode = errorCode;
	}
	
	public GenException(int errorCode, String message, Throwable cause) {
		super(message, cause);
		this.errorCode = errorCode;
	}
	
	public int getErrorCode() {
		return errorCode;
	}
}