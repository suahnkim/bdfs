package tom.mediabc.search.dao;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import javax.annotation.Resource;

import org.apache.ibatis.session.SqlSessionFactory;
import org.springframework.stereotype.Repository;
import org.springframework.transaction.annotation.Propagation;
import org.springframework.transaction.annotation.Transactional;

import tom.common.basic.BasicDao;
import tom.common.util.GenException;
import tom.mediabc.search.vo.dao.CContentFileVO;
import tom.mediabc.search.vo.dao.CContentVO;
import tom.mediabc.search.vo.dao.MetadataFileVO;
import tom.mediabc.search.vo.dao.MetadataHistVO;
import tom.mediabc.search.vo.dao.MetadataVO;

@Repository("CCDAO")
public class CCDAOImpl extends BasicDao {

	public static final String PREFIX = "cc";
	
	@Resource(name = "sqlSessionFactory")
	public void setSqlSessionFactory(SqlSessionFactory sqlSessionFactory) {
		super.setSqlSessionFactory(sqlSessionFactory);
	}
	
	
	/*
	public List<RegisterQueueVO> selectJobByCode(String procCode, int limit) throws GenException {
		HashMap<String, Object> param = new HashMap<String, Object>();
		param.put("procCode", procCode);
		param.put("limit",    limit);
		return runSelectStatement(PREFIX + ".selectJobByCode", param);
	}
	
	public int updateRegisterQueue(RegisterQueueVO queueVO) throws GenException {
		return runUpdateStatement(PREFIX + ".updateRegisterQueue", queueVO);
	}
	*/
	
	
	
	@Transactional(propagation=Propagation.REQUIRED, rollbackFor={GenException.class})
	public void registerCC(
			CContentVO metaCCid, 
			ArrayList<MetadataFileVO> metaFilesArr, 
			ArrayList<CContentFileVO> contentFilesArr, 
			ArrayList<MetadataVO> metaDataArr) throws GenException {
		
		try {
			
			runInsertStatement(PREFIX + ".insertCContent", metaCCid);
			
			for(int i=0;i<metaFilesArr.size(); i++) {
				runInsertStatement(PREFIX + ".insertMetaFiles", metaFilesArr.get(i));
			}
			for(int i=0;i<contentFilesArr.size(); i++) {
				runInsertStatement(PREFIX + ".insertContentFiles", contentFilesArr.get(i));
			}
			for(int i=0;i<metaDataArr.size(); i++) {
				runInsertStatement(PREFIX + ".insertMetaData", metaDataArr.get(i));
			}
			
			
			
		} catch (GenException e) {
			throw e;
		} catch (Exception e) {
			throw new GenException(GenException.DB_ERROR, e.getMessage(), e);
		}
	}
	
	
	public int deleteCC(CContentVO metaCCid) throws GenException {
		try {
			return runDeleteStatement(PREFIX + ".deleteCC", metaCCid);
		} catch (GenException e) {
			throw e;
		} 
	}
	
	public List<MetadataVO> selectMetadataByCcidVer(String ccid, String version) throws GenException {
		try {
			HashMap<String, String> param = new HashMap<String, String>();
			param.put("ccid", ccid);
			param.put("version", version);
			
			return runSelectStatement(PREFIX + ".selectMetadataByCcidVer", param);
		} catch (GenException e) {
			throw e;
		} 
	}
	
	
	public List<String> selectAllVersionByCcid(String ccid) throws GenException {
		HashMap<String, Object> param = new HashMap<String, Object>();
		param.put("ccid", ccid);
		return runSelectStatement(PREFIX + ".selectAllVersionByCcid", param);
	}
	
	
	
	public MetadataVO selectMetadataByMetaSeq(long metaSeq) throws GenException {
		try {
			HashMap<String, String> param = new HashMap<String, String>();
			param.put("metaSeq", metaSeq + "");
			
			return runSelectStatementOne(PREFIX + ".selectMetadataByMetaSeq", param);
		} catch (GenException e) {
			throw e;
		} 
	}
	
	
	
	
	public List<CContentFileVO> selectContentFilesByCCidVersion(String ccid, String version) throws GenException {
		try {
			HashMap<String, String> param = new HashMap<String, String>();
			param.put("ccid", ccid);
			param.put("version", version);
			
			return runSelectStatement(PREFIX + ".selectContentFilesByCCidVersion", param);
		} catch (GenException e) {
			throw e;
		} 
	}
	
	
	public CContentVO selectCcontentCCidByCCidVersion(String ccid, String version) throws GenException {
		try {
			HashMap<String, String> param = new HashMap<String, String>();
			param.put("ccid", ccid);
			param.put("version", version);
			
			return runSelectStatementOne(PREFIX + ".selectCcontentCCidByCCidVersion", param);
		} catch (GenException e) {
			throw e;
		} 
	}
	
	public int updateCcontent(CContentVO cContent) throws GenException {
		try {
			
			return runUpdateStatement(PREFIX + ".updateCcontent", cContent);
		} catch (GenException e) {
			throw e;
		} 
	}
	
	public int updateMetadata(MetadataVO metadata) throws GenException {
		try {
			
			return runUpdateStatement(PREFIX + ".updateMetadata", metadata);
		} catch (GenException e) {
			throw e;
		} 
	}
	
	
	
	
	public MetadataFileVO selectMetaFileByCCidVersionPath(String ccid, String version, String path) throws GenException {
		try {
			HashMap<String, String> param = new HashMap<String, String>();
			param.put("ccid", ccid);
			param.put("version", version);
			param.put("path", path);
			
			return runSelectStatementOne(PREFIX + ".selectMetaFileByCCidVersionPath", param);
		} catch (GenException e) {
			throw e;
		} 
	}
	
	public List<MetadataFileVO> selectMetaFileByCCidVersion(String ccid, String version) throws GenException {
		try {
			HashMap<String, String> param = new HashMap<String, String>();
			param.put("ccid", ccid);
			param.put("version", version);
			
			return runSelectStatement(PREFIX + ".selectMetaFileByCCidVersion", param);
		} catch (GenException e) {
			throw e;
		} 
	}
	
	
	public int updateMetaFiles(MetadataFileVO metadataFile) throws GenException {
		try {
			
			return runUpdateStatement(PREFIX + ".updateMetaFiles", metadataFile);
		} catch (GenException e) {
			throw e;
		} 
	}
	
	public int insertMetaFiles(MetadataFileVO metadataFile) throws GenException {
		try {
			
			return runInsertStatement(PREFIX + ".insertMetaFiles", metadataFile);
		} catch (GenException e) {
			throw e;
		} 
	}
	
	
	public int insertMetadataHist(MetadataHistVO metadataHist) throws GenException {
		try {
			
			return runInsertStatement(PREFIX + ".insertMetadataHist", metadataHist);
		} catch (GenException e) {
			throw e;
		} 
	}
	
	
}