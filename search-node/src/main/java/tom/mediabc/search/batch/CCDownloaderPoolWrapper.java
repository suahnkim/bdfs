package tom.mediabc.search.batch;

import org.apache.commons.pool.PoolableObjectFactory;
import org.apache.commons.pool.impl.GenericObjectPool;

import tom.common.configuration.Configuration;
import tom.common.util.ObjectTicketPool;
import tom.common.util.ObjectTicket;

public class CCDownloaderPoolWrapper {

	
	private PoolableObjectFactory<ObjectTicket> factory = null;
	private GenericObjectPool<ObjectTicket> objectPool = null;
	
	private static CCDownloaderPoolWrapper instance = null;
	private CCDownloaderPoolWrapper() {
		Integer threadNum = Configuration.getInstance().getIntegerExtra("batch.download.thread");
		factory = new ObjectTicketPool();
        objectPool = new GenericObjectPool<ObjectTicket>(factory, threadNum.intValue());
    }
	
    public static synchronized CCDownloaderPoolWrapper getInstance() {
        if (instance == null) {
            instance = new CCDownloaderPoolWrapper();
        }
        return instance;
    }
    
    public GenericObjectPool<ObjectTicket> getObjectPool() {
    	return objectPool;
    }
    
    /*
    public Ticket borrowObject() throws Exception {
    	return objectPool.borrowObject();
    }
    public void returnObject(Ticket obj) throws Exception {
    	objectPool.returnObject(obj);
    }
    
    public int getNumActive() {
    	objectPool.getNumActive();
    }
    */
}
