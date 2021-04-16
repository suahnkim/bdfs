package tom.mediabc.search.restapi;

import java.net.URLDecoder;
import java.util.Iterator;

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
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import com.fasterxml.jackson.databind.ObjectMapper;

import tom.common.basic.BasicController;
import tom.common.basic.ObjectMapperInstance;
import tom.common.configuration.LoggerName;
import tom.common.util.KeyStringValue;
import tom.mediabc.search.core.ESManagerForMovieMetaV1;
import tom.mediabc.search.vo.cc.BasicMetaVO;


@Controller
public class RestApiController extends BasicController {
	
	//private static final String URL_PREFIX = "search";
	//private static final String VERSION = "v1";
	private Logger log = LoggerFactory.getLogger(LoggerName.SVC);

	@Autowired
	private RestApiServiceImpl restApiService;
	
	
	@RequestMapping(
			value = { "/search" }, 
			method = {RequestMethod.GET }, 
			produces = "application/json;charset=utf-8")
	@ResponseBody
	public ResponseEntity<String> search(Model model, HttpServletRequest request, HttpServletResponse response,
			@ModelAttribute SearchParam sParam,
			@RequestParam(value="searchTarget", defaultValue="svc", required=false) String searchTarget) {
		long startTime = System.currentTimeMillis();
		int retSize = 0;
		int tid = genTid();
		String resString = "{}";
		HttpStatus resStatus = HttpStatus.OK;
		try {
			log.debug("[" + tid + "].......... Start (" + genReqInfo(request) + ")  ..........");
			
			@SuppressWarnings("unchecked")
			Iterator<String> iter = request.getParameterMap().keySet().iterator();
			
			while(iter.hasNext()) {
				String key = iter.next();
				if(key.startsWith("sk_")) {
					String newKey = key.substring(3);
					String value = request.getParameter(key);
					sParam.addSearchField(new KeyStringValue(newKey, value));	
				}
			}
			String indexName = ESManagerForMovieMetaV1.INDEX_SVC;
			if(searchTarget.equals("ori")) {
				indexName = ESManagerForMovieMetaV1.INDEX_ORI;
			}
			log.debug("PARAM ["+sParam+"]");
			SearchResponseVO searchRes = restApiService.searchCC(tid, indexName, sParam);
			
			
			

			ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
			resString = mapper.writeValueAsString(searchRes);
		
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
	
	
	
	
	
	
	
	@RequestMapping(
			value = { "/ccontent/{ccid}/{version}/**" }, 
			method = {RequestMethod.GET }
			//, produces = "application/json;charset=utf-8"
			)
	@ResponseBody
	public void content(Model model, HttpServletRequest request, HttpServletResponse response,
			@PathVariable String ccid, @PathVariable String version) {
		long startTime = System.currentTimeMillis();
		int retSize = 0;
		int tid = genTid();
		//String resString = "{}";
		HttpStatus resStatus = HttpStatus.OK;
		try {
			log.debug("[" + tid + "].......... Start (" + genReqInfo(request) + ")  ..........");
			//String servletPath = request.getServletPath();
			String servletPath = request.getRequestURI();
			//log.debug("[" + tid + "] getRequestURI ["+request.getRequestURI()+"]  ..........");
			
			String prefix = "/ccsearch/v1/ccontent/" + ccid + "/" + version + "/";
			String ccFilePath = null;
			if(servletPath.length() > prefix.length()) {
				ccFilePath = servletPath.substring(prefix.length());	
			}
			
			
			log.debug("["+tid+"]  -------- enc ["+ccid+"]["+version+"]["+ccFilePath+"]");
			if(ccFilePath != null) {
				ccFilePath = URLDecoder.decode(ccFilePath, "UTF-8");
			}
			log.debug("["+tid+"]  -------- dec ["+ccid+"]["+version+"]["+ccFilePath+"]");
			
			restApiService.readCC(tid, response, ccid, version, ccFilePath);
			
			
		} catch (Exception e) {
			resStatus = HttpStatus.INTERNAL_SERVER_ERROR;
			response.setHeader("X-Status-Code", "500");
			response.setHeader("X-Status-Reason", e.getMessage());

			log.error("[" + tid + "] status[" + resStatus + "] [" + e + "]", e);
		} finally {
			log.debug("[" + tid + "].......... End retSize(" + retSize + ") execTime(" + (System.currentTimeMillis() - startTime) + ")ms..........");
		}

		//return new ResponseEntity<String>(resString, resStatus);
	}
	
	
	
	
	
	
	
	@RequestMapping(
			value = { "/transfer_cc_status" }, 
			method = {RequestMethod.POST }, 
			produces = "application/json;charset=utf-8")
	@ResponseBody
	public ResponseEntity<String> transferCcStatus(Model model, HttpServletRequest request, HttpServletResponse response) {
		long startTime = System.currentTimeMillis();
		int retSize = 0;
		int tid = genTid();
		String resString = "{}";
		HttpStatus resStatus = HttpStatus.OK;
		try {
			log.debug("[" + tid + "].......... Start (" + genReqInfo(request) + ")  ..........");
			ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
			
			byte[] buf = getInputStreamToByte(request.getInputStream());
			CcStatusUpdateRequestVO updateReq = mapper.readValue(buf, CcStatusUpdateRequestVO.class);
			
			log.debug("["+tid+"] " + updateReq);
			restApiService.updateCcStatus(tid, updateReq);
			
			
			resString = mapper.writeValueAsString(updateReq);
		
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
	
	
	
	
	
	@RequestMapping(
			value = { "/group_by_count" }, 
			method = {RequestMethod.GET }, 
			produces = "application/json;charset=utf-8")
	@ResponseBody
	public ResponseEntity<String> groupByCount(Model model, 
			HttpServletRequest request, 
			HttpServletResponse response,
			@RequestParam String index,
			@RequestParam String key) {
		long startTime = System.currentTimeMillis();
		int retSize = 0;
		int tid = genTid();
		String resString = "{}";
		HttpStatus resStatus = HttpStatus.OK;
		try {
			log.debug("[" + tid + "].......... Start (" + genReqInfo(request) + ")  ..........");
			ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
			
			GroupByResponseVO groupRes = restApiService.groupByCount(tid, index, key);
			
			resString = mapper.writeValueAsString(groupRes);
		
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
	
	
	
	
	
	@RequestMapping(
			value = { "/update_cc_metadata" }, 
			method = {RequestMethod.POST }, 
			produces = "application/json;charset=utf-8")
	@ResponseBody
	public ResponseEntity<String> updateCcMetadata(Model model, HttpServletRequest request, HttpServletResponse response) {
		long startTime = System.currentTimeMillis();
		int retSize = 0;
		int tid = genTid();
		String resString = "{}";
		HttpStatus resStatus = HttpStatus.OK;
		
		
		try {
			log.debug("[" + tid + "].......... Start (" + genReqInfo(request) + ")  ..........");
			ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
			
			
			byte[] buf = getInputStreamToByte(request.getInputStream());
			//String reqMetaContainer = new String(buf, "UTF-8");
			BasicMetaVO basicMeta = mapper.readValue(buf, BasicMetaVO.class);
			
			DefResponseVO defRes = restApiService.updateCcMetadata(tid, basicMeta);
			
			

			resString = mapper.writeValueAsString(defRes);
		
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
	
	
	@RequestMapping(
			value = { "/upload_service_image" }, 
			method = {RequestMethod.POST }, 
			produces = "application/json;charset=utf-8")
	@ResponseBody
	public ResponseEntity<String> uploadServiceImage(Model model, HttpServletRequest request, HttpServletResponse response) {
		long startTime = System.currentTimeMillis();
		int retSize = 0;
		int tid = genTid();
		String resString = "{}";
		HttpStatus resStatus = HttpStatus.OK;
		
		
		try {
			log.debug("[" + tid + "].......... Start (" + genReqInfo(request) + ")  ..........");
			ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
			
			
			byte[] buf = getInputStreamToByte(request.getInputStream());
			//String reqMetaContainer = new String(buf, "UTF-8");
			UploadServiceImageRequest uploadReq = mapper.readValue(buf, UploadServiceImageRequest.class);
			
			restApiService.uploadServiceImage(tid, uploadReq);
			
			

			resString = mapper.writeValueAsString(uploadReq);
		
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
