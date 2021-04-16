package tom.common.util;

public class SimilarArtistInfo implements Comparable<SimilarArtistInfo> {

	private int orgArtistId = 0;
	private int artistId = 0;
	private double similarity = 0;
	private int orgArtistRating = 0;
	
	
	

	

	public SimilarArtistInfo(int orgArtistId, int artistId, double similarity) {
		this.orgArtistId = orgArtistId;
		this.artistId = artistId;
		this.similarity = similarity;
	}
	public SimilarArtistInfo(int orgArtistId, int artistId, double similarity, int orgArtistRating) {
		this.orgArtistId = orgArtistId;
		this.artistId = artistId;
		this.similarity = similarity;
		this.orgArtistRating = orgArtistRating;
	}
	
	public int getOrgArtistRating() {
		return orgArtistRating;
	}

	public void setOrgArtistRating(int orgArtistRating) {
		this.orgArtistRating = orgArtistRating;
	}
	public int getOrgArtistId() {
		return orgArtistId;
	}

	public void setOrgArtistId(int orgArtistId) {
		this.orgArtistId = orgArtistId;
	}

	public int getArtistId() {
		return artistId;
	}

	public void setArtistId(int artistId) {
		this.artistId = artistId;
	}

	public double getSimilarity() {
		return similarity;
	}

	public void setSimilarity(double similarity) {
		this.similarity = similarity;
	}

	public SimilarArtistInfo() {
	}

	@Override
	public int compareTo(SimilarArtistInfo o) {

		if (similarity > o.getSimilarity()) {
			return -1;
		} else if (similarity < o.getSimilarity()) {
			return 1;
		} else {
			return 0;
		}
	}

}
