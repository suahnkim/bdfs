package tom.mediabc.search.restapi;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;

import com.fasterxml.jackson.annotation.JsonInclude;

import lombok.Data;


@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class MediaBcRegEventVO {

	private String reg;
	private String ccid;
	private String version;
	private String category1;
	private String category2;
	private String accountid;
	
	
	
	public static final String FLAG_TRUE  = "true";
	public static final String FLAG_FALSE = "false";
	
	
	public static final String MODE_INSERT = "i";
	public static final String MODE_MODIFY = "m";
	public static final String MODE_DELETE = "d";
	
	
	//20200710 추가
	private String flag;
	private String mode;
	
	
	
	
	
	
	public Date getRegAsDate() throws Exception {
		if(reg == null || reg.trim().equals("")) {
			return null;
		}
		try {
			SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss");
			return sdf.parse(reg);	
		} catch (ParseException e) {
			throw new Exception("invalid reg-date format ["+reg+"]");
		}
		
	}
}
