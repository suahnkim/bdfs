package tom.mediabc.search.batch;

import java.io.File;
import java.util.Date;
import java.util.Random;
import java.util.concurrent.TimeUnit;

import org.apache.commons.pool.impl.GenericObjectPool;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import tom.common.configuration.Configuration;
import tom.common.configuration.LoggerName;
import tom.common.util.ObjectTicket;
import tom.mediabc.search.dao.RegisterQueueDAOImpl;
import tom.mediabc.search.vo.dao.RegisterQueueVO;

public class CCDownloadThread extends Thread{

	private Logger log = LoggerFactory.getLogger(LoggerName.INDEX_BATCH);
	private RegisterQueueVO queueVO;
	private RegisterQueueDAOImpl registerQueueDAO;
	
	
	
	
	public CCDownloadThread(RegisterQueueDAOImpl registerQueueDAO, RegisterQueueVO queueVO) {
		this.registerQueueDAO = registerQueueDAO;
		this.queueVO = queueVO;
	}
	
	
	
	public void run() {
		Configuration config = Configuration.getInstance();
		
		
		GenericObjectPool<ObjectTicket> indexerPool = CCIndexerPoolWrapper.getInstance().getObjectPool();
		ObjectTicket ticket = null;
		int tid = (new Random()).nextInt(1000000);
		try {
			ticket = indexerPool.borrowObject();
			log.debug("["+tid+"] ======== Start Downloader["+queueVO.getCcid()+"]["+queueVO.getVersion()+"] ========");
			log.debug("["+tid+"] Apply Download timeout.");
			
			queueVO.setJobProc(RegisterQueueVO.JOB_DOWNLOAD);
			queueVO.setJobDownloadStart(new Date());
			registerQueueDAO.updateRegisterQueue(queueVO);
			
			
			
			String ccid    = queueVO.getCcid();
			String version = queueVO.getVersion();
			String ccBasePath = config.getStringExtra("cc.basedir");
			String ipfsPath   = config.getStringExtra("ipfs.path");
			
			File workingDir = new File(ccBasePath + "/download/"+ ccid + "_" + version);
			workingDir.mkdirs();
			
			{
				String[] cmd = {
						ipfsPath, 
						"ccget",
						"/ccfs/"+ccid+"/" + version + "/manifest.json",
						"-p",
						"0",
						"-o",
						workingDir.getAbsolutePath()
						};
				String[] cmdOri = {
						ipfsPath, 
						"get",
						version + "/manifest.json",
						"-o",
						workingDir.getAbsolutePath()
						};
				
				Process currProc = Runtime.getRuntime().exec(cmd, null, workingDir);
				//Process currProc = Runtime.getRuntime().exec(cmdOri, null, workingDir);
				(new StreamConsumer(currProc.getInputStream())).start();
				(new StreamConsumer(currProc.getErrorStream())).start();
				//int returnCode = currProc.waitFor();
				boolean isDone = currProc.waitFor(60, TimeUnit.SECONDS);
				log.debug("["+tid+"] DONWLOAD CC is DONE  ["+isDone+"]");
				
				if(isDone == false) {
					if(currProc.isAlive()) {
						currProc.destroyForcibly();
					}
					//isDone 이 false 이면 프로세스 죽여야함...
					throw new Exception("process hasn't exited");
				}
				
				int returnCode = currProc.exitValue();
				log.debug("["+tid+"] DONWLOAD CC["+version+"/manifest.json] ["+returnCode+"]");
				
				if(returnCode != 0) {
					throw new Exception("return code is not 0. ["+returnCode+"]");
				}
			}
			{
				String[] cmd = {
						ipfsPath, 
						"ccget",
						"/ccfs/"+ccid+"/" + version + "/basicMeta",
						"-p",
						"0",
						"-o",
						workingDir.getAbsolutePath() + "/basicMeta"
						};
				
				String[] cmdOri = {
						ipfsPath, 
						"get",
						version + "/basicMeta",
						"-o",
						workingDir.getAbsolutePath() + "/basicMeta"
						};
				
				Process currProc = Runtime.getRuntime().exec(cmd, null, workingDir);
				//Process currProc = Runtime.getRuntime().exec(cmdOri, null, workingDir);
				(new StreamConsumer(currProc.getInputStream())).start();
				(new StreamConsumer(currProc.getErrorStream())).start();
				//int returnCode = currProc.waitFor();
				boolean isDone = currProc.waitFor(60 * 10, TimeUnit.SECONDS);
				log.debug("["+tid+"] DONWLOAD CC is DONE  ["+isDone+"]");
				
				if(isDone == false) {
					if(currProc.isAlive()) {
						currProc.destroyForcibly();
					}
					//isDone 이 false 이면 프로세스 죽여야함...
					throw new Exception("process hasn't exited");
				}
				int returnCode = currProc.exitValue();
				log.debug("["+tid+"] DONWLOAD CC["+version+"/basicMeta] ["+returnCode+"]");
				
				if(returnCode != 0) {
					throw new Exception("return code is not 0. ["+returnCode+"]");
				}
			}
			
			
			/*
			File ccPath = new File(workingDir + "/" + version);
			File renameCcPath = new File(workingDir + "/" + ccid + "_" + version);
			boolean rename = ccPath.renameTo(renameCcPath);
			log.debug("["+tid+"] RENAME CC Path ["+rename+"]");
			*/
			
			
			
			queueVO.setJobProgress("Success");
			queueVO.setJobProcStatus(RegisterQueueVO.STATUS_SUCCESS);
			queueVO.setJobProc(RegisterQueueVO.JOB_DOWNLOAD_DONE);
			queueVO.setJobDownloadEnd(new Date());
			registerQueueDAO.updateRegisterQueue(queueVO);
			
		} catch (Exception e) {
			log.error("["+tid+"] download error " + e.getMessage(), e);
			try {
				queueVO.setJobProcStatus(RegisterQueueVO.STATUS_ERROR);
				queueVO.setJobProgress(e.getMessage());
				registerQueueDAO.updateRegisterQueue(queueVO);	
			} catch (Exception ex) {
				log.error("["+tid+"] ", ex);
			}
			
			
			//ERROR 로그 관련내용 기록...
			
			
		} finally {
			if(ticket != null) {
				try {
					indexerPool.returnObject(ticket);
				} catch (Exception e) {
					log.error("["+tid+"]Object return error " + e.getMessage(), e);
				}
			}
			
			log.debug("["+tid+"] ===============================");
		}
	}
	
}
