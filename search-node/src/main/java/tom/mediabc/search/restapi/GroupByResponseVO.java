package tom.mediabc.search.restapi;

import java.util.ArrayList;

import lombok.Data;


@Data
public class GroupByResponseVO {
	
	private ArrayList<KeyValue> result = new ArrayList<KeyValue>();
	
	
	
	public void addKeyValue(String key, long value) {
		result.add(new KeyValue(key, value));
	}
}
@Data
class KeyValue {
	
	public KeyValue() {
	}
	public KeyValue(String key, long value) {
		this.key = key;
		this.value = value;
	}
	
	private String key;
	private long value;
}
