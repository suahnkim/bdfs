package tom.common.util;

import org.json.JSONObject;

public class StringUtil {

	
	
	public static boolean isInteger(String num) {
		if(num == null) {
			return false;
		}
		try {
			Integer.parseInt(num);
			return true;
		} catch (Exception e) {
			return false;
		}
	}
	
	public static String nullToBlank(String str) {
		if(str == null) {
			return "";
		} else {
			return str;
		}
	}
	
	public static String nullToBlank(Object str) {
		if(str == null) {
			return "";
		} else {
			return str.toString();
		}
	}
	
	
	public static String albumIdToUrl(String id) {
		
		if(id != null && id.length() == 8) {
			return "http://media.dingaradio.com/img/alb/" + id.substring(0, 3) + "/" + id.substring(3, 6) +"/" + id + ".jpg";
			
			
		} else {
			return "";
		}
	}
	
	
	public static String getString(JSONObject jsonObj, String name, String def) {
		if(jsonObj.isNull(name) == true) {
			return def;
		} else {
			return jsonObj.get(name).toString();
		}
	}
}
