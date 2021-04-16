package tom.mediabc.search.restapi;

import java.util.ArrayList;

import lombok.Data;
import tom.common.util.KeyStringValue;


@Data
public class SearchParam {
	private int nowPage = 1;
	private int rowPerPage = 10;
	private String ccid;
	private String version;
	private String metaSeq;
	private String fromDate;
	private String toDate;
	private String sortField;
	private String sortOrder;
	private String ccStatus = "service";
	private String keyword;
	
	
	
	private ArrayList<KeyStringValue> fields;
	
	public void addSearchField(KeyStringValue sFiels) {
		if(fields == null) {
			fields = new ArrayList<KeyStringValue>();
		}
		fields.add(sFiels);
	}
	
	
}
