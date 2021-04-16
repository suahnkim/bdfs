package tom.common.basic;

import java.io.IOException;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.core.JsonGenerationException;
import com.fasterxml.jackson.core.JsonParseException;
import com.fasterxml.jackson.databind.JsonMappingException;
import com.fasterxml.jackson.databind.ObjectMapper;



@JsonIgnoreProperties({"timestampAsDate", "tid"})
public class BasicFields {
	
	protected SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss");
	protected String source = null;
	protected String operation = null;
	protected Date timestamp = null;
	protected int tid = -1;
	protected HashMap<String, Object> body = new HashMap<String, Object>(); 
	

	public BasicFields() {
	}
	
	public BasicFields(String source, String operation, Date timestamp) {
		this.source = source;
		this.operation = operation;
		this.timestamp = timestamp;
	}
	
	public String getSource() {
		return source;
	}
	public void setSource(String source) {
		this.source = source;
	}
	public String getOperation() {
		return operation;
	}
	public void setOperation(String operation) {
		this.operation = operation;
	}
	public String getTimestamp() {
		return sdf.format(timestamp);
	}
	public Date getTimestampAsDate() {
		return timestamp;
	}
	public void setTimestamp(String timestamp) throws ParseException {
		this.timestamp = sdf.parse(timestamp);
	}
	public void setTimestampAsDate(Date timestamp) throws ParseException {
		this.timestamp = timestamp;
	}
	public HashMap<String, Object> getBody() {
		return body;
	}
	public int getTid() {
		return tid;
	}
	public void setTid(int tid) {
		this.tid = tid;
	}
	
	
	
	public byte[] toJSONString() throws JsonGenerationException, JsonMappingException, IOException {
		ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
		return mapper.writeValueAsBytes(this);
	}
	
	
	public static <T> T readValue(byte[] src, Class<T> valueType) throws IOException, JsonParseException, JsonMappingException {
		ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
		return mapper.readValue(src, valueType);
	}
	
	
	
	
	
	public String getStringBody(String key) {
		Object obj = body.get(key);
		
		
		
		if(obj instanceof String) {
			return (String)obj;
		} else {
			return null;
		}
	}
	public Integer getIntegerBody(String key) {
		Object obj = body.get(key);
		
		if(obj instanceof Integer) {
			return (Integer)obj;
		} else {
			return null;
		}
	}
	public int getIntegerBody(String key, int def) {
		Integer value = getIntegerBody(key);
		if(value == null) {
			return def;
		} else {
			return value.intValue();
		}
	}
	public Long getLongBody(String key) {
		Object obj = body.get(key);
		
		if(obj instanceof Long) {
			return (Long)obj;
		} else {
			return null;
		}
	}
	public long getLongBody(String key, long def) {
		Long value = getLongBody(key);
		if(value == null) {
			return def;
		} else {
			return value.longValue();
		}
	}
	public Double getDoubleBody(String key) {
		Object obj = body.get(key);
		
		if(obj instanceof Double) {
			return (Double)obj;
		} else {
			return null;
		}
	}
	public double getDoubleBody(String key, double def) {
		Double value = getDoubleBody(key);
		if(value == null) {
			return def;
		} else {
			return value.doubleValue();
		}
	}
	public Object getObjectBody(String key) {
		return body.get(key);
	}
	
}
