package tom.common.util;

import org.apache.commons.pool.PoolableObjectFactory;

public class ObjectTicketPool implements PoolableObjectFactory<ObjectTicket> {

	int size = 0;

	@Override
	public ObjectTicket makeObject() throws Exception {
		
		ObjectTicket ticket = new ObjectTicket();
		ticket.setNumber(++size);
		
		return ticket;
	}

	@Override
	public void destroyObject(ObjectTicket obj) throws Exception {
	}

	@Override
	public boolean validateObject(ObjectTicket obj) {
		return false;
	}

	@Override
	public void activateObject(ObjectTicket obj) throws Exception {
		
	}

	@Override
	public void passivateObject(ObjectTicket obj) throws Exception {
		
	}
		
}
