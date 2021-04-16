package tom.common.util;

import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import tom.common.configuration.LoggerName;

public class FileUtil {

	
	
	
	
	public static void write(File outFile, InputStream is) {
		File parentFile = outFile.getParentFile();
		if(parentFile.exists()==false) {
			parentFile.mkdirs();
		}
		
		OutputStream os = null;
		try {
			os = new BufferedOutputStream(new FileOutputStream(outFile));
			int readNum = -1;
			byte[] buf = new byte[8192];
			
			while((readNum = is.read(buf)) != -1) {
				os.write(buf, 0, readNum);
			}
		} catch (Exception e) {
			e.printStackTrace();
		} finally {
			close(os);
			close(is);
		}
	}
	
	
	public static byte[] read(File inFile) {
		
		ByteArrayOutputStream os = null;
		InputStream is = null;
		try {
			is = new BufferedInputStream(new FileInputStream(inFile));
			os = new ByteArrayOutputStream();
			int readNum = -1;
			byte[] buf = new byte[8192];
			
			while((readNum = is.read(buf)) != -1) {
				os.write(buf, 0, readNum);
			}
		} catch (Exception e) {
			e.printStackTrace();
		} finally {
			close(os);
			close(is);
		}
		
		return os.toByteArray();
	}
	
	
	public static String streamToString(InputStream is) {
		ByteArrayOutputStream os = new ByteArrayOutputStream();
		try {
			int readNum = -1;
			byte[] buf = new byte[8192];
			
			while((readNum = is.read(buf)) != -1) {
				os.write(buf, 0, readNum);
			}
		} catch (Exception e) {
			e.printStackTrace();
		} finally {

			close(is);
		}
		return os.toString();
	}
	
	
	public static void deleteFile(File file) {
		Logger log = LoggerFactory.getLogger(LoggerName.SVC);
		
		if(file.isDirectory()) {
			
			File[] files = file.listFiles();
			for(int i=0; i<files.length; i++) {
				deleteFile(files[i]);
			}
			boolean delete = file.delete();
			log.info(file.getAbsolutePath() + " DELETE ["+delete+"]");
			//file.deleteOnExit();
			
		} else {
			boolean delete = file.delete();
			log.info(file.getAbsolutePath() + " DELETE ["+delete+"]");
		}
		
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
	
	private static void close(OutputStream o) {
		if(o != null) {
			try {
				o.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}
}
