package tom.mediabc.search.catalog;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.ResponseBody;

import com.fasterxml.jackson.databind.ObjectMapper;

import tom.common.basic.BasicController;
import tom.common.basic.ObjectMapperInstance;
import tom.common.configuration.LoggerName;


@Controller
public class ManagerController extends BasicController {

	
	private Logger log = LoggerFactory.getLogger(LoggerName.SVC);
	
	@Autowired
	private UserServiceImpl userService;
	
	@RequestMapping(value="login/sign", method={RequestMethod.GET})
	public String signIn(Model model, HttpServletRequest request) {
		return "login/sign";
	}
	
	@RequestMapping(value="login/logout", method={RequestMethod.GET})
	public String logout(Model model, HttpServletRequest request) {
		request.getSession().invalidate();
		return "redirect:/catalog/login/sign.do";
	}
	
	
	
	
	@RequestMapping(
			value= "login/ajax_sign_in_proc", 
			method={RequestMethod.POST}, 
			produces="application/json;charset=utf-8")
	public @ResponseBody ResponseEntity<String> ajaxSignInProc(
			Model model, 
			HttpServletRequest request,
			HttpServletResponse response) {
		
		long startTime = System.currentTimeMillis();
		int retSize = 0;
		int tid = genTid();
		String resString = "{}";
		HttpStatus resStatus = HttpStatus.OK;
		ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
		
		try {
			log.debug("[" + tid + "].......... Start (" + genReqInfo(request) + ")  ..........");
			
			byte[] reqByte = getInputStreamToByte(request.getInputStream());
			LoginInfo loginInfo = mapper.readValue(reqByte, LoginInfo.class);
			
			log.debug("[" + tid + "] " + loginInfo);
			
			LoginResponse res = userService.login(tid, loginInfo, request);
			
			
			resString = mapper.writeValueAsString(res);
			

		} catch (Exception e) {
			resStatus = HttpStatus.INTERNAL_SERVER_ERROR;
			response.setHeader(XSTATUS_CODE, "500");
			response.setHeader(XSTATUS_REASON, e.getMessage());
			
			resString = getSimpleErrRes(e);
			log.error("["+tid+"] status["+resStatus+"] ["+e+"]", e);	
		} finally {
			log.debug("["+tid+"].......... End retSize("+retSize+") execTime("+(System.currentTimeMillis() - startTime)+")ms..........");
		}
		
		return new ResponseEntity<String>(resString, resStatus);
	}
	
	
	
	
	
	@RequestMapping(value="original/list", method={RequestMethod.GET})
	public String oriliginalList(Model model, HttpServletRequest request) {
		return "original/list";
	}
	
	@RequestMapping(value="original/detail", method={RequestMethod.GET})
	public String oriliginalDetail(Model model, HttpServletRequest request) {
		return "original/detail";
	}
	
	@RequestMapping(value="original/analytic", method={RequestMethod.GET})
	public String oriliginalAnalytic(Model model, HttpServletRequest request) {
		return "original/analytic";
	}
	
	
	@RequestMapping(value="service/list", method={RequestMethod.GET})
	public String serviceList(Model model, HttpServletRequest request) {
		return "service/list";
	}
	
	@RequestMapping(value="service/detail", method={RequestMethod.GET})
	public String serviceDetail(Model model, HttpServletRequest request) {
		return "service/detail";
	}
	
	@RequestMapping(value="service/analytic", method={RequestMethod.GET})
	public String serviceAnalytic(Model model, HttpServletRequest request) {
		return "service/analytic";
	}
	
}
