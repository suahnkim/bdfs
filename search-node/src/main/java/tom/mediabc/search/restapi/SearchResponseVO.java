package tom.mediabc.search.restapi;

import java.util.ArrayList;

import lombok.Data;
import tom.mediabc.search.vo.cc.EsCContentVO;


@Data
public class SearchResponseVO {
	
	private long took;
	private String status;
	private PaginationVO pagination;
	private ArrayList<EsCContentVO> result;
}
