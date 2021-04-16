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

public class IOUtil {

	public static byte[] fileToByte(File file) throws GenException {
		
		InputStream is = null;
		try {
			is = new BufferedInputStream(new FileInputStream(file));
			ByteArrayOutputStream os = new ByteArrayOutputStream();
			
			byte[] buf = new byte[8192];
			int readNum = -1;
			
			while((readNum = is.read(buf)) != -1) {
				os.write(buf, 0, readNum);
			}
			
			return os.toByteArray();
			
		} catch (Exception e) {
			throw new GenException(GenException.IO_STREAM_READ_ERROR, "stream read error", e);
		} finally {
			close(is);
		}
	}

	public static byte[] streamToByte(InputStream is) throws GenException {
		
		try {
			ByteArrayOutputStream os = new ByteArrayOutputStream();
			
			byte[] buf = new byte[8192];
			int readNum = -1;
			
			while((readNum = is.read(buf)) != -1) {
				os.write(buf, 0, readNum);
			}
			
			return os.toByteArray();
			
		} catch (Exception e) {
			throw new GenException(GenException.IO_STREAM_READ_ERROR, "stream read error", e);
		}
	}
	
	public static long streamToFile(InputStream is, File outFile) throws GenException {
		long fileSize = 0;
		OutputStream os = null;
		try {
			os = new BufferedOutputStream(new FileOutputStream(outFile));
			byte[] buf = new byte[8192];
			int readNum = -1;
			
			while((readNum = is.read(buf)) != -1) {
				os.write(buf, 0, readNum);
				fileSize = fileSize + readNum;
			}
			
			return readNum;
		} catch (Exception e) {
			throw new GenException(GenException.IO_STREAM_READ_ERROR, "stream read error", e);
		} finally {
			close(os);
		}
	}
	
	
	public static void copy(File srcFile, File outFile) throws GenException {
		
		InputStream is = null;
		OutputStream os = null;
		
		try {
			File parentDir = outFile.getParentFile();
			if(parentDir.exists() == false) {
				parentDir.mkdirs();
			}
			
			is = new BufferedInputStream(new FileInputStream(srcFile));
			os = new BufferedOutputStream(new FileOutputStream(outFile));
			
			int readNum = -1;
			byte[] buf = new byte[8192];
			
			while((readNum = is.read(buf)) != -1) {
				os.write(buf, 0, readNum);
			}
			
		} catch (Exception e) {
			throw new GenException(GenException.IO_STREAM_READ_ERROR, "copy error", e);
		} finally {
			close(is);
			close(os);
		}
	}
	
	
	public static void close(InputStream o) {
		if(o != null) {
			try {
				o.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}
	
	public static void close(OutputStream o) {
		if(o != null) {
			try {
				o.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}
}