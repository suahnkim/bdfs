package tom.mediabc.search.batch;

import java.util.List;

import org.apache.commons.pool.impl.GenericObjectPool;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Repository;

import tom.common.configuration.Configuration;
import tom.common.configuration.LoggerName;
import tom.common.util.ObjectTicket;
import tom.mediabc.search.dao.CCDAOImpl;
import tom.mediabc.search.dao.RegisterQueueDAOImpl;
import tom.mediabc.search.vo.dao.RegisterQueueVO;

@Repository("CCDownloadBatch")
public class CCDownloadBatch {
	
	private Logger log = LoggerFactory.getLogger(LoggerName.INDEX_BATCH);
	
	@Autowired
	private RegisterQueueDAOImpl registerQueueDAO;
	@Autowired
	private CCDAOImpl ccDAO;
	
	public void downloadNewCC() {
		
		
		if(Configuration.getInstance().isInit() == false) {
			return;
		}
		
		GenericObjectPool<ObjectTicket> indexerPool = CCDownloaderPoolWrapper.getInstance().getObjectPool();
		int activeThread = indexerPool.getNumActive();
		int maxThread = indexerPool.getMaxActive();
		int availableThread = maxThread - activeThread;
		
		//log.debug("------------------- INDEX NEW ComplexContent -------------------");
		//log.debug("availableThread["+availableThread+"] = maxThread["+maxThread+"] - activeThread["+activeThread+"]");
		
		try {
			if(availableThread > 0) {
				List<RegisterQueueVO> jobList = registerQueueDAO.selectJobByCode(RegisterQueueVO.JOB_STANDBY, availableThread);
		
				for(int i=0; i<jobList.size(); i++) {
					CCDownloadThread ccIndexerTh = new CCDownloadThread(registerQueueDAO, jobList.get(i));
					ccIndexerTh.start();
				}
				
				
				
				
			}
		} catch (Exception e) {
			log.error("ERROR " + e.getMessage(), e);
		}
		
		//log.debug("----------------------------------------------------------------");
	}
	
	
	
	
	
	
	
	
}
