package tom.common.util;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import tom.common.configuration.LoggerName;

public class BufferedReaderForMulti {
	private static final Logger logger = LoggerFactory.getLogger(LoggerName.SVC);
	private BufferedReader br = null;
	private int count = 0;

	public BufferedReaderForMulti(File file) throws UnsupportedEncodingException, FileNotFoundException {
		br = new BufferedReader(new InputStreamReader(new FileInputStream(file), "UTF8"), 8192 * 50);
	}
	
	public synchronized String readLine() throws IOException {
		
		String line = br.readLine();
		if(line != null) {
			count++;
			if(count%100 == 0) {
				logger.info(">>>>>>>>>>> BufferedReaderForMulti Read["+count+"]");
			}
		}
		
		
		return line;
	}
	
	public void close()throws IOException {
		br.close();
	}
}