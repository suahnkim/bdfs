package tom.common.util;

import java.util.Date;

import java.text.SimpleDateFormat;

public class TimeUtil {
	
	public static String getTimestring(SimpleDateFormat sdf, Date date) {
		if(date == null) {
			return "";
		}
		Date kstDate = new Date(date.getTime() + 32400000l);
		if(sdf == null) {
			return kstDate.toString();
		}
		return sdf.format(kstDate);
	}
	
	public static String getTimestring(Date date) {
		SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
		return getTimestring(sdf, date);
	}
}
