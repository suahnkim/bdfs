package tom.mediabc.search.restapi;

import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.OutputStream;

import org.bouncycastle.util.encoders.Base64;

public class Base64ImageDecoder {
	
	private String base64Str = null;
	private String header = null;
	private String base64Img = null;
	private String format = null;
	private String encoding = null;
	
	
	public Base64ImageDecoder(String base64Str) {
		this.base64Str = base64Str;
		init();
	}
	
	
	private void init() {	
		if(base64Str.startsWith("data:image")) {
			header    = base64Str.substring(0, base64Str.indexOf(','));
			base64Img = base64Str.substring(base64Str.indexOf(',')+1);
			//System.out.println("["+header+"]");
			String[] fields = header.substring(11).split(";");
			format   = fields[0];
			encoding = fields[1];
		}
	}
	
	
	public String getEncoding() {
		return encoding;
	}
	
	public String getFormat() {
		return format;
	}
	
	public void writeImage(File file) throws IOException {
		File parentDir = file.getParentFile();
		parentDir.mkdirs();
		OutputStream os = null;
		
		try {
			os = new BufferedOutputStream(new FileOutputStream(file));
			Base64.decode(base64Img, os);
			os.flush();	
		} finally {
			close(os);
		}
	}
	
	private void close(OutputStream o) {
		if(o != null) {
			try {
				o.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}
}
