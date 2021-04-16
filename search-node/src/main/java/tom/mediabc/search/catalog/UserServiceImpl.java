package tom.mediabc.search.catalog;

import javax.servlet.http.HttpServletRequest;

import org.bouncycastle.util.encoders.Base64;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import tom.common.configuration.LoggerName;
import tom.common.util.SimpleCryptoUtil;
import tom.mediabc.search.dao.UserDAOImpl;

@Service("userService")
public class UserServiceImpl {

	
	
	private Logger log = LoggerFactory.getLogger(LoggerName.SVC);
	
	
	@Autowired
	private UserDAOImpl userDAO;
	
	
	public LoginResponse login(int tid, LoginInfo reqUser, HttpServletRequest request) {
		
		log.debug("["+tid+"] start login " + reqUser);
		LoginResponse loginRes = new LoginResponse();
		
		try {
		
			LoginInfo dbUser = userDAO.selectUserByUserId(reqUser.getUserId());
			if(dbUser == null) {
				loginRes.setStatus(LoginResponse.STATUS_NOT_FOUND_USER);
				return loginRes;
			}
			
			String encPw = dbUser.getPassword();
			byte[] base64EncPw = Base64.decode(encPw);
			
			byte[] salt = new byte[4];
			byte[] hash = new byte[base64EncPw.length-4];
			System.arraycopy(base64EncPw, 0, salt, 0, 4);
			System.arraycopy(base64EncPw, 4, hash, 0, hash.length);
			
			
			
			String reqPw = reqUser.getPassword();
			byte[] reqPwByte = reqPw.getBytes();
			//byte[] reqSaltPw = new byte[4 + reqPwByte.length];
			//System.arraycopy(salt, 0, reqSaltPw, 0, 4);
			//System.arraycopy(reqPwByte, 0, reqSaltPw, 4, reqPwByte.length);
			
			byte[] reqHashByte = SimpleCryptoUtil.sha256(salt, reqPwByte);
			
			String dbHash = new String(Base64.encode(hash));
			String reqHash = new String(Base64.encode(reqHashByte));
			if(dbHash.equals(reqHash)) {
				dbUser.setPassword("");
				loginRes.setStatus(LoginResponse.STATUS_SUCCESS);
				loginRes.setLoginInfo(dbUser);
				
				request.getSession().setAttribute(LoginInfo.SESSION_USER, dbUser);
				
			} else {
				loginRes.setMessage(LoginResponse.STATUS_MISSMATCH_PW);
			}
			
			return loginRes;
		} catch (Exception e) {
			log.error("["+tid+"] login err ", e);
			loginRes.setStatus("error");
			return loginRes;
		}
	}
}
