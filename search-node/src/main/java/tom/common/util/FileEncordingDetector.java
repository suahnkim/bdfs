package tom.common.util;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;

import org.mozilla.universalchardet.UniversalDetector;

public class FileEncordingDetector {

	
	
	public static String detectEncording(File file) {
		
		UniversalDetector detector = new UniversalDetector(null);
		BufferedInputStream bis = null;
		String encoding = "UTF-8";
		try {
			
			bis = new BufferedInputStream(new FileInputStream(file));
			
		    int readNum = 0;
		    byte[] buf = new byte[4096];
		    while((readNum = bis.read(buf)) != -1) {
		    	detector.handleData(buf, 0, readNum);
		    }
		    detector.dataEnd();
		    encoding = detector.getDetectedCharset();
		    if (encoding == null) {
		    	encoding = "UTF-8";
		    }
		    
			
		} catch (Exception e) {
			e.printStackTrace();
			
		} finally {
			close(bis);
			close(detector);
		}
		return "UTF-8";
	}
	
	
	private static void close(InputStream o) {
		if(o != null) {
			try {
				o.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}
	private static void close(UniversalDetector o) {
		if(o != null) {
			try {
				o.reset();
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
	}
}
