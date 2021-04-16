package tom.common.util;


import java.io.File;

import com.amazonaws.auth.AWSCredentials;
import com.amazonaws.auth.AWSStaticCredentialsProvider;
import com.amazonaws.auth.BasicAWSCredentials;
import com.amazonaws.services.s3.AmazonS3;
import com.amazonaws.services.s3.AmazonS3ClientBuilder;

import tom.common.configuration.Configuration;

public class S3AccelerateUtil {
	
	
	private AmazonS3 s3Client = null;
	
	private static S3AccelerateUtil instance = null;
	private S3AccelerateUtil() {	
		Configuration config = Configuration.getInstance();
		
		String accesskeyId     = config.getStringExtra("s3.accesskeyId");
		String secretAccessKey = config.getStringExtra("s3.secretAccessKey");
		
		String region          = config.getStringExtra("s3.region");
		AWSCredentials credentials = new BasicAWSCredentials(accesskeyId, secretAccessKey);
		s3Client = AmazonS3ClientBuilder.standard()
                .withRegion(region)
                .withCredentials(new AWSStaticCredentialsProvider(credentials))
                .enableAccelerateMode()
                .build();
    }
	
    public static synchronized S3AccelerateUtil getInstance() {
        if (instance == null) {
            instance = new S3AccelerateUtil();
        }
        return instance;
    }
    
    public void transfer(String cKeyName, File srcFile) {
    	Configuration config   = Configuration.getInstance();
    	String bucketName      = config.getStringExtra("s3.bucketName");
    	s3Client.putObject(bucketName, cKeyName, srcFile);
    }
    
}