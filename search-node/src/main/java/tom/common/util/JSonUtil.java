package tom.common.util;

import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;

import org.json.JSONArray;
import org.json.JSONObject;



public class JSonUtil {

	public static Map<String, Double> JsonToDoubleMap(JSONObject jsonObject) {
		Map<String, Double> map = new HashMap<String, Double>();
		
		Iterator<String> iter = jsonObject.keys();
		while(iter.hasNext()) {
			String key = iter.next();
			double value = jsonObject.getDouble(key);
			
			map.put(key, value);
		}
		return map;
	}
	
	public static Map<String, Object> JsonToMap(JSONObject jsonObject) {
		Map<String, Object> map = new HashMap<String, Object>();
		
		Iterator<String> iter = jsonObject.keys();
		while(iter.hasNext()) {
			String key = iter.next();
			Object value = jsonObject.get(key);
			
			map.put(key, value);
		}
		return map;
	}
	
	public static JSONArray mapListToJsonArr(List<HashMap<String, Object>> list) {
		JSONArray jsonArr = new JSONArray();
		
		for(int i=0; i<list.size(); i++) {
			jsonArr.put(new JSONObject(list.get(i)));
		}
		
		return jsonArr;
	}
	
	
	public static String getStrValue(JSONObject json, String key, String def) {
		
		if(json.has(key)==false || json.isNull(key) ) {
			return def;
		} else {
			Object value = json.get(key);
			if(value instanceof String) {
				return (String) value;
			} else {
				return def;
			}
		}
	}
	
	public static int getIntValue(JSONObject json, String key, int def) {
		
		if(json.isNull(key)) {
			return def;
		} else {
			Object value = json.get(key);
			if(value instanceof Integer) {
				return ((Integer)value).intValue();
			} else if (value instanceof Long) {
				return ((Long)value).intValue();
			} else {
				return def;
			}
		}
	}
}
