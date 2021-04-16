package tom.common.util;

import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;
import java.sql.Timestamp;
import java.util.Date;
import java.util.HashMap;
import java.util.Iterator;

import com.google.common.base.CaseFormat;



public class VOUtil {

	
	
	public static void fillValues(HashMap<String, Object> src, Object obj) throws NoSuchMethodException, SecurityException, IllegalAccessException, IllegalArgumentException, InvocationTargetException {
		Method[] methodArr = obj.getClass().getMethods();
		HashMap<String, Method> methodMap = new HashMap<String, Method>();
		for(int i=0; i<methodArr.length; i++) {
			if(methodArr[i].getParameters().length == 1) {
				methodMap.put(methodArr[i].getName(), methodArr[i]);	
			}
		}
		
		
		Iterator<String> iter = src.keySet().iterator();
		while(iter.hasNext()) {
			String key = iter.next();	
			Object value = src.get(key);
			String methodName = "set" + CaseFormat.LOWER_UNDERSCORE.to(CaseFormat.UPPER_CAMEL, key);
			
			
			if(value == null) {
				//PASS
			} else if (value instanceof String) {	
				Method method = methodMap.get(methodName);
				if(method != null) {
					if(method.getParameters()[0].getType().getName().equals(String.class.getName())) {
						method.invoke(obj, value);
					}
				}
				
			} else if (value instanceof Integer) {
				Method method = methodMap.get(methodName);
				if(method != null) {
					if(method.getParameters()[0].getType().getName().equals("int")) {
						method.invoke(obj, value);
					}
				}
			
			} else if (value instanceof Long) {
				Method method = methodMap.get(methodName);
				if(method != null) {
					if(method.getParameters()[0].getType().getName().equals("long")) {
						method.invoke(obj, value);
					} else if(method.getParameters()[0].getType().getName().equals("int")) {
						method.invoke(obj, new Integer(((Long)value).intValue()));
					}
				}
				
			} else if (value instanceof Double) {
				Method method = methodMap.get(methodName);
				if(method != null) {
					if(method.getParameters()[0].getType().getName().equals("double")) {
						method.invoke(obj, value);
					} 
				}
			
			} else if (value instanceof Timestamp) {
				Method method = methodMap.get(methodName);
				if(method != null) {
					//System.out.println("Timestamp " + value);
					
					//System.out.println("method.getParameters()[0].getType().getName() " + method.getParameters()[0].getType().getName());
					//System.out.println("Date.class.getName() " + Date.class.getName());
					if(method.getParameters()[0].getType().getName().equals(Date.class.getName())) {
						method.invoke(obj, new Date(((Timestamp)value).getTime()));
					}
				}
				
			} else if (value instanceof java.sql.Date) {
				Method method = methodMap.get(methodName);
				if(method != null) {
					if(method.getParameters()[0].getType().getName().equals(Date.class.getName())) {
						method.invoke(obj, new Date(((java.sql.Date)value).getTime()));
					}
				}
			} else {
				//System.out.println("else  " + value + " ;;;; " + value.getClass());
			}
		}
		

	}
	
	
	
	public static String nullStr(String str) {
		if(str == null) {
			return "";
		} else {
			return str;
		}
	}
	
}
