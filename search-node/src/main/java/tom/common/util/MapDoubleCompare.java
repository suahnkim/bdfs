package tom.common.util;

import java.util.Comparator;
import java.util.Map;

public class MapDoubleCompare implements Comparator<Map<String, Object>> {
    private final String key;

    public MapDoubleCompare(String key){
        this.key = key;
    }

    public int compare(Map<String, Object> first, Map<String, Object> second) {
        // TODO: Null checking, both for maps and values
        
    	if(first.get(key) == null) {
    		first.put(key, 0);
    	}
    	if(second.get(key) == null) {
    		second.put(key, 0);
    	}
    	
    	Double firstValue = (Double)first.get(key);
    	Double secondValue = (Double)second.get(key);
        
        if(firstValue > secondValue) {
        	return -1;
        } else if (firstValue < secondValue) {
        	return 1;
        } else {
        	return 0;
        }
    }
}