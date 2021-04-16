package tom.mediabc.search.vo.cc;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;

import com.fasterxml.jackson.annotation.JsonIgnore;
import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonProperty;

import lombok.Data;

@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class BasicMetaVO {

	@JsonIgnore
	private SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
	
	
	private String title;
	@JsonProperty("content-type")
	private String contentType;
	private String format;
	
	private List<IdentifierVO> identifier;
	private List<ContributionVO> contribution;
	
	@JsonProperty("meta-type")
	private String metaType;
	private String metaClass;
	
	private List<String> target;
	private MetaBasicMoveV1VO metadata;
	private String metaSeq;
	private Date lastModify;
	
	
	public String getLastModify() {
		if(lastModify != null) {
			return sdf.format(lastModify);
		} else {
			return null;
		}
	}
	
	public void setLastModify(String str) throws ParseException {
		lastModify = sdf.parse(str);
	}
	public void setLastModifyAsDate(Date date) throws ParseException {
		lastModify = date;
	}
	
}
