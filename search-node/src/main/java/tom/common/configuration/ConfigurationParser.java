package tom.common.configuration;

import java.io.File;
import java.net.InetAddress;

import org.apache.xpath.XPathAPI;
import org.slf4j.Logger;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;

import tom.common.util.GenException;
import tom.common.util.XMLUtil;

public class ConfigurationParser {
	
	public static final String TYPE_STRING  = "string";
	public static final String TYPE_INTEGER = "integer";
	public static final String TYPE_LONG    = "long";
	public static final String TYPE_DOUBLE  = "double";
	
	
	public static void parseConfig(File configFile, Logger log) throws GenException {
		
		Configuration conf = Configuration.getInstance();
		
		try {
			log.info("******************** Start VIMS ********************");
			
			
			Document doc = XMLUtil.generateDocument(configFile);
			Element rootEle = doc.getDocumentElement();
			
			String serverIp = "";
			try {
				serverIp = InetAddress.getLocalHost().getHostAddress();	
			} catch (Exception e) {
				log.error("Search error " + e.getMessage());
			}
			
			
			String serverName = XPathAPI.eval(rootEle, "serverName").str();
			serverName = serverName + "_" + serverIp;
			
			log.info("Server Name : ["+serverName+"]");
			conf.clearExtras();
			
			
			NodeList nl = XPathAPI.selectNodeList(rootEle, "extras/field");
			
			
			for(int i=0; i<nl.getLength(); i++) {
				//<field name="test.Double" type="double">1000.054</field>
				Element ele = (Element)nl.item(i);
				
				String name = ele.getAttribute("name");
				String type = ele.getAttribute("type");
				String value = ele.getTextContent();
				
				log.debug("Extra ["+name+"]["+type+"]:["+value+"]");
				
				if(name == null || name.trim().equals("") == true) {
					throw new GenException(-1, "extraField name is null");
				}
				
				if(TYPE_STRING.equals(type)) {
					conf.putExtra(name, value);
				} else if (TYPE_INTEGER.equals(type)) {
					conf.putExtra(name, Integer.parseInt(value));
				} else if (TYPE_LONG.equals(type)) {
					conf.putExtra(name, Long.parseLong(value));
				} else if (TYPE_DOUBLE.equals(type)) {
					conf.putExtra(name, Double.parseDouble(value));
				} else {
					throw new GenException(GenException.CONF_INVALID_TYPE, "invalid field type ["+type+"]");
				}
			}
			
			conf.setServerName(serverName);
			conf.setInit(true);
			
			log.info("***************************************************************");
		} catch (Exception e) {
			conf.clearExtras();
			throw new GenException(GenException.CONF_ERROR, "configuration error", e);
		}
	}
}