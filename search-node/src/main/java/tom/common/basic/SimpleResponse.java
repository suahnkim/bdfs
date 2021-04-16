package tom.common.basic;

public class SimpleResponse {
	public int status;
	public String message;
	
	public SimpleResponse(int status, String message) {
		this.status = status;
		this.message = message;
	}
	
	public SimpleResponse() {
		this.status = 0;
		this.message = "Success";
	}
	
	public int getStatus() {
		return status;
	}

	public void setStatus(int status) {
		this.status = status;
	}

	public String getMessage() {
		return message;
	}

	public void setMessage(String message) {
		this.message = message;
	}
}
