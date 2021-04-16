package tom.mediabc.search.vo.cc;

import java.util.List;

import lombok.Data;

@Data
public class MetaBasicMoveV1VO {

	private String venderId;
	private String country;
	private String originalSpokenLocale;
	private String title;
	private String synopsis;
	private String productionCompany;
	private String copyrightCline;
	private String theatricalReleaseDate;
	private String rating;
	
	private List<String> genre;
	private List<CastVO> cast;
	private List<CrewVO> crew;
	private List<ArtworkFileVO> artwork;
	
	private String contentsInfo;
}
