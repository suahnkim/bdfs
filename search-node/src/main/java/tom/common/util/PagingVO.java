package tom.common.util;

import java.util.ArrayList;
import javax.servlet.http.HttpServletRequest;

public class PagingVO
{
  private int entityPerPage = 10;
  private int nowPage = 1;
  private int totalEntity = 0;
  private HttpServletRequest request = null;
  
  public PagingVO(int nowPage, int totalEntity, int entityPerPage)
  {
    this.nowPage = nowPage;
    this.totalEntity = totalEntity;
    this.entityPerPage = entityPerPage;
  }
  
  public int getLastRow()
  {
    return this.entityPerPage * this.nowPage;
  }
  
  public int getFirstRow()
  {
    return this.entityPerPage * this.nowPage - this.entityPerPage;
  }
  
  public int getMaxPage()
  {
    return (int)Math.ceil(this.totalEntity / (this.entityPerPage * 1.0D));
  }
  
  public int[] getPages()
  {
    int maxPage = (int)Math.ceil(this.totalEntity / (this.entityPerPage * 1.0D));
    int startPageNum = (int)Math.floor((this.nowPage - 1) / 10.0D) * 10 + 1;
    
    ArrayList<Integer> pageList = new ArrayList();
    for (int i = 0; i < 10; i++)
    {
      if (startPageNum + i > maxPage) {
        break;
      }
      pageList.add(Integer.valueOf(startPageNum + i));
    }
    int[] retPages = new int[pageList.size()];
    for (int i = 0; i < retPages.length; i++) {
      retPages[i] = ((Integer)pageList.get(i)).intValue();
    }
    return retPages;
  }
  
  public String genPaginHtml()
  {
    StringBuffer sb = new StringBuffer();
    int[] pages = getPages();
    
    String qString = this.request.getQueryString();
    qString = qString == null ? "" : qString;
    
    qString = qString.replaceAll("nowPage=" + this.nowPage + "&", "");
    qString = qString.replaceAll("nowPage=" + this.nowPage, "");
    if (!qString.equals("")) {
      qString = "&" + qString;
    }
    sb.append("<ul>");
    for (int i = 0; i < pages.length; i++) {
      if (pages[i] == this.nowPage) {
        sb.append("<li ><b>[" + pages[i] + "]</b></li>");
      } else {
        sb.append("<a href=\"?nowPage=" + pages[i] + qString + "\"><li >[" + pages[i] + "]</li></a>");
      }
    }
    sb.append("</ul>");
    
    return sb.toString();
  }
  
  public int getEntityPerPage()
  {
    return this.entityPerPage;
  }
  
  public int getNowPage()
  {
    return this.nowPage;
  }
  
  public int getTotalEntity()
  {
    return this.totalEntity;
  }
  
  public static String printSelected(String param1, String param2)
  {
    if ((param1 != null) && (param1.equals(param2))) {
      return "selected";
    }
    return "";
  }
  
  public static String nullToBlank(String str)
  {
    if (str == null) {
      return "";
    }
    return str;
  }
  
  public static String nullToBlankHtml(String str)
  {
    if (str == null) {
      return "";
    }
    return str.replaceAll("\n", "<br/>");
  }
}
