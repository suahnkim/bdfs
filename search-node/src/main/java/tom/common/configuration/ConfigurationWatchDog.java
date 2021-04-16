package tom.common.configuration;

import java.io.File;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import tom.common.util.GenException;


public class ConfigurationWatchDog extends Thread {
	
	private File configFile = null;
	private long deleyTime = 5000;
	private long lastModified = 0;
	private  Logger log = null;
	
	
	public ConfigurationWatchDog(File configFile, long deleyTime, Logger log) throws GenException {
		this.deleyTime = deleyTime;
		this.configFile = configFile;
		if(log == null) {
			log = LoggerFactory.getLogger(ConfigurationWatchDog.class);
		} else {
			this.log = log;	
		}
		
		lastModified = configFile.lastModified();
		ConfigurationParser.parseConfig(configFile, log);
	}
	
	
	public void run() {
		
		while(true) {
			
			try {
				sleep(deleyTime);
			} catch (InterruptedException e) {
				e.printStackTrace();
			}
			
			long nowLastModified = configFile.lastModified();
			
			if(lastModified != nowLastModified) {
				log.info("changed configuration!!");
				lastModified = nowLastModified;
				
				try {
					ConfigurationParser.parseConfig(configFile, log);	
					log.info("applyed changed configuration!!");
				} catch (Exception e) {
					log.error("configuration error  " + e.getMessage(), e);
				}
			}
		}
	}
}
