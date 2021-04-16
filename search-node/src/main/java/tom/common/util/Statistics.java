package tom.common.util;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;



import org.json.JSONObject;

public class Statistics {
	double[] data;
    int size;   

    public Statistics(double[] data) 
    {
        this.data = data;
        size = data.length;
    }   

    public double getMean()
    {
        double sum = 0.0;
        for(double a : data)
            sum += a;
        return sum/size;
    }

    public double getVariance()
    {
        double mean = getMean();
        double temp = 0;
        for(double a :data)
            temp += (mean-a)*(mean-a);
        return temp/size;
    }

    public double getStdDev()
    {
        return Math.sqrt(getVariance());
    }

    public double median() 
    {
       Arrays.sort(data);

       if (data.length % 2 == 0) 
       {
          return (data[(data.length / 2) - 1] + data[data.length / 2]) / 2.0;
       } 
       else 
       {
          return data[data.length / 2];
       }
    }
    
    
    public double min() {
    	double min = data[0];
    	for(int i=1; i<data.length; i++) {
    		if(min > data[i]) {
    			min = data[i];
    		}
    	}
    	return min;
    }
    public double max() {
    	double min = data[0];
    	for(int i=1; i<data.length; i++) {
    		if(min < data[i]) {
    			min = data[i];
    		}
    	}
    	return min;
    }
    
    
    public static Map<String, Double> jsonToDoubleMap(JSONObject jsonMap) {
		HashMap<String, Double> map = new HashMap<String, Double>();
		Iterator<String> iter = jsonMap.keys();
		while(iter.hasNext()) {
			String key = iter.next();
			double value = jsonMap.getDouble(key);
			
			map.put(key, value);
		}
		
		return map;
	}
	
	
	public static List<KeyDoubleValue> mapToList(Map<String, Double>  dataMap, int limit, double cutValue) {
		
		if(dataMap == null) {
			return new ArrayList<KeyDoubleValue>();
		}
		
		List<KeyDoubleValue> list = new ArrayList<KeyDoubleValue>();
		Iterator<String> iter = dataMap.keySet().iterator();
		while(iter.hasNext()) {
			String key = iter.next();
			Double value = dataMap.get(key);
			
			list.add(new KeyDoubleValue(key, value));
		}
		
		KeyDoubleValue[] arr = new KeyDoubleValue[list.size()];
		for(int i=0; i<arr.length; i++) {
			arr[i] = list.get(i);
		}
		Arrays.sort(arr);
		
		List<KeyDoubleValue> retList = new ArrayList<KeyDoubleValue>();
		for(int i=0; i<arr.length; i++) {
			if(retList.size() >= limit) {
				break;
			}
			
			if(arr[i].getV() >= cutValue) {
				retList.add(arr[i]);	
			}
		}
		return retList;
	}
}
