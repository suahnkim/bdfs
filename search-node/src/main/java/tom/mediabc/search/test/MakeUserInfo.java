package tom.mediabc.search.test;

import java.io.ByteArrayOutputStream;

import org.bouncycastle.util.encoders.Base64;

import tom.common.util.SimpleCryptoUtil;

public class MakeUserInfo {

	public static void main(String[] args) {
	
		
		
		try {
			String userId = "user01";
			String password = "1111";
			
			byte[] salt = new byte[4];
			SimpleCryptoUtil.getSecureRandom(salt);
			
			
			byte[] hash = SimpleCryptoUtil.sha256(salt, password.getBytes());
			
			ByteArrayOutputStream baos = new ByteArrayOutputStream();
			baos.write(salt);
			baos.write(hash);
			byte[] saltHash = baos.toByteArray();
			
			String saltHashStr = new String(Base64.encode(saltHash));
			//System.out.println("["+saltHashStr+"]");
			
			
			
			System.out.println("INSERT INTO user(user_id, password) VALUES('"+userId+"', '"+saltHashStr+"') ");
			
			
			
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		
	}

}
