package tom.common.basic;

import java.io.OutputStream;
import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.Statement;
import java.util.List;

import org.apache.ibatis.session.SqlSession;
import org.apache.ibatis.session.SqlSessionFactory;

import tom.common.util.GenException;


public class BasicDao {

private SqlSessionFactory sqlSessionFactory;
	
	public void setSqlSessionFactory(SqlSessionFactory sqlSessionFactory) {
		this.sqlSessionFactory = sqlSessionFactory;
	}
	
	
	
	public int runUpdateStatement(String statement, Object parameter) throws GenException {
		SqlSession sSession = null;
		try {
			sSession = sqlSessionFactory.openSession();
			return sSession.update(statement, parameter);
		} catch (Exception e) {
			throw new GenException(GenException.DB_UPDATE_ERROR, e.getMessage() + " update error. ["+statement+"]["+parameter+"] ", e);
		} finally {
			close(sSession);
		}
	}
	
	public int runInsertStatement(String statement, Object parameter) throws GenException {
		SqlSession sSession = null;
		try {
			sSession = sqlSessionFactory.openSession();
			return sSession.insert(statement, parameter);
		} catch (Exception e) {
			throw new GenException(GenException.DB_INSERT_ERROR, e.getMessage() + " insert error. ["+statement+"]["+parameter+"] ", e);
		} finally {
			close(sSession);
		}
	}
	
	public int runDeleteStatement(String statement, Object parameter) throws GenException {
		SqlSession sSession = null;
		try {
			sSession = sqlSessionFactory.openSession();
			return sSession.delete(statement, parameter);
		} catch (Exception e) {
			throw new GenException(GenException.DB_DELETE_ERROR, e.getMessage() + " delete error. ["+statement+"]["+parameter+"] ", e);
		} finally {
			close(sSession);
		}
	}
	
	public <E> List<E> runSelectStatement(String statement, Object parameter) throws GenException {
		SqlSession sSession = null;
		try {
			sSession = sqlSessionFactory.openSession();
			return sSession.selectList(statement, parameter);
		} catch (Exception e) {
			throw new GenException(GenException.DB_UPDATE_ERROR, e.getMessage() + " select error. ["+statement+"]["+parameter+"] ", e);
		} finally {
			close(sSession);
		}
	}
	
	public <T> T runSelectStatementOne(String statement, Object parameter) throws GenException {
		SqlSession sSession = null;
		try {
			sSession = sqlSessionFactory.openSession();
			return sSession.selectOne(statement, parameter);
		} catch (Exception e) {
			throw new GenException(GenException.DB_UPDATE_ERROR, e.getMessage() + " select error. ["+statement+"]["+parameter+"] ", e);
		} finally {
			close(sSession);
		}
	}
	
	/*
	public int runSelectCountStatement(String statement, HashMap<String, Object> parameter, String fieldName) throws GenException {
		SqlSession sSession = null;
		try {
			sSession = sqlSessionFactory.openSession();
			List<HashMap<String, Object>> list = sSession.selectList(statement, parameter);
			
			if(list.size() == 0) {
				return 0;
			} else {
				return ((Long)list.get(0).get(fieldName)).intValue();
			}
			
		} catch (Exception e) {
			throw new GenException(GenException.DB_UPDATE_ERROR, e.getMessage() + " select error. ["+statement+"]["+parameter+"] ", e);
		} finally {
			close(sSession);
		}
	}
	*/
	
	
	public SqlSession getSqlSession() throws GenException {
		try {
			return sqlSessionFactory.openSession();	
		} catch (Exception e) {
			throw new GenException(GenException.DB_INSERT_ERROR, e.getMessage() + " getConnection", e);
		}
	}
	
	
	
	
	
	/*
	public HashMap<String, Object> rsToMap(ResultSet rs) throws SQLException {
		
		HashMap<String, Object> map = new HashMap<String, Object>();
		ResultSetMetaData meta = rs.getMetaData();
		int colNum = meta.getColumnCount();
		

		
		for(int i=0; i<colNum; i++) {
			String columnName  = meta.getColumnName(i+1);
			Object columnValue = rs.getObject(columnName);
			map.put(columnName, columnValue);
		}
		
		return map;
	}
	*/
	
	
	
	
	
	public void close(OutputStream o) {
		if(o != null) {
			try {
				o.close();	
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
	}
	public void close(ResultSet o) {
		if(o != null) {
			try {
				o.close();	
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
	}
	
	public void close(Statement o) {
		if(o != null) {
			try {
				o.close();	
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
	}
	
	
	public void close(Connection o) {
		if(o != null) {
			try {
				o.close();	
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
	}
	
	public void close(SqlSession o) {
		if(o != null) {
			try {
				o.close();	
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
	}
	
	
	public String cutString(String line, int len) {
		if(line == null) {
			return null;
		} else { 
			if(line.length() > len) {
				return line.substring(0, len);
			} else {
				return line;
			}
		}
	}
}