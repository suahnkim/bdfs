package tom.common.util;

import java.io.ByteArrayOutputStream;
import java.io.Closeable;
import java.io.IOException;
import java.io.InputStream;
import java.net.UnknownHostException;
import java.util.ArrayList;
import java.util.List;

import org.apache.http.Header;
import org.apache.http.HttpEntity;
import org.apache.http.StatusLine;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.methods.CloseableHttpResponse;
import org.apache.http.client.methods.HttpDelete;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.client.methods.HttpPut;
import org.apache.http.client.methods.HttpUriRequest;
import org.apache.http.conn.HttpClientConnectionManager;
import org.apache.http.conn.HttpHostConnectException;
import org.apache.http.entity.ByteArrayEntity;
import org.apache.http.impl.client.CloseableHttpClient;
import org.apache.http.impl.client.HttpClients;

public class HttpRequestor {
	
	public static final int HTTP_GET = 0;
	public static final int HTTP_POST = 1;
	public static final int HTTP_DELETE = 2;
	public static final int HTTP_PUT = 3;
	
	private String url = null;
	private List<Header> addHeader = new ArrayList<Header>();
	private Header[] responseHeader = null;
	private int statusCode = -1;
	private String responseProtocol = null;
	private byte[] responseBody = null;
	private int method = HTTP_GET;
	private byte[] requestData = null;
	private HttpEntity requestEntity = null;
	private HttpClientConnectionManager connManager = null;
	
	public HttpRequestor(String url, int method) {		
		init(url, method);
	}
	
	private void init(String url, int method){
		this.url = url;
		this.method = method;
		
		if(url == null)
			throw new NullPointerException("request url is null");
		
		if(method != HTTP_GET && method != HTTP_POST && method != HTTP_DELETE && method != HTTP_PUT)
			throw new IllegalArgumentException("invalid http method. ["+method+"]");
	}
	
	
	
	
	public int request() throws ClientProtocolException, UnknownHostException, HttpHostConnectException, IOException {
		
		CloseableHttpClient httpClient = null;
		if(connManager != null) {
			httpClient = HttpClients.createMinimal(connManager);
		} else {
			httpClient = HttpClients.createDefault();
		}
		 
		
		CloseableHttpResponse response = null;
		
		try {
			HttpUriRequest httpReq = null;
			if(method == HTTP_GET) {
				httpReq = new HttpGet(url);
			} else if(method == HTTP_DELETE) {
				httpReq = new HttpDelete(url);
			} else if(method == HTTP_PUT) {
				httpReq = new HttpPut(url);
			} else {
				httpReq = new HttpPost(url);
			}

			httpReq.addHeader("Connection", "close");
			

			if(method == HTTP_POST && requestData != null) {
				HttpEntity entity = new ByteArrayEntity(requestData);
				((HttpPost)httpReq).setEntity(entity);
			} else if (method == HTTP_POST && requestEntity != null) {
				((HttpPost)httpReq).setEntity(requestEntity);
				
			} else if(method == HTTP_PUT && requestData != null) {
				HttpEntity entity = new ByteArrayEntity(requestData);
				((HttpPut)httpReq).setEntity(entity);
			} else if (method == HTTP_PUT && requestEntity != null) {
				((HttpPut)httpReq).setEntity(requestEntity);
			}
			
			for(int i=0; i<addHeader.size(); i++){
				httpReq.addHeader(addHeader.get(i));
			}
			
			response = httpClient.execute(httpReq);
			StatusLine stLine = response.getStatusLine();
			//System.out.println(">> ["+stLine+"]");
			
			
			statusCode = stLine.getStatusCode();
			responseProtocol = stLine.getProtocolVersion().toString();
			responseHeader = response.getAllHeaders();
			
			HttpEntity entry = response.getEntity();
			
			if(entry != null) {
				ByteArrayOutputStream baos = new ByteArrayOutputStream();
				InputStream is = null;
				try {
					is = entry.getContent();
					byte[] buf = new byte[8192];
					int readNum = 0;
					
					while((readNum = is.read(buf)) != -1){
						baos.write(buf, 0, readNum);
					}
					
					responseBody = baos.toByteArray();
				} finally {
					close(is);	
				}
			}
			
		} finally {
			close(httpClient);
			close(response);
		}
		
		return statusCode;
	}
	
	
	public void close(Closeable o) {
		if(o != null) {
			try {
				o.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}
	
	
	public void setRequestData(byte[] requestData) {
		this.requestData = requestData;
	}
	
	public String getUrl() {
		return url;
	}

	public Header[] getResponseHeader() {
		return responseHeader;
	}
	
	public String getResponseHeader(String name) {	
		for(int i=0;responseHeader != null && i<responseHeader.length; i++){
			if(responseHeader[i].getName().equalsIgnoreCase(name)) {
				return responseHeader[i].getValue();
			}
		}
		
		return null;
	}

	public int getStatusCode() {
		return statusCode;
	}

	public String getResponseProtocol() {
		return responseProtocol;
	}

	public byte[] getResponseBody() {
		return responseBody;
	}
	
	public void addHeader(Header header){
		addHeader.add(header);
	}
	
	public HttpEntity getRequestEntity() {
		return requestEntity;
	}
	
	public void setRequestEntity(HttpEntity requestEntity) {
		this.requestEntity = requestEntity;
	}
	
	private void close(InputStream is) {
		if(is != null) {
			try {
				is.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}
}