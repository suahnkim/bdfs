package tom.mediabc.search.dao;

import java.util.HashMap;
import java.util.List;

import javax.annotation.Resource;

import org.apache.ibatis.session.SqlSessionFactory;
import org.springframework.stereotype.Repository;

import tom.common.basic.BasicDao;
import tom.common.util.GenException;
import tom.mediabc.search.vo.dao.RegisterQueueVO;

@Repository("registerQueueDAO")
public class RegisterQueueDAOImpl extends BasicDao {
	public static final String PREFIX = "register";
	
	@Resource(name = "sqlSessionFactory")
	public void setSqlSessionFactory(SqlSessionFactory sqlSessionFactory) {
		super.setSqlSessionFactory(sqlSessionFactory);
	}
	
	
	public List<RegisterQueueVO> selectJobByCode(String procCode, int limit) throws GenException {
		HashMap<String, Object> param = new HashMap<String, Object>();
		param.put("procCode", procCode);
		param.put("limit",    limit);
		return runSelectStatement(PREFIX + ".selectJobByCode", param);
	}
	
	
	
	
	public int insertRegisterQueue(RegisterQueueVO queueVO) throws GenException {
		return runInsertStatement(PREFIX + ".insertRegisterQueue", queueVO);
	}
	
	
	
	public int updateRegisterQueue(RegisterQueueVO queueVO) throws GenException {
		return runUpdateStatement(PREFIX + ".updateRegisterQueue", queueVO);
	}
	
	
	
	
	
	
	/*
	public RegisterQueueVO selectJobByCcidVer(String ccid, String version) throws GenException {
		HashMap<String, Object> param = new HashMap<String, Object>();
		param.put("ccid",    ccid);
		param.put("version", version);
		return runSelectStatementOne(PREFIX + ".selectJobByCcidVer", param);
	}
	*/
	
}
