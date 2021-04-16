package tom.common.basic;

import java.util.Date;

import com.fasterxml.jackson.annotation.JsonPropertyOrder;




@JsonPropertyOrder({"source", "operation", "timestamp", "body"})
public class BasicRequest extends BasicFields {
	
	
	public BasicRequest() {
	}
	
	public BasicRequest(String source, String operation, Date timestamp) {
		super(source, operation, timestamp);
	}
	
	
	@Override
	public String toString() {
		return "BasicRequest [source=" + source + ", operation=" + operation
				+ ", timestamp=" + timestamp + ", body=" + body + "]";
	}
}