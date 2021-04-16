package tom.common.configuration;

import java.io.File; 

import javax.servlet.ServletConfig;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;

import org.apache.log4j.xml.DOMConfigurator;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
 
public class InitializeServlet extends HttpServlet {
	
	private static final long serialVersionUID = -7413747120726677447L;
	
	public void init(ServletConfig config) throws ServletException {
		super.init();
		
		Logger log = LoggerFactory.getLogger(LoggerName.SVC);
		
		try {
			
			File log4jFile = new File(config.getServletContext().getRealPath("WEB-INF/classes/log4j.xml"));
			log.info("LOG4J  FILE    :: [" + log4jFile.getAbsolutePath() + "]["+log4jFile.exists()+"]");
			DOMConfigurator.configureAndWatch(log4jFile.getAbsolutePath(), 5000);
			
			File configFile = new File(config.getServletContext().getRealPath("WEB-INF/config/config.xml"));
			log.info("CONFIG FILE    :: [" + configFile.getAbsolutePath() + "]["+configFile.exists()+"]");
			
			ConfigurationWatchDog configurationWatchDog = new ConfigurationWatchDog(configFile, 5000, log);
			configurationWatchDog.start();
			
		} catch (Exception e) {
			log.error("Server Initialize Failure! " + e.getMessage(), e);
			log.error("Server Shutdown!");
			
			System.exit(0);
		}
	}
}