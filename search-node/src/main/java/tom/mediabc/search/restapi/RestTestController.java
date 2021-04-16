package tom.mediabc.search.restapi;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.json.JSONObject;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.ResponseBody;

import tom.common.basic.BasicController;
import tom.common.configuration.LoggerName;


@Controller
public class RestTestController extends BasicController {
	
	//private static final String URL_PREFIX = "search";
	private static final String VERSION = "v1";
	private Logger log = LoggerFactory.getLogger(LoggerName.SVC);

	@RequestMapping(
			value = { VERSION + "/test" }, 
			method = {RequestMethod.POST }, 
			produces = "application/json;charset=utf-8")
	@ResponseBody
	public ResponseEntity<String> search(Model model, HttpServletRequest request, HttpServletResponse response,
			@ModelAttribute SearchParam sParam) {
		long startTime = System.currentTimeMillis();
		int retSize = 0;
		int tid = genTid();
		String resString = "{}";
		HttpStatus resStatus = HttpStatus.OK;
		try {
			log.debug("[" + tid + "].......... Start (" + genReqInfo(request) + ")  ..........");
			
			
			byte[] buf = getInputStreamToByte(request.getInputStream());
			JSONObject jsonObj = new JSONObject(new String(buf, "UTF-8"));
			log.debug("["+tid+"] " + jsonObj.toString(4) ) ;
			
			
			JSONObject jsonRes = new JSONObject();
			jsonRes.put("jsonrpc", "2.0");
			jsonRes.put("result", "0xd46e8dd67c5d32be8d46e8dd67c5d32be80");
			
			resString = jsonRes.toString();
		
		} catch (Exception e) {
			resStatus = HttpStatus.INTERNAL_SERVER_ERROR;
			response.setHeader("X-Status-Code", "500");
			response.setHeader("X-Status-Reason", e.getMessage());

			log.error("[" + tid + "] status[" + resStatus + "] [" + e + "]", e);
		} finally {
			log.debug("[" + tid + "].......... End retSize(" + retSize + ") execTime(" + (System.currentTimeMillis() - startTime) + ")ms..........");
		}

		return new ResponseEntity<String>(resString, resStatus);
	}
	
	
	
	
}
