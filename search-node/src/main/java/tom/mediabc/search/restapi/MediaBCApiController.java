package tom.mediabc.search.restapi;

import java.text.SimpleDateFormat;
import java.util.Date;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.ResponseBody;

import com.fasterxml.jackson.databind.ObjectMapper;

import tom.common.basic.BasicController;
import tom.common.basic.BasicResponse;
import tom.common.basic.ObjectMapperInstance;
import tom.common.configuration.LoggerName;


@Controller
public class MediaBCApiController extends BasicController {
	
	private Logger log = LoggerFactory.getLogger(LoggerName.SVC);

	@Autowired
	private MediaBCServiceImpl mediaBCService;
	
	
	@RequestMapping(
			value = { "/content/register" }, 
			method = {RequestMethod.GET }, 
			produces = "application/json;charset=utf-8")
	@ResponseBody
	public ResponseEntity<String> register(Model model, HttpServletRequest request, HttpServletResponse response,
			@ModelAttribute MediaBcRegEventVO regEventReq) {
		long startTime = System.currentTimeMillis();
		int retSize = 0;
		int tid = genTid();
		String resString = "{}";
		HttpStatus resStatus = HttpStatus.OK;
		try {
			log.debug("[" + tid + "].......... Start (" + genReqInfo(request) + ")  ..........");
			
			
			if(regEventReq.getRegAsDate() == null) {
				SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss");
				regEventReq.setReg(sdf.format(new Date()));
			}
			
			ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
			MediaBcResponseVO regEventRes = null;
			if(regEventReq.getFlag() != null && regEventReq.getFlag().trim().equals(MediaBcRegEventVO.FLAG_TRUE)) {
				
				if(regEventReq.getMode() != null && 
						(regEventReq.getMode().equals(MediaBcRegEventVO.MODE_INSERT) ||
						regEventReq.getMode().equals(MediaBcRegEventVO.MODE_MODIFY) ||
						regEventReq.getMode().equals(MediaBcRegEventVO.MODE_DELETE))) {
					regEventRes = mediaBCService.registerEvent(tid, regEventReq);	

					
				} else {
					
					regEventRes = new MediaBcResponseVO();
					regEventRes.setResult(1);
					regEventRes.setDesc("invalid mode["+regEventReq.getMode()+"]");
				}
				
			} else if(regEventReq.getFlag() != null && regEventReq.getFlag().trim().equals(MediaBcRegEventVO.FLAG_FALSE)) {
				//TODO... 삭제..
				regEventReq.setMode(MediaBcRegEventVO.MODE_DELETE);
				regEventRes = mediaBCService.registerEvent(tid, regEventReq);	
				
				
			} else {
				log.debug("["+tid+"] INVALID FALG");
				
				regEventRes = new MediaBcResponseVO();
				regEventRes.setResult(1);
				regEventRes.setDesc("invalid falg["+regEventReq.getFlag()+"]");
			}
			
			
			resString = mapper.writeValueAsString(regEventRes);	
			
			
			
		
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
