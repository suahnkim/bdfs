package tom.mediabc.search.vo.cc;

import lombok.Data;

@Data
public class CrewVO {

	public CrewVO() {
	}
	public CrewVO(String artistId, String name, String role) {
		this.artistId = artistId;
		this.name = name;
		this.role = role;
	}
	
	private String name;
	private String artistId;
	private String role;
}
