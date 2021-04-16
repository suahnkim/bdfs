package tom.common.basic;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Random;

import javax.servlet.http.HttpServletRequest;

import tom.common.configuration.Configuration;
import tom.common.configuration.LoggerName;
import tom.common.util.GenException;

import org.json.JSONObject;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

public abstract class BasicController {
	
	protected Configuration config = Configuration.getInstance();
	protected Random random = new Random();
	public Logger log = LoggerFactory.getLogger(LoggerName.SVC);
	public static final String XSTATUS_CODE   = "X-Status-Code";
	public static final String XSTATUS_REASON = "X-Status-Reason";
	public static final String SESSION_KEY    = "Session-Key";
	
	public int genTid() {
		return random.nextInt();
	}
	
	public String getServerName() {
		return config.getServerName();
	}
	
	public byte[] genErrorJson(int tid, byte[] reqByte, String operation, Exception ex, Logger log) {
		
		int errorCode = GenException.INTERNAL_ERROR;
		String message = ex.getMessage() + "";
		
		if(ex instanceof GenException) {
			errorCode = ((GenException)ex).getErrorCode();
		} 
		
		SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss");
		JSONObject jsonObj = new JSONObject();
			
		jsonObj.put("source",    getServerName());
		jsonObj.put("operation", operation);
		jsonObj.put("timestamp", sdf.format(new Date()));
		jsonObj.put("status",    errorCode);
		jsonObj.put("message",   message);
		
		
		byte[] resByte = null;
		
		try {
			resByte = jsonObj.toString(4).getBytes("UTF-8");
		} catch (Exception e) {
			resByte = jsonObj.toString(4).getBytes();
		}
		
		if(log != null) {
			log.error("["+tid+"] Input  >>" + new String(reqByte) + "<<");	
			log.error("["+tid+"] Output >>" + new String(resByte) + "<<");	
			log.error("["+tid+"] " + ex.getMessage(), ex);	
		}
		
		return resByte;
	}
	
	
	public String nullToBlank(String str) {
		if(str == null) {
			return "";
		} else {
			return str;
		}
	}
	
	
	public byte[] getInputStreamToByte(InputStream is) throws IOException {
		
		int readNum = -1;
		byte[] buf = new byte[8192];
		ByteArrayOutputStream os = new ByteArrayOutputStream();
		
		while((readNum = is.read(buf)) != -1) {
			os.write(buf, 0, readNum);
		}
		
		is.close();
		return os.toByteArray();
	}
	
	public JSONObject genByteToJson(byte[] buf) {
		JSONObject jsonObj = null;
		try {
			return new JSONObject(new String(buf, "UTF-8"));
		} catch (Exception e) {
			log.warn("genByteToJson " + e);
		}
		
		return jsonObj;
	}
	
	
	public String genReqInfo(HttpServletRequest request)
	  {
	    StringBuffer sb = new StringBuffer();
	    if (request != null)
	    {
	      String userIp = getIpFromRequest(request);
	      String userAgent = request.getHeader("User-Agent");
	      if ((userAgent != null) && (userAgent.length() > 20)) {
	        userAgent = userAgent.substring(0, 20) + "...";
	      }
	      String method = request.getMethod();
	      String path = request.getRequestURI();
	      String query = request.getQueryString();
	      
	      sb.append("[" + method + " " + path);
	      if (query != null) {
	        sb.append("?" + query + "]");
	      } else {
	        sb.append("]");
	      }
	      sb.append(" >>> [" + userIp + "][" + userAgent + "]");
	    }
	    return sb.toString();
	  }
	  
	  public String getIpFromRequest(HttpServletRequest request)
	  {
	    String userIp = request.getHeader("x-forwarded-for");
	    if ((userIp == null) || (userIp.trim().equals(""))) {
	      userIp = request.getRemoteAddr();
	    }
	    userIp.indexOf(':');
	    if (userIp.indexOf(',') != -1)
	    {
	      String separateIp = userIp.substring(0, userIp.indexOf(','));
	      
	      userIp = separateIp;
	    }
	    return userIp;
	  }
	  
	  
	  public String getSimpleErrRes(GenException ex)
	  {
	    JSONObject errJson = new JSONObject();
	    errJson.put("status", ex.getErrorCode());
	    errJson.put("message", ex.getMessage());
	    return errJson.toString();
	  }
	  
	  public String getSimpleErrRes(Exception ex)
	  {
	    JSONObject errJson = new JSONObject();
	    errJson.put("status", 500);
	    errJson.put("message", ex.getMessage());
	    return errJson.toString();
	  }
	  
}
