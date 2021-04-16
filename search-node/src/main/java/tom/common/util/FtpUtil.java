package tom.common.util;

import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.OutputStream;
import java.net.SocketException;

import org.apache.commons.net.ftp.FTP;
import org.apache.commons.net.ftp.FTPClient;

public class FtpUtil {
	private FTPClient ftpClient = new FTPClient();
	
	public FtpUtil(String host, String id, String pw) throws SocketException, IOException {
		init(host, id, pw);
	}
	
	private void init(String host, String id, String pw) throws SocketException, IOException {
		//ftpClient.connect("mr.ftp.dingastar.com");
		//boolean login = ftpClient.login("msi01", "ftpftp1234");
		
		ftpClient.connect(host);
		boolean login = ftpClient.login(id, pw);
		if(!login) {
			throw new IOException("login failure.");
		}
		ftpClient.enterLocalPassiveMode();
		ftpClient.setBufferSize(1024);    
		ftpClient.setFileType(FTP.BINARY_FILE_TYPE); 
	}
	
	public boolean download(String path, File dest) {
		
		boolean exists = false;
		OutputStream os = null;
		
		try {
			os = new BufferedOutputStream(new FileOutputStream(dest));
			return ftpClient.retrieveFile(path, os);
		} catch (Exception e) {
			return exists;
		} finally {
			close(os);
		}
	}
	
	

	
	private void close(OutputStream os) {
		if(os != null) {
			try {
				os.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}
	
	
	public void close(){
		if(ftpClient != null) {
			try {
				ftpClient.logout();
			} catch (IOException e) {
				e.printStackTrace();
			}
			try {
				ftpClient.disconnect();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}
	
	
}
