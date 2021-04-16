package tom.mediabc.search.batch;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Random;

import org.apache.commons.pool.impl.GenericObjectPool;
import org.apache.http.message.BasicHeader;
import org.elasticsearch.action.delete.DeleteResponse;
import org.json.JSONObject;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.fasterxml.jackson.databind.ObjectMapper;

import tom.common.basic.ObjectMapperInstance;
import tom.common.configuration.Configuration;
import tom.common.configuration.LoggerName;
import tom.common.util.GenException;
import tom.common.util.HttpRequestor;
import tom.common.util.ObjectTicket;
import tom.mediabc.search.core.ESManagerForMovieMetaV1;
import tom.mediabc.search.dao.CCDAOImpl;
import tom.mediabc.search.dao.RegisterQueueDAOImpl;
import tom.mediabc.search.restapi.MediaBcRegEventVO;
import tom.mediabc.search.vo.cc.BasicMetaVO;
import tom.mediabc.search.vo.cc.EsCContentVO;
import tom.mediabc.search.vo.cc.FileInfoVO;
import tom.mediabc.search.vo.cc.ManifestVO;
import tom.mediabc.search.vo.dao.CContentFileVO;
import tom.mediabc.search.vo.dao.CContentVO;
import tom.mediabc.search.vo.dao.MetadataFileVO;
import tom.mediabc.search.vo.dao.MetadataVO;
import tom.mediabc.search.vo.dao.RegisterQueueVO;

public class CCIndexerThread extends Thread{

	private Logger log = LoggerFactory.getLogger(LoggerName.INDEX_BATCH);
	private RegisterQueueVO queueVO;
	private RegisterQueueDAOImpl registerQueueDAO;
	private CCDAOImpl ccDAO;
	
	
	public CCIndexerThread(RegisterQueueDAOImpl registerQueueDAO, CCDAOImpl ccDAO, RegisterQueueVO queueVO) {
		this.registerQueueDAO = registerQueueDAO;
		this.ccDAO = ccDAO;
		this.queueVO = queueVO;
	}
	
	
	public void run() {
		
		
		
		
		//TODO.. 삭제 수정 업데이트 로직 수행...
		
		
		
		GenericObjectPool<ObjectTicket> indexerPool = CCIndexerPoolWrapper.getInstance().getObjectPool();
		ObjectTicket ticket = null;
		int tid = (new Random()).nextInt(1000000);
		try {
			ticket = indexerPool.borrowObject();
			log.debug("["+tid+"] ======== Start Indexer["+queueVO.getCcid()+"]["+queueVO.getVersion()+"] ========");
			
			queueVO.setJobProc(RegisterQueueVO.JOB_INDEXING);
			queueVO.setJobIndexStart(new Date());
			registerQueueDAO.updateRegisterQueue(queueVO);
			
			
			if(MediaBcRegEventVO.MODE_INSERT.equals(queueVO.getJobType())) {
				writeEsIndex(tid);
				
				sendIndexingComplateNotify(tid, queueVO.getCcid(), queueVO.getVersion());
				
				
			} else if(MediaBcRegEventVO.MODE_MODIFY.equals(queueVO.getJobType())) {
				//TODO. 해당 ccid의 다른 버전 삭제.
				
				List<String> allVersion = ccDAO.selectAllVersionByCcid(queueVO.getCcid());
				for(String version : allVersion) {
					cleanDbAndES(tid, queueVO.getCcid(), version);
				}
				writeEsIndex(tid);
				
				sendIndexingComplateNotify(tid, queueVO.getCcid(), queueVO.getVersion());
				
			} else if(MediaBcRegEventVO.MODE_DELETE.equals(queueVO.getJobType())) {
				//TODO. 해당 ccid-version 삭제..
				
				cleanDbAndES(tid, queueVO.getCcid(), queueVO.getVersion());
			}
			
			
			
			
			
			queueVO.setJobProgress("Success");
			queueVO.setJobProcStatus(RegisterQueueVO.STATUS_SUCCESS);
			queueVO.setJobProc(RegisterQueueVO.JOB_INDEXING_DONE);
			queueVO.setJobIndexEnd(new Date());
			registerQueueDAO.updateRegisterQueue(queueVO);
			
		} catch (Exception e) {
			log.error("["+tid+"]indexing error " + e.getMessage(), e);
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
	
	
	
	private void sendIndexingComplateNotify(int tid, String ccid, String version) throws Exception {
		
		String sendUrl = Configuration.getInstance().getStringExtra("download_complate_url");
		try {
			JSONObject payloadJson = new JSONObject();
			payloadJson.put("ccid",    ccid);
			payloadJson.put("version", version);
			payloadJson.put("sflag",   true);
			
			
			log.debug("REQUEST ["+tid+"] ["+sendUrl+"] ["+payloadJson.toString()+"]");
			
			HttpRequestor httpReq = new HttpRequestor(sendUrl, HttpRequestor.HTTP_POST);
			httpReq.addHeader(new BasicHeader("Content-Type", "application/json"));
			httpReq.setRequestData(payloadJson.toString().getBytes());
			
			
			int status =  httpReq.request();
			byte[] resBoby = httpReq.getResponseBody();
			String resBodyStr = new String(resBoby);
			log.debug("RESPONSE ["+tid+"] status["+status+"] ["+sendUrl+"] ["+resBodyStr+"]");
			
			if(status == 200) {
				//PASS
				JSONObject resObj = new JSONObject(resBodyStr);
				int resultCode = resObj.getInt("resultCode");
				if(resultCode == 0) {
					//PASS
				} else {
					throw new Exception("interface resultCode is not okay["+resultCode+"]");
				}
				
				
			} else {
				//FAIL
				throw new Exception("Http Status is NOT Okay["+status+"]");
			}
			
			
			
		} catch (Exception e) {
			throw new Exception("Send Complate notification error["+sendUrl+"] " + e.getMessage(), e);
		}
	}
	
	private void writeEsIndex(int tid) throws IOException, GenException, ParseException {
		
		Configuration config = Configuration.getInstance();
		
		String ccid    = queueVO.getCcid();
		String version = queueVO.getVersion();
		String ccBasePath = config.getStringExtra("cc.basedir");
		
		
		String ccPath = ccBasePath + "/download/" + ccid +"_" + version;
		String manifestPath = ccPath + "/manifest.json";
		
		log.debug("["+tid+"] ccBase Path ["+ccPath+"]");
		
		byte[] bytes = Files.readAllBytes(Paths.get(manifestPath));
		ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
		ManifestVO manifestVO = mapper.readValue(bytes, ManifestVO.class);
		
		
		
		
		cleanDbAndES(tid, ccid, version);
		
		//log.debug("["+tid+"] ["+queueVO+"]");
		
		ArrayList<BasicMetaVO> metaContainerArr = insertDataBase(tid, queueVO.getCcid(), queueVO.getVersion(), manifestVO);
		CContentVO ccontent = ccDAO.selectCcontentCCidByCCidVersion(ccid, version);
		
		
		EsCContentVO esContent = new EsCContentVO();
		esContent.setCcid(ccid);
		esContent.setVersion(version);
		//esContent.setStatus(CContentVO.STATUS_INDEX);
		//TODO.. 일단 등록하자마자 검색 될수 있게 처리 
		esContent.setStatus(CContentVO.STATUS_SERVICE);
		esContent.setOwnerId(ccontent.getOwnerId());
		esContent.setOwnerRegDateAsDate(ccontent.getOwnerRegDate());
		esContent.setMetaContainer(metaContainerArr);
		
		
		ESManagerForMovieMetaV1.getInstance().createOtUpdateMeta(ESManagerForMovieMetaV1.INDEX_ORI, esContent);
		ESManagerForMovieMetaV1.getInstance().createOtUpdateMeta(ESManagerForMovieMetaV1.INDEX_SVC, esContent);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	private ArrayList<BasicMetaVO> insertDataBase(int tid, String ccid, String version, ManifestVO manifestVO) throws GenException, IOException {
		log.debug("["+tid+"] manifestVO ["+manifestVO+"]");
		
		ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
		
		Configuration config = Configuration.getInstance();
		String ccBasePath = config.getStringExtra("cc.basedir");
		String ccPath = ccBasePath + "/download/" + ccid +"_" + version;
		
		
		List<FileInfoVO> basicMetaArr  = manifestVO.getBasicMeta();
		List<FileInfoVO> extMetaArr    = manifestVO.getExtendedMeta();
		List<FileInfoVO> contentArr    = manifestVO.getContents();
		List<FileInfoVO> derContentArr = manifestVO.getDerivedContents();
		
		CContentVO ccontentVO = new CContentVO(ccid, version, CContentVO.STATUS_INDEX);
		ccontentVO.setOwnerId(queueVO.getOwnerId());
		ccontentVO.setOwnerRegDate(queueVO.getOwnerRegDate());
		
		
		ArrayList<MetadataFileVO> metaFilesArr = new ArrayList<MetadataFileVO>();
		ArrayList<CContentFileVO> contentFilesArr = new ArrayList<CContentFileVO>();
		ArrayList<MetadataVO>     metaDataArr = new ArrayList<MetadataVO>();
		ArrayList<BasicMetaVO>    metaContainerArr = new ArrayList<BasicMetaVO>();
		
		
		for(int i=0; i<basicMetaArr.size(); i++) {
			MetadataFileVO metaFile = new MetadataFileVO();
			metaFile.setCcid(ccid);
			metaFile.setVersion(version);
			metaFile.setMetaPath(basicMetaArr.get(i).getPath());
			metaFile.setMetaType(basicMetaArr.get(i).getType());
			metaFile.setMetaSize(basicMetaArr.get(i).getSize());
			metaFile.setMetaClass(CContentVO.CLASS_BASIC);
			metaFile.setFileStatus(MetadataFileVO.STATUS_INIT);
			metaFilesArr.add(metaFile);
			
			if(basicMetaArr.get(i).getType().equals("manifest")) {
				
				String metaFullPath = ccPath + "/" + basicMetaArr.get(i).getPath();
				byte[] metaBytes = Files.readAllBytes(Paths.get(metaFullPath));
				//String metaJsonStr = new String(metaBytes, "UTF-8");
				BasicMetaVO metaContainer = mapper.readValue(metaBytes, BasicMetaVO.class);
				//JSONObject metaJson = new JSONObject(metaJsonStr);
				
				if(metaContainer.getTitle() == null) {
					metaContainer.setTitle(metaContainer.getMetadata().getTitle());
				}
				
				
				MetadataVO metaData = new MetadataVO();
				metaData.setCcid(ccid);
				metaData.setVersion(version);
				metaData.setMetaPath(basicMetaArr.get(i).getPath());
				metaData.setMetadataOriginal(new String(metaBytes, "UTF-8"));
				metaData.setMetadataService(new String(metaBytes, "UTF-8"));
				metaData.setMetaType(metaContainer.getMetaType());
				metaData.setMetaClass(CContentVO.CLASS_BASIC);
				metaData.setTitle(metaContainer.getTitle());
				metaData.setContentType(metaContainer.getContentType());
				metaDataArr.add(metaData);
				
				
				metaContainer.setMetaClass(CContentVO.CLASS_BASIC);
				metaContainerArr.add(metaContainer);
				
			}
		}
		
		for(int i=0; extMetaArr !=null && i<extMetaArr.size(); i++) {
			MetadataFileVO metaFile = new MetadataFileVO();
			metaFile.setCcid(ccid);
			metaFile.setVersion(version);
			metaFile.setMetaPath(extMetaArr.get(i).getPath());
			metaFile.setMetaType(extMetaArr.get(i).getType());
			metaFile.setMetaSize(extMetaArr.get(i).getSize());
			metaFile.setMetaClass(CContentVO.CLASS_EXTENSION);
			metaFile.setFileStatus(MetadataFileVO.STATUS_INIT);
			metaFilesArr.add(metaFile);
			
			if(extMetaArr.get(i).getType().equals("manifest")) {
				
				String metaFullPath = ccPath + "/" + extMetaArr.get(i).getPath();
				byte[] metaBytes = Files.readAllBytes(Paths.get(metaFullPath));
				//String metaJsonStr = new String(metaBytes, "UTF-8");
				//JSONObject metaJson = new JSONObject(metaJsonStr);
				BasicMetaVO metaContainer = mapper.readValue(metaBytes, BasicMetaVO.class);
				
				MetadataVO metaData = new MetadataVO();
				metaData.setCcid(ccid);
				metaData.setVersion(version);
				metaData.setMetaPath(extMetaArr.get(i).getPath());
				metaData.setMetadataOriginal(new String(metaBytes, "UTF-8"));
				metaData.setMetaType(metaContainer.getMetaType());
				metaData.setMetaClass(CContentVO.CLASS_EXTENSION);
				metaData.setTitle(metaContainer.getTitle());
				metaData.setContentType(metaContainer.getContentType());
				metaDataArr.add(metaData);
				
				
				metaContainer.setMetaClass(CContentVO.CLASS_EXTENSION);
				metaContainerArr.add(metaContainer);
				
			}
		}
		
		for(int i=0; i<contentArr.size(); i++) {
			CContentFileVO contentFile = new CContentFileVO();
			contentFile.setCcid(ccid);
			contentFile.setVersion(version);
			contentFile.setContentPath(contentArr.get(i).getPath());
			contentFile.setContentType(contentArr.get(i).getType());
			contentFile.setContentSize(contentArr.get(i).getSize());
			contentFile.setContentClass(CContentVO.CLASS_BASIC);
			contentFilesArr.add(contentFile);
		}
		for(int i=0; derContentArr!=null && i<derContentArr.size(); i++) {
			CContentFileVO contentFile = new CContentFileVO();
			//contentFile.setMetaSeq();
			contentFile.setContentPath(derContentArr.get(i).getPath());
			contentFile.setContentType(derContentArr.get(i).getType());
			contentFile.setContentSize(derContentArr.get(i).getSize());
			contentFile.setContentClass(CContentVO.CLASS_EXTENSION);
			contentFilesArr.add(contentFile);
		}
		
		
		ccDAO.registerCC(ccontentVO, metaFilesArr, contentFilesArr, metaDataArr);
		log.debug("["+tid+"] insertDataBase ..... Done");
		
		for(int i=0; i<metaDataArr.size(); i++) {
			metaContainerArr.get(i).setMetaSeq(metaDataArr.get(i).getMetaSeq()+"");
		}
		
		return metaContainerArr;
	}
	
	
	private void cleanDbAndES(int tid, String ccid, String version) throws GenException, IOException {
		CContentVO metaCCid = new CContentVO(queueVO.getCcid(), queueVO.getVersion());
		
		
		List<MetadataVO> metaList= ccDAO.selectMetadataByCcidVer(ccid, version);
		for(int i=0; i<metaList.size(); i++) {
			int metaSeq = metaList.get(i).getMetaSeq();
			DeleteResponse delResOri = ESManagerForMovieMetaV1.getInstance().delete(ESManagerForMovieMetaV1.INDEX_ORI, ccid,version);
			DeleteResponse delResSvc = ESManagerForMovieMetaV1.getInstance().delete(ESManagerForMovieMetaV1.INDEX_SVC, ccid,version);
			log.debug("["+tid+"] Delete Es by id["+metaSeq+"] " + delResOri + " " + delResSvc);
		}
		int delCount = ccDAO.deleteCC(metaCCid);
		log.debug("["+tid+"] Delete Metadata res ["+delCount+"] ");
	}
}
