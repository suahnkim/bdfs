package tom.mediabc.search.vo.cc;

import com.fasterxml.jackson.annotation.JsonProperty;

import lombok.Data;

@Data
public class ContributionVO {

	private String contributor;
	@JsonProperty("contributorRole")
	private String contributorRole;
}
