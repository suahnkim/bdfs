package  tom.common.util;


import java.security.Key; 
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.SecureRandom;

import javax.crypto.Cipher;
import javax.crypto.spec.SecretKeySpec;

import org.bouncycastle.util.encoders.Base64;
import org.bouncycastle.util.encoders.Hex;


public class SimpleCryptoUtil {
	private static final String ALGO = "AES";
	private static final byte[] keyValue = 
			new byte[] { 'm', 'a', 'r', 'k', 'a', 'n', 'y', 'm', 'u', 's', 'i','c', 'm', 'a', 't', 'e' };

	public static String encrypt(String data) throws Exception {
		Key key = generateKey();
		Cipher c = Cipher.getInstance(ALGO);
		c.init(Cipher.ENCRYPT_MODE, key);
		byte[] encVal = c.doFinal(data.getBytes());
		return new String(Base64.encode(encVal));
	}

	public static String decrypt(String encryptedData) throws Exception {
		Key key = generateKey();
		Cipher c = Cipher.getInstance(ALGO);
		c.init(Cipher.DECRYPT_MODE, key);
		byte[] decordedValue = Base64.decode(encryptedData);
		byte[] decValue = c.doFinal(decordedValue);
		String decryptedValue = new String(decValue);
		return decryptedValue;
	}

	private static Key generateKey() throws Exception {
		Key key = new SecretKeySpec(keyValue, ALGO);
		return key;
	}
	
	public static String genHexSessionKey(int len) throws NoSuchAlgorithmException {
		byte[] randByte = new byte[len];
		getSecureRandom(randByte);
		return new String(Hex.encode(randByte));
	}
	
	public static void getSecureRandom(byte[] ranByte) throws NoSuchAlgorithmException {
		SecureRandom sRandom = null;
		try {
			sRandom = SecureRandom.getInstance("SHA1PRNG");
			sRandom.nextBytes(ranByte);
		} catch (NoSuchAlgorithmException e) {
		    throw e;
		}
	}
	
	public static String sha256(String data) throws Exception {
		MessageDigest md = MessageDigest.getInstance("SHA-256");
		md.update(data.getBytes("UTF-8")); // Change this to "UTF-16" if needed
		byte[] digest = md.digest();
		return new String(Base64.encode(digest));
	}
	
	
	public static byte[] sha256(byte[] salt, byte[] data) throws Exception {
		MessageDigest md = MessageDigest.getInstance("SHA-256");
		md.update(salt);
		md.update(data);
		return md.digest();
	}
	
	
	public static String genSessionKey(int len) throws NoSuchAlgorithmException {
		byte[] randByte = new byte[len];
		getSecureRandom(randByte);
		return new String(Base64.encode(randByte));
	}
	
	
	public static void main(String[] args) throws Exception {

		
		
		String mdn = "01012341234";
		String encData = SimpleCryptoUtil.encrypt(mdn);
		
		System.out.println("Plain Text : " + mdn);
		System.out.println("Encrypted Text : " + encData);
		
		String orgData = SimpleCryptoUtil.decrypt(encData);
		System.out.println("Decrypted Text : " + orgData);
		
		
	}
}
