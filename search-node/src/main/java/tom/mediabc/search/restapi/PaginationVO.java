package tom.mediabc.search.restapi;

import java.util.ArrayList;
import java.util.List;

import lombok.Data;

@Data
public class PaginationVO {

	private long searchCount;
	private int nowPage;
	private int rowPerPage;
	private int dispPage = 10;
	
	public int getTotalPage() {
		Double tPage = Math.ceil((searchCount*1.0)/(rowPerPage*1.0));
		return tPage.intValue();
	}
	
	public List<Integer> getPageRange() {
		//System.out.println("searchCount ["+searchCount+"] nowPage["+nowPage+"] rowPerPage["+rowPerPage+"]");
		int startPage = (((nowPage-1)/dispPage) *dispPage) + 1;
		int totalPage = getTotalPage();
		//int[] range = new int[dispPage];
		List<Integer> range = new ArrayList<Integer>();
		
		for(int i=startPage; i<startPage+dispPage; i++) {
			range.add(i);
			if(i >= totalPage ) {
				break;
			}
		}
		return range;
	}

}
