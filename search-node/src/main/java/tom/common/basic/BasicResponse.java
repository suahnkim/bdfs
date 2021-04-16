package tom.common.basic;

import java.util.Date;

import com.fasterxml.jackson.annotation.JsonPropertyOrder;





@JsonPropertyOrder({"source", "operation", "timestamp", "status", "message", "body"})
public class BasicResponse extends BasicFields {
	
	private long status = -1;
	private String message = null;
	
	public BasicResponse() {
	}
	
	public BasicResponse(String source, String operation, Date timestamp, long status, String message) {
		super(source, operation, timestamp);
		this.status = status;
		this.message = message;
		
		
	}
	
	public long getStatus() {
		return status;
	}
	public void setStatus(long status) {
		this.status = status;
	}
	public String getMessage() {
		return message;
	}
	public void setMessage(String message) {
		this.message = message;
	}
	
	
	@Override
	public String toString() {
		return "BasicRequest [source=" + source + ", operation=" + operation
				+ ", timestamp=" + timestamp + ", status=" + status
				+ ", message=" + message + ", body=" + body + "]";
	}
}