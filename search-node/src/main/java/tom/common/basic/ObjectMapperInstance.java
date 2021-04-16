package tom.common.basic;



import com.fasterxml.jackson.databind.DeserializationFeature;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.PropertyNamingStrategy;

public class ObjectMapperInstance {
	

	private static ObjectMapperInstance instance = null;
	private ObjectMapperInstance() {	
		
		mapper.setPropertyNamingStrategy(
			    PropertyNamingStrategy.CAMEL_CASE_TO_LOWER_CASE_WITH_UNDERSCORES);
		
		mapper.configure(DeserializationFeature.FAIL_ON_UNKNOWN_PROPERTIES, false);
    }
	
    public static synchronized ObjectMapperInstance getInstance() {
        if (instance == null) {
            instance = new ObjectMapperInstance();
        }
        return instance;
    }
	
	private ObjectMapper mapper = new ObjectMapper();
	
	public ObjectMapper getMapper() {
		return mapper;
	}
}