package tom.common.util;

import java.text.DecimalFormat;

import org.slf4j.Logger;

public class MemoryStatus {

	public static String printMemoryStatus(Logger l) {
		StringBuffer sb = new StringBuffer();
		
		long mega = 1024 * 1024;
		long maxMemoty   = Runtime.getRuntime().maxMemory();
		long totalMemoty = Runtime.getRuntime().totalMemory();
		long freeMemoty  = Runtime.getRuntime().freeMemory();
		long useMemory   = totalMemoty - freeMemoty;
		double usePct    = (useMemory/(maxMemoty * 1.0))*100.0; 
		
		DecimalFormat df = new DecimalFormat("#,###,###,###,###.00");
		sb.append("\n");
		sb.append("---MEMORY INFO ---\n");
		sb.append("MAX   Memory : " + df.format(maxMemoty/mega)   + "Mb\n");
		sb.append("Total Memory : " + df.format(totalMemoty/mega) + "Mb\n");
		sb.append("Use   Memory : " + df.format(useMemory/mega)   + "Mb  >>>> PCT ["+df.format(usePct)+"]%\n");
		sb.append("------------------\n");
		
		String message = sb.toString();
		l.info(message);
		
		return message;
	}
}
