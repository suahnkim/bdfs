package tom.common.util;

import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.io.UnsupportedEncodingException;
import java.net.URL;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.validation.Schema;
import javax.xml.validation.SchemaFactory;
import javax.xml.validation.Validator;

import org.apache.xml.security.c14n.CanonicalizationException;
import org.apache.xml.security.c14n.Canonicalizer;
import org.apache.xml.security.c14n.InvalidCanonicalizerException;
import org.w3c.dom.DOMImplementation;
import org.w3c.dom.Document;
import org.w3c.dom.Node;
import org.w3c.dom.bootstrap.DOMImplementationRegistry;
import org.w3c.dom.ls.DOMImplementationLS;
import org.w3c.dom.ls.LSOutput;
import org.w3c.dom.ls.LSSerializer;
import org.xml.sax.SAXException;

public class XMLUtil {
		
	public static String CANOICAL_METHOD = "http://www.w3.org/2001/10/xml-exc-c14n#";
	private static DocumentBuilderFactory documentBuilderFactory = DocumentBuilderFactory.newInstance();
	
	
	static{
		org.apache.xml.security.Init.init();
	}
    
    
    public static byte[] canonicalize(Node node) throws InvalidCanonicalizerException, CanonicalizationException  {
    	Canonicalizer canonicalizer = Canonicalizer.getInstance(CANOICAL_METHOD);
    	return canonicalizer.canonicalizeSubtree(node);
    }
    
    
    
    public static Document generateDocument(String xmlStr) throws ParserConfigurationException, SAXException, IOException  {
    	InputStream is = new ByteArrayInputStream(xmlStr.getBytes());
		return generateDocument(is);
    }
    
    public static Document generateDocument() throws ParserConfigurationException {
    	DocumentBuilder documentBuilder = documentBuilderFactory.newDocumentBuilder();
    	Document doc = documentBuilder.newDocument();
    	
		return doc;
    }
    
    
    public static Document generateDocument(InputStream is) throws ParserConfigurationException, SAXException, IOException  {
    	DocumentBuilder documentBuilder = documentBuilderFactory.newDocumentBuilder();
    	Document doc = documentBuilder.parse(is);
    	
		return doc;
    }
    
    
    public static Document generateDocument(File file) throws ParserConfigurationException, SAXException, IOException  {
    	DocumentBuilder documentBuilder = documentBuilderFactory.newDocumentBuilder();
    	Document doc = documentBuilder.parse(file);
    	
		return doc;
    }
    
    public static byte[] serialize(Document doc) throws IOException, ClassCastException, ClassNotFoundException, InstantiationException, IllegalAccessException {        
    	return serialize(doc, false, doc.getXmlEncoding());
    }
    
    public static byte[] serialize(Node doc, boolean indent, String encoding) throws ClassCastException, ClassNotFoundException, InstantiationException, IllegalAccessException, UnsupportedEncodingException {
    	ByteArrayOutputStream baos = new ByteArrayOutputStream();
    	
    	DOMImplementation implementation= DOMImplementationRegistry.newInstance().getDOMImplementation("XML 3.0");
    	DOMImplementationLS feature = (DOMImplementationLS) implementation.getFeature("LS", "3.0");
    	LSSerializer lsSerializer = feature.createLSSerializer();
    	
    	LSOutput output = feature.createLSOutput();
    	output.setByteStream(baos);
    	output.setEncoding(encoding);
    	lsSerializer.getDomConfig().setParameter("format-pretty-print", indent);
    	
    	lsSerializer.write(doc, output);
    	return baos.toByteArray();
    }
    
    
    
    
    public static String getTextContent(Node node){
    	if(node == null)
    		return "";
    	
    	Node firstChild = node.getFirstChild();
    	
    	if(firstChild == null)
    		return "";
    	
    	String nodeValue = firstChild.getNodeValue();
    	if(nodeValue == null) {
    		return "";
    	} else {
    		return nodeValue;
    	}
    }
    
    
    
    
    public static Schema getSchemaFromXSD(URL xsdUrl) throws SAXException {
    	if(xsdUrl == null) {
    		throw new NullPointerException("xsd url is null");
    	}
    	
    	SchemaFactory factory = SchemaFactory.newInstance("http://www.w3.org/2001/XMLSchema");
        return factory.newSchema(xsdUrl);
    }
    
    public static Schema getSchemaFromXSD(File xsdFile) throws SAXException {
    	if(xsdFile == null) {
    		throw new NullPointerException("xsd file is null");
    	}
    	
    	SchemaFactory factory = SchemaFactory.newInstance("http://www.w3.org/2001/XMLSchema");
        return factory.newSchema(xsdFile);
    }
    
    public static Validator getValidatorFromXSD(URL xsdUrl) throws SAXException {
    	return getSchemaFromXSD(xsdUrl).newValidator();
    }
    
    public static Validator getValidatorFromXSD(File xsdFile) throws SAXException {
    	return getSchemaFromXSD(xsdFile).newValidator();
    }
    
    
    public static String convertXmlString(String str) {
    	
    	if(str == null) {
    		return "";
    	} else {
    		str = str.replaceAll("\"", "&quot;");
    		str = str.replaceAll("&", "&amp;");
    		str = str.replaceAll("'", "&apos;");
    		str = str.replaceAll("<", "&lt;");
    		str = str.replaceAll(">", "&gt;");
    		
    		return str;
    	}
    }
}
