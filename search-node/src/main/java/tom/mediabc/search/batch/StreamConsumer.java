package tom.mediabc.search.batch;

import java.io.InputStream;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import tom.common.configuration.LoggerName;

public class StreamConsumer extends Thread {
	private Logger log = LoggerFactory.getLogger(LoggerName.INDEX_BATCH);
	private InputStream  is = null;
	private int tid = 0;
	public StreamConsumer(int tid, InputStream is) {
		this.is = is;
		this.tid = tid;
	}
	public StreamConsumer(InputStream is) {
		this.is = is;
	}
	
	public void run()  {
		try {
			//log = LoggerFactory.getLogger(LoggerName.VIMS_TC);	
		} catch (Exception e) {
			//
		}
		
		try {
			
			/*
			byte[] buf = new byte[8192];
			int readNum = -1;
			while((readNum = is.read(buf)) != -1) {
				System.out.println(new String(buf, 0, readNum));
			}
			*/
			
			
			int readNum = -1;
			byte[] buf = new byte[8192];
			while((readNum = is.read(buf)) != -1) {
				String msg = new String(buf, 0, readNum);
				//logger.error("["+tid+"] " + msg);
				//errorMsg.append(msg);
				if(log != null) {
					log.debug("["+tid+"] " + msg);
				} else {
					System.out.println(msg);	
				}
			}
			
		} catch (Throwable e) {
			if(log != null) {
				log.error("["+tid+"] StreamConsumer error ", e);	
			} else {
				System.err.println("["+tid+"] StreamConsumer error " + e.getMessage());	
			}
			//
			
		}
	} 
}
