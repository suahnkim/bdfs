package tom.common.util;

public class MatchingNameGenerator {
	
	
	
	public static String genMatchingName(String title, String artist) {
		String conTitle = normalizeString(title);
		String conArtist = normalizeString(artist);
		//String conAlbum = convertString(album);
		
		return conTitle + "::" + conArtist;
	}
	
	
	
	public static String normalizeString(String str) {
		int limitCharLen = 50;
		
		//& -> and 수정..
		//- + , . ( ) [ ] ' ` ’ * ! ? 
		if(str == null) {
			return "";
		}
		
		char[] cBuf = str.toCharArray();
		
		
		for(int i=0; i<cBuf.length; i++) {
			if(cBuf[i] == '-' ||
				cBuf[i] == '+' ||
				cBuf[i] == ',' ||
				cBuf[i] == '.' ||
				cBuf[i] == '(' ||
				cBuf[i] == ')' ||
				cBuf[i] == '[' ||
				cBuf[i] == ']' ||
				cBuf[i] == '\'' ||
				cBuf[i] == '`' ||
				cBuf[i] == '’' ||
				cBuf[i] == '*' ||
				cBuf[i] == '!' ||
				cBuf[i] == '?' ||
				cBuf[i] == '=' ||
				cBuf[i] == '_' ||
				cBuf[i] == '@' ||
				cBuf[i] == '#' ||
				cBuf[i] == '$' ||
				cBuf[i] == '%' ||
				cBuf[i] == '^' ||
				cBuf[i] == '/' ||
				cBuf[i] == '|' ||
				cBuf[i] == '~' ||
				cBuf[i] == '’' ||
				cBuf[i] == '\\' ||
				cBuf[i] == '`') {
				
				cBuf[i] = ' ';
			}
		}
		
		String newString = new String(cBuf);
		newString = newString.toLowerCase();
		newString = newString.replace(" ", "");
		newString = newString.replace("&", "and");
		
		
		newString = newString.length() > limitCharLen ? newString.substring(0, limitCharLen) : newString;
		
		return newString;
	}	
	
}
