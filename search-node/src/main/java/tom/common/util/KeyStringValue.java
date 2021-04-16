package tom.common.util;

public class KeyStringValue implements Comparable<KeyStringValue>{

	private String k = "";
	private String v = "";
	
	public KeyStringValue() {
	}
	
	public KeyStringValue(String key, String value) {
		this.k = key;
		this.v = value;
	}
	
	
	
	public String getK() {
		return k;
	}
	public void setK(String k) {
		this.k = k;
	}
	public String getV() {
		return v;
	}
	public void setV(String v) {
		this.v = v;
	}

	@Override
	public int compareTo(KeyStringValue o) {
		return v.compareTo(o.getV());
	}

	@Override
	public String toString() {
		return "KeyStringValue [k=" + k + ", v=" + v + "]";
	}
	
	
}
