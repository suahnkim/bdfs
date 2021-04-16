package tom.mediabc.search.restapi;

import java.awt.image.BufferedImage;
import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.text.DecimalFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Random;

import javax.imageio.ImageIO;
import javax.servlet.http.HttpServletResponse;

import org.elasticsearch.action.search.SearchResponse;
import org.elasticsearch.search.SearchHit;
import org.elasticsearch.search.SearchHits;
import org.elasticsearch.search.aggregations.Aggregations;
import org.elasticsearch.search.aggregations.bucket.terms.ParsedStringTerms;
import org.elasticsearch.search.aggregations.bucket.terms.Terms;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import com.fasterxml.jackson.databind.ObjectMapper;

import tom.common.basic.ObjectMapperInstance;
import tom.common.configuration.Configuration;
import tom.common.configuration.LoggerName;
import tom.common.util.GenException;
import tom.mediabc.search.core.ESManagerForMovieMetaV1;
import tom.mediabc.search.dao.CCDAOImpl;
import tom.mediabc.search.vo.cc.ArtworkFileVO;
import tom.mediabc.search.vo.cc.BasicMetaVO;
import tom.mediabc.search.vo.cc.EsCContentVO;
import tom.mediabc.search.vo.dao.CContentVO;
import tom.mediabc.search.vo.dao.MetadataFileVO;
import tom.mediabc.search.vo.dao.MetadataHistVO;
import tom.mediabc.search.vo.dao.MetadataVO;

@Service("restApiService")
public class RestApiServiceImpl {

	
	private Logger log = LoggerFactory.getLogger(LoggerName.SVC);
	
	@Autowired
	private CCDAOImpl ccDAO;
	
	
	public SearchResponseVO searchCC(int tid, String index, SearchParam sParam) {
		
		log.debug("["+tid+"] start searchCC Service " + sParam);
		SearchResponseVO searchRes = new SearchResponseVO();
		
		try {
			
			ObjectMapper mapper =  ObjectMapperInstance.getInstance().getMapper();
			
			SearchResponse sRes = ESManagerForMovieMetaV1.getInstance().search(tid, index, sParam);
			SearchHits sHits = sRes.getHits();
			
			PaginationVO pagination = new PaginationVO();
			pagination.setSearchCount(sHits.getTotalHits().value);
			pagination.setRowPerPage(sParam.getRowPerPage());
			pagination.setNowPage(sParam.getNowPage());
			
			searchRes.setPagination(pagination);
			searchRes.setStatus("success");
			searchRes.setTook(sRes.getTook().getMillis());
			
			//ES 에서의 응답값 출력 
			//log.debug((new JSONObject(sRes.toString())).toString(4));
			
			ArrayList<EsCContentVO> resultArr = new ArrayList<EsCContentVO>();
			
			
			SearchHit[] searchHits = sHits.getHits();
			for(int i=0; i<searchHits.length; i++) {
				float score = searchHits[i].getScore();
				
				
				
				String sourceJson = searchHits[i].getSourceAsString();
				EsCContentVO esCcontent = mapper.readValue(sourceJson, EsCContentVO.class);
				esCcontent.setScore(score);
				//esCcontent.setStatus(null);
				resultArr.add(esCcontent);
			}
			
			searchRes.setResult(resultArr);
			
			
			
			
			return searchRes;
		} catch (Exception e) {
			log.error("["+tid+"] searchCC err ", e);
			searchRes.setStatus("error");
			return searchRes;
		}
	}
	
	
	
	public void readCC(int tid, HttpServletResponse response, String ccid, String version, String ccFilePath) {
		

		try {
			Configuration config = Configuration.getInstance();
			String ccBasePath = config.getStringExtra("cc.basedir");
			String ccPath = ccBasePath + "/download/" + ccid +"_" + version;
			
			
			
			File readFile = null;
			if(ccFilePath == null) {
				//복한컨텐츠 manifest 파일 출력
				CContentVO metaCCid = ccDAO.selectCcontentCCidByCCidVersion(ccid, version);
				if(metaCCid != null) {
					readFile = new File(ccPath + "/manifest.json");
				} else {
					log.warn("["+tid+"] not exists ["+ccid+"]["+version+"]");
					throw new GenException(GenException.IO_NOT_FOUND_FILE, "not found content ["+ccid+"/"+version+"/"+ccFilePath+"]");
				}
			} else {
				//복한컨텐츠 manifest 메타파일 출력
				MetadataFileVO metaFile = ccDAO.selectMetaFileByCCidVersionPath(ccid, version, ccFilePath);
				if(metaFile != null) {
					readFile = new File(ccPath + "/" + ccFilePath);
				} else {
					log.warn("["+tid+"] not exists ["+ccid+"]["+version+"]["+ccFilePath+"]");
					throw new GenException(GenException.IO_NOT_FOUND_FILE, "not found content ["+ccid+"/"+version+"/"+ccFilePath+"]");
				}
			}
			
			if(readFile.exists() == false) {
				//throw new Exception("not found file ["+readFile.getAbsolutePath()+"]");
				throw new GenException(GenException.IO_NOT_FOUND_FILE, "not found file ["+readFile.getAbsolutePath()+"]");
			} else {
				
				InputStream is = null;
				OutputStream os = null;
				try {
					int readNum =0;
					byte[] buf = new byte[8192];
					
					is = new BufferedInputStream(new FileInputStream(readFile));
					os = response.getOutputStream();
					response.setContentLength((int)readFile.length());
					
					while((readNum = is.read(buf)) != -1) {
						os.write(buf, 0, readNum);
					}
					os.flush();
					
					
				} catch (Exception e) {
					log.error("["+tid+"] readCC Error " , e);
				} finally {
					close(is);
					close(os);
				}
				
			}
		} catch (Exception e) {
			if(e instanceof GenException) {
				if(((GenException)e).getErrorCode() == GenException.IO_NOT_FOUND_FILE) {
					log.error("["+tid+"] " + e.getMessage());
				}
			} else {
				log.error("["+tid+"] readCC Error " , e);	
			}
			
			OutputStream os = null;
			try {
				byte[] buf = (ccid +"/" + version + "/" + ccFilePath + "  NotFound").getBytes();
				log.warn("["+tid+"] not exists ["+ccid+"]["+version+"]["+ccFilePath+"]");
				response.setStatus(HttpServletResponse.SC_NOT_FOUND);
				response.setContentLength(buf.length);
				os = response.getOutputStream();
				os.write(buf);
				os.flush();
			} catch (Exception ex) {
				log.error("["+tid+"] readCC Error " , ex);
			} finally {
				close(os);
			}
		}
	}
	
	
	
	
	
	/**
	 * 0. Status 값 검사
	 * 1. DB ccontent status 업데이트
	 * 2. ES status 값 업데이트 (original, service)
	 * */
	public void updateCcStatus(int tid, CcStatusUpdateRequestVO req) {
		
		try {
			ArrayList<CcStatus> ccStatusList = req.getCcStatusList();
			
			for(int i=0; i<ccStatusList.size(); i++) {
				CcStatus ccStatus = ccStatusList.get(i);
				if(CContentVO.MAP_STATUS.containsKey(ccStatus.getStatus()) == false) {
					throw new GenException(GenException.IF_INVALID_VALUE, "invalid status value["+ccStatus.getStatus()+"]");
				}
			}
			
			ESManagerForMovieMetaV1 esMgr = ESManagerForMovieMetaV1.getInstance();
			for(int i=0; i<ccStatusList.size(); i++) {
				CcStatus ccStatus = ccStatusList.get(i);
				try {
					
					ccStatus = ccStatusList.get(i);
					
					//1. DB ccontent status 업데이트
					CContentVO cContent = ccDAO.selectCcontentCCidByCCidVersion(ccStatus.getCcid(), ccStatus.getVersion());	
					cContent.setStatus(ccStatus.getStatus());
					ccDAO.updateCcontent(cContent);
					
					esMgr.updateCcStatus(ESManagerForMovieMetaV1.INDEX_ORI, ccStatus.getCcid(), ccStatus.getVersion(), ccStatus.getStatus());
					esMgr.updateCcStatus(ESManagerForMovieMetaV1.INDEX_SVC, ccStatus.getCcid(), ccStatus.getVersion(), ccStatus.getStatus());
					
					
					
					ccStatus.setResult("Success");
					
				} catch (Exception e) {
					log.error("["+tid+"] transferCcStatus err ["+ccStatus+"]", e);
					ccStatus.setResult("Error");
					ccStatus.setResultMessage(e.getMessage());
				}
			}
			
		} catch (Exception e) {
			log.error("["+tid+"] transferCcStatus err ", e);
		}
	}
	
	

	private HashMap<String, ArtworkFileVO> alistToMap(List<ArtworkFileVO> list) {
		HashMap<String, ArtworkFileVO> map = new HashMap<String, ArtworkFileVO>();
		for(int i=0; i<list.size(); i++) {
			map.put(list.get(i).getFileName(), list.get(i));
		}
		return map;
	}
	
	private HashMap<String, MetadataFileVO> mlistToMap(List<MetadataFileVO> list) {
		HashMap<String, MetadataFileVO> map = new HashMap<String, MetadataFileVO>();
		for(int i=0; i<list.size(); i++) {
			map.put(list.get(i).getMetaPath(), list.get(i));
		}
		return map;
	}
	
	public DefResponseVO updateCcMetadata(int tid, BasicMetaVO basicMeta) {
		
		DefResponseVO defRes = new DefResponseVO();
		try {
			Date lastModify = new Date();
			basicMeta.setLastModifyAsDate(lastModify);
			
			ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
			String basicMetaString = mapper.writeValueAsString(basicMeta);
			
			
			
			MetadataVO dbMetaData = ccDAO.selectMetadataByMetaSeq(Long.parseLong(basicMeta.getMetaSeq()));
			dbMetaData.setMetadataService(basicMetaString);
			dbMetaData.setLastModify(lastModify);
			ccDAO.updateMetadata(dbMetaData);
			
			
			//아트웤 파일 동기화..
			List<MetadataFileVO> fileList = ccDAO.selectMetaFileByCCidVersion(dbMetaData.getCcid(), dbMetaData.getVersion());
			List<ArtworkFileVO> artworkList = basicMeta.getMetadata().getArtwork();
			HashMap<String, ArtworkFileVO> artworkMap = alistToMap(artworkList);
			HashMap<String, MetadataFileVO> metaFileMap = mlistToMap(fileList);
			
			
			List<MetadataFileVO> removeList = new ArrayList<MetadataFileVO>();
			List<MetadataFileVO> addList = new ArrayList<MetadataFileVO>();
			for(int i=0; i<fileList.size(); i++) {
				MetadataFileVO metaFile = fileList.get(i);
				if(metaFile.getMetaType().equals("image") && 
					(metaFile.getFileStatus().equals(MetadataFileVO.STATUS_INIT) || metaFile.getFileStatus().equals(MetadataFileVO.STATUS_ADD))) {
					
					if(artworkMap.get(metaFile.getMetaPath()) == null) {
						removeList.add(metaFile);
					}
				}
			}
			for(int i=0; i<artworkList.size(); i++) {
				ArtworkFileVO artworkFile = artworkList.get(i);
				if(metaFileMap.get(artworkFile.getFileName()) == null) {
					
					MetadataFileVO addMetaFile = new MetadataFileVO();
					addMetaFile.setCcid(dbMetaData.getCcid());
					addMetaFile.setVersion(dbMetaData.getVersion());
					addMetaFile.setMetaPath(artworkFile.getFileName());
					addMetaFile.setMetaType("image");
					addMetaFile.setMetaSize(artworkFile.getFileSize());
					addMetaFile.setMetaClass("basic");
					addMetaFile.setFileStatus(MetadataFileVO.STATUS_ADD);
					addList.add(addMetaFile);
				}
			}
			
			for(int i=0; i<removeList.size(); i++) {
				removeList.get(i).setFileStatus(MetadataFileVO.STATUS_DEL);
				int ret = ccDAO.updateMetaFiles(removeList.get(i));
				log.debug("["+tid+"] remove artwork ["+removeList.get(i).getMetaPath()+"] ["+ret+"]");
			}
			for(int i=0; i<addList.size(); i++) {
				int ret = ccDAO.insertMetaFiles(addList.get(i));
				log.debug("["+tid+"] add artwork ["+addList.get(i).getMetaPath()+"] ["+ret+"]");
			}
			
			
			
			
			//TODO. 업데이트 히스토리 등록..
			MetadataHistVO metaHist = new MetadataHistVO();
			metaHist.setMetaSeq(dbMetaData.getMetaSeq());
			metaHist.setMetadata(basicMetaString);
			metaHist.setUpdateDate(lastModify);
			ccDAO.insertMetadataHist(metaHist);
			
			
			
			
			ESManagerForMovieMetaV1 esMgr = ESManagerForMovieMetaV1.getInstance();
			esMgr.updateMetadata(ESManagerForMovieMetaV1.INDEX_SVC, dbMetaData.getCcid(), dbMetaData.getVersion(), basicMeta);
			
			
			
			defRes.setResult(DefResponseVO.SUCCESS);
		} catch (Exception e) {
			log.error("["+tid+"] updateCcMetadata err ", e);
			defRes.setResult(DefResponseVO.ERROR);
			defRes.setResultMessage(e.getMessage());
		}
		return defRes;
	}
	
	
	public void uploadServiceImage(int tid, UploadServiceImageRequest uploadReq) {
		try {
			Configuration config = Configuration.getInstance();
			String ccBasePath = config.getStringExtra("cc.basedir");
			String ccPath = ccBasePath + "/download/" + uploadReq.getCcid() +"_" + uploadReq.getVersion();
			
			Random r = new Random();
			DecimalFormat df = new DecimalFormat("000000");
			List<ArtworkFileVO> artworkList = uploadReq.getImageList();
			for(int i=0; i<artworkList.size(); i++) {
				ArtworkFileVO artwork = artworkList.get(i);
				if(artwork.getBase64Image() != null) {
					Base64ImageDecoder imgDecoder = new Base64ImageDecoder(artwork.getBase64Image());
					
					String imagePath = "basicMeta/" + System.currentTimeMillis() + "_" + df.format(r.nextInt(100000)) + "." + imgDecoder.getFormat();
					File saveImageFile = new File(ccPath + "/" + imagePath);
					imgDecoder.writeImage(saveImageFile);
					log.debug("["+tid+"] saveFile ["+saveImageFile.getAbsolutePath()+"] ["+saveImageFile.length()+"]");
					
					artwork.setBase64Image(null);
					artwork.setFileName(imagePath);	
				}
				
				//Fill Meta
				File imgFile = new File(ccPath + "/" + artwork.getFileName());
				BufferedImage bimg = ImageIO.read(imgFile);
				artwork.setFileSize(imgFile.length());
				artwork.setHeight(bimg.getHeight());
				artwork.setWidth(bimg.getWidth());
				
				if(imgFile.getName().toUpperCase().endsWith(".JPEG")) {
					artwork.setFormat("I01");	
				} else if (imgFile.getName().toUpperCase().endsWith(".JPG")) {
					artwork.setFormat("I02");
				} else if (imgFile.getName().toUpperCase().endsWith(".PNG")) {
					artwork.setFormat("I03");
				} else if (imgFile.getName().toUpperCase().endsWith(".GIF")) {
					artwork.setFormat("I04");
				} else if (imgFile.getName().toUpperCase().endsWith(".BMP")) {
					artwork.setFormat("I05");
				} else {
					artwork.setFormat("I06");
				}
				//JPEG(I01), JPG(I02), PNG(I03), GIF(I04), BMP(I05)
			}
			
		} catch (Exception e) {
			log.error("["+tid+"] uploadServiceImage err ", e);
		}
	}
	
	
	public GroupByResponseVO groupByCount(int tid, String index, String key) {
		
		
		GroupByResponseVO response = new GroupByResponseVO();
		try {
			ESManagerForMovieMetaV1 esMgr = ESManagerForMovieMetaV1.getInstance();
			SearchResponse res = esMgr.groupByCount(tid, index, key);
			
			Aggregations aggs = res.getAggregations();
			ParsedStringTerms gAgg = aggs.get("group_by_state");
			
			@SuppressWarnings("unchecked")
			List<Terms.Bucket> list = (List<Terms.Bucket>)gAgg.getBuckets();
			
			
			for(int i=0; i<list.size(); i++) {
				response.addKeyValue(list.get(i).getKeyAsString(), list.get(i).getDocCount());
			}
			
			
			
		} catch (Exception e) {
			log.error("["+tid+"] groupByCount err ", e);
		}
		
		return response;
	}
	
		
	private void close(InputStream o) {
		if(o != null) {
			try {
				o.close();
			} catch (IOException e) {
				log.error("close error", e);
			}
		}
	}
	private void close(OutputStream o) {
		if(o != null) {
			try {
				o.close();
			} catch (IOException e) {
				log.error("close error", e);
			}
		}
	}
}
