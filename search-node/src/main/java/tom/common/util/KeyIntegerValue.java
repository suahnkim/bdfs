package tom.common.util;

public class KeyIntegerValue implements Comparable<KeyIntegerValue>{

	private String k = "";
	private int v = 0;
	
	public KeyIntegerValue() {
	}
	
	public KeyIntegerValue(String key, int value) {
		this.k = key;
		this.v = value;
	}
	
	
	
	public String getK() {
		return k;
	}
	public void setK(String k) {
		this.k = k;
	}
	public int getV() {
		return v;
	}

	public void setV(int v) {
		this.v = v;
	}
	public void plusValue() {
		v++;
	}
	

	@Override
	public int compareTo(KeyIntegerValue o) {
		
		if(v > o.getV()) {
			return -1;
		} else if (v < o.getV()) {
			return 1;
		} else {
			return 0;
		}
	}
	
	@Override
	public String toString() {
		return "KeyIntegerValue [k=" + k + ", v=" + v + "]";
	}
}
