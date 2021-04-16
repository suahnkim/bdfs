package tom.common.util;


import com.amazonaws.auth.AWSCredentials;
import com.amazonaws.auth.AWSStaticCredentialsProvider;
import com.amazonaws.auth.BasicAWSCredentials;
import com.amazonaws.services.sqs.AmazonSQS;
import com.amazonaws.services.sqs.AmazonSQSClientBuilder;
import com.amazonaws.services.sqs.model.SendMessageRequest;
import com.amazonaws.services.sqs.model.SendMessageResult;

import tom.common.configuration.Configuration;

public class SQSUtil {
	
	
	private AmazonSQS sqs = null;
	private static SQSUtil instance = null;
	private SQSUtil() {	
		Configuration config = Configuration.getInstance();
		String region          = config.getStringExtra("sqs.region");
		String accesskeyId     = config.getStringExtra("sqs.accesskeyId");
		String secretAccessKey = config.getStringExtra("sqs.secretAccessKey");
		
		
		AWSCredentials credentials = new BasicAWSCredentials(accesskeyId, secretAccessKey);
		sqs = AmazonSQSClientBuilder.standard()
				.withRegion(region)
				.withCredentials(new AWSStaticCredentialsProvider(credentials))
				.build();
		
    }
	
    public static synchronized SQSUtil getInstance() {
        if (instance == null) {
            instance = new SQSUtil();
        }
        return instance;
    }
    
    public SendMessageResult sendMessage(String queueUrl, String message) {
    	SendMessageRequest send_msg_request = new SendMessageRequest()
		        .withQueueUrl(queueUrl)
		        .withMessageBody(message)
		        .withDelaySeconds(5);
		return sqs.sendMessage(send_msg_request);	
    }
    
    public AmazonSQS getAmazonSQSInstance() {
    	return sqs;
    }
}