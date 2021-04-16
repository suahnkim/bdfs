package tom.mediabc.search.vo.cc;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;

import com.fasterxml.jackson.annotation.JsonIgnore;
import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;

import lombok.Data;



@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class EsCContentVO {
	
	@JsonIgnore
	private SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
	
	private String ccid;
	private String version;
	private String status;
	private String ownerId;
	private Date   ownerRegDate;
	
	private ArrayList<BasicMetaVO> metaContainer;
	//private ArrayList<MetaBasicMoveV1VO> metadata;
	
	@JsonProperty("_score")
	private float score;
	
	
	public String getOwnerRegDate() {
		if(ownerRegDate != null) {
			return sdf.format(ownerRegDate);
		} else {
			return null;
		}
	}
	
	public void setOwnerRegDate(String str) throws ParseException {
		ownerRegDate = sdf.parse(str);
	}
	public void setOwnerRegDateAsDate(Date date) throws ParseException {
		ownerRegDate = date;
	}
	
}
