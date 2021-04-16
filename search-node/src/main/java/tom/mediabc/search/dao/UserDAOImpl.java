package tom.mediabc.search.dao;

import java.util.HashMap;

import javax.annotation.Resource;

import org.apache.ibatis.session.SqlSessionFactory;
import org.springframework.stereotype.Repository;

import tom.common.basic.BasicDao;
import tom.common.util.GenException;
import tom.mediabc.search.catalog.LoginInfo;

@Repository("iserDAO")
public class UserDAOImpl extends BasicDao {
	public static final String PREFIX = "user";
	
	@Resource(name = "sqlSessionFactory")
	public void setSqlSessionFactory(SqlSessionFactory sqlSessionFactory) {
		super.setSqlSessionFactory(sqlSessionFactory);
	}
	
	
	public LoginInfo selectUserByUserId(String userId) throws GenException {
		HashMap<String, Object> param = new HashMap<String, Object>();
		param.put("userId", userId);
		return runSelectStatementOne(PREFIX + ".selectUserByUserId", param);
	}
	
}
