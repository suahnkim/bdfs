<?php
class Pagination{
	private static function paging($base_url, $total_rows, $limit = 10, $num_links = 5, $query_string_segment = 'pageNum'){
		$qs = array();
		parse_str($_SERVER['QUERY_STRING'], $qs);
		$cur_page = (int)(@$qs[$query_string_segment] == null ? 1 : $qs[$query_string_segment]);
		$qs[$query_string_segment] = "(:num)";
		$urlPattern = $base_url."?".http_build_query($qs);
		$urlPattern = str_replace("%28%3Anum%29", "(:num)", $urlPattern);
		
		if($num_links > 0) $num_links = $num_links - 1;

		$total_page = ceil($total_rows / $limit);
		$start_page = ((ceil($cur_page / ($num_links+1)) - 1) * ($num_links+1)) + 1;
		$end_page = $start_page + $num_links;

		if($end_page >= $total_page) $end_page = $total_page;

		$paging_arr = array();

		if($cur_page < 1) $cur_page = 1;
		if($cur_page > 1) array_push($paging_arr, array("num"=> 1, "url"=>str_replace("(:num)", $cur_page, $urlPattern), "pos"=>"first"));
		if($start_page > 1) array_push($paging_arr, array("num"=> ($start_page - 1), "url"=>str_replace("(:num)", ($start_page - 1), $urlPattern), "pos"=>"prev"));

		if($total_page >= 1){
			for($i=$start_page;$i<=$end_page;$i++){
				if($cur_page == $i){
					array_push($paging_arr, array("num"=> $i, "url"=>str_replace("(:num)", $i, $urlPattern), "pos"=>"cur"));
				}else{
					array_push($paging_arr, array("num"=> $i, "url"=>str_replace("(:num)", $i, $urlPattern), "pos"=>"num"));
				}
			}
		}

		if($total_page < $end_page) $end_page = $total_page;
		if($end_page != $total_page) array_push($paging_arr, array("num"=> $end_page+1, "url"=>str_replace("(:num)", $end_page+1, $urlPattern), "pos"=>"next"));
		if($cur_page != $total_page) array_push($paging_arr, array("num"=> $total_page, "url"=>str_replace("(:num)", $total_page, $urlPattern), "pos"=>"last"));

		return $paging_arr;
	}



	public static function make($base_url, $total_rows, $limit = 10, $num_links = 5, $query_string_segment = 'pageNum'){
		$pages = self::paging($base_url, $total_rows, $limit, $num_links, $query_string_segment);
		$html = '<div id="paging">';
		
		foreach ($pages as $key=>$page){
			switch($page['pos']){
				case "prev":
					$html .= '<a href="'.$page['url'].'"><button type="button" class="nav btn_bk btn_round col_ff">&lt;</button></a>';
					break;
				case "next":
					$html .= '<a href="'.$page['url'].'"><button type="button" class="nav btn_bk btn_round col_ff">&gt;</button></a>';
					break;
				case "num":
					$html .= '<a href="'.$page['url'].'"><button type="button" class="num btn_wh bold col_20">'.$page['num'].'</button></a>';
					break;
				case "cur":
					$html .= '<a href="'.$page['url'].'"><button type="button" class="num btn_yl bold col_20">'.$page['num'].'</button></a>';
					break;
				case "first":
				case "last":
					break;
			}
		}
		$html .= '</div>';
		return $html;
	}

/*<div class= text-center" style="text-align:center;"><ul class="pagination"><li class="paginate_button page-item previous disabled" id="datatable-editable_previous"><a href="#" aria-controls="datatable-editable" data-dt-idx="0" tabindex="0" class="page-link">Previous</a></li><li class="paginate_button page-item active"><a href="#" aria-controls="datatable-editable" data-dt-idx="1" tabindex="0"class= "page-link">1</a></li><li class="paginate_button page-item "><a href="#" aria-controls="datatable-editable" data-dt-idx="2" tabindex="0" class="page-link">2</a></li><li class="paginate_button page-item "><a href="#" aria-controls="datatable-editable" data-dt-idx="3" tabindex="0" class="page-link">3</a></li><li class="paginate_button page-item "><a href="#" aria-controls="datatable-editable" data-dt-idx="4" tabindex="0" class="page-link">4</a></li><li class="paginate_button page-item "><a href="#" aria-controls="datatable-editable" data-dt-idx="5" tabindex="0" class="page-link">5</a></li><li class="paginate_button page-item "><a href="#" aria-controls="datatable-editable" data-dt-idx="6" tabindex="0" class="page-link">6</a></li><li class="paginate_button page-item next" id="datatable-editable_next"><a href="#" aria-controls="datatable-editable" data-dt-idx="7" tabindex="0" class="page-link">Next</a></li></ul></div>*/

    public static function makePage($base_url, $total_rows, $limit = 10, $num_links = 5, $query_string_segment = 'pageNum'){
        $pages = self::paging($base_url, $total_rows, $limit, $num_links, $query_string_segment);
        $html = '<div style="margin-top:20px;"><ul class="pagination text-center" style="position:relative; display: -webkit-flex;display: flex;-webkit-justify-content: center;justify-content: center;-webkit-align-items: center;
  align-items: center; ">';

        foreach ($pages as $key=>$page){
            switch($page['pos']){
                case "prev":
                    $html .= '<li class="paginate_button page-item previous"><a href="'.$page['url'].'" class= "page-link">&lt;</a></li>';
                    break;
                case "next":
                    $html .= '<li class="paginate_button page-item next"><a href="'.$page['url'].'" class= "page-link">&gt;</a><li>';
                    break;
                case "num":
                    $html .= '<li class="paginate_button page-item"><a href="'.$page['url'].'" class= "page-link">'.$page['num'].'</a><li>';
                    break;
                case "cur":
                    $html .= '<li class="paginate_button page-item active"><a href="javascript:;" class= "page-link">'.$page['num'].'</a></li>';
                    break;
                case "first":
                case "last":
                    break;
            }
        }
        $html .= '</ul></div>';
        return $html;
    }
}