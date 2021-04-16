package tom.mediabc.search.restapi;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import tom.common.configuration.LoggerName;
import tom.mediabc.search.dao.RegisterQueueDAOImpl;
import tom.mediabc.search.vo.dao.RegisterQueueVO;

@Service("mediaBCService")
public class MediaBCServiceImpl {

	private Logger log = LoggerFactory.getLogger(LoggerName.INDEX_BATCH);
	
	@Autowired
	private RegisterQueueDAOImpl registerQueueDAO;
	
	
	
	
	public void deleteCc(String ccid) {
		
	}
	
	public void deleteCc(String ccid, String version) {
		
		
		
		
	}
	
	
	
	
	
	public MediaBcResponseVO registerEvent(int tid, MediaBcRegEventVO regEvent) {
		
		MediaBcResponseVO res = new MediaBcResponseVO();
		try {
			RegisterQueueVO reqQueue = new RegisterQueueVO();
			reqQueue.setCcid(regEvent.getCcid());
			reqQueue.setVersion(regEvent.getVersion());
			reqQueue.setCategory1(regEvent.getCategory1());
			reqQueue.setCategory2(regEvent.getCategory2());
			reqQueue.setOwnerId(regEvent.getAccountid());
			reqQueue.setOwnerRegDate(regEvent.getRegAsDate());
			reqQueue.setJobProc(RegisterQueueVO.JOB_STANDBY);
			reqQueue.setJobProcStatus(RegisterQueueVO.STATUS_SUCCESS);
			reqQueue.setJobType(regEvent.getMode());
			
			
			registerQueueDAO.insertRegisterQueue(reqQueue);	
			
			
			
			
			res.setResult(0);
			res.setDesc("Success");
		} catch (Exception e) {
			log.error("["+tid+"] registerEvent error ", e);
			res.setResult(500);
			res.setDesc("Error " + e.getMessage());
		}
		
		
		return res;
	}
}
