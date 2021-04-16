package tom.common.configuration;

import java.util.HashMap;




public class Configuration {
	
	private static Configuration instance = null;
	private Configuration() {	
    }
	
    public static synchronized Configuration getInstance() {
        if (instance == null) {
            instance = new Configuration();
        }
        return instance;
    }
	
	
	private boolean init = false;
	private String serverName = null;
	private HashMap<String, Object> extras = new HashMap<String, Object>();
	
	private boolean runningAllBatch = false;
	private boolean runningSingleBatch = false;
	
	public void clearExtras() {
		extras.clear();
	}
	public void putExtra(String key, String value) {
		extras.put(key, value);
	}
	public void putExtra(String key, Integer value) {
		extras.put(key, value);
	}
	public void putExtra(String key, Long value) {
		extras.put(key, value);
	}
	public void putExtra(String key, Double value) {
		extras.put(key, value);
	}
	
	public String getStringExtra(String key) {
		Object obj = extras.get(key);
		
		if(obj instanceof String) {
			return (String)obj;
		} else {
			return null;
		}
	}
	public String getStringExtra(String key, String def) {
		String value = getStringExtra(key);
		if(value == null) {
			return def;
		} else {
			return value;
		}
	}
	
	public Integer getIntegerExtra(String key) {
		Object obj = extras.get(key);
		
		if(obj instanceof Integer) {
			return (Integer)obj;
		} else {
			return null;
		}
	}
	public int getIntegerExtra(String key, int def) {
		Integer value = getIntegerExtra(key);
		if(value == null) {
			return def;
		} else {
			return value;
		}
	}
	
	public Long getLongExtra(String key) {
		Object obj = extras.get(key);
		
		if(obj instanceof Long) {
			return (Long)obj;
		} else {
			return null;
		}
	}
	public long getLongExtra(String key, long def) {
		Long value = getLongExtra(key);
		if(value == null) {
			return def;
		} else {
			return value;
		}
	}
	
	public Double getDoubleExtra(String key) {
		Object obj = extras.get(key);
		
		if(obj instanceof Double) {
			return (Double)obj;
		} else {
			return null;
		}
	}
	public double getDoubleExtra(String key, double def) {
		Double value = getDoubleExtra(key);
		if(value == null) {
			return def;
		} else {
			return value;
		}
	}
	
	
	
	
	
	
	
	public boolean isRunningAllBatch() {
		return runningAllBatch;
	}
	public void setRunningAllBatch(boolean runningAllBatch) {
		this.runningAllBatch = runningAllBatch;
	}
	public boolean isRunningSingleBatch() {
		return runningSingleBatch;
	}
	public void setRunningSingleBatch(boolean runningSingleBatch) {
		this.runningSingleBatch = runningSingleBatch;
	}
	public boolean isInit() {
		return init;
	}
	public void setInit(boolean init) {
		this.init = init;
	}
	public String getServerName() {
		return serverName;
	}
	public void setServerName(String serverName) {
		this.serverName = serverName;
	}
	
	
	
	public static final boolean parsingBoolean(String str) {
		if(str != null && str.trim().toUpperCase().equals("TRUE")) {
			return true;
		} else {
			return false;
		}
	}
}