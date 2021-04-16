package tom.common.util;

public class KeyDoubleValue implements Comparable<KeyDoubleValue>{

	private String k = "";
	private double v = 0;
	
	public KeyDoubleValue() {
	}
	
	public KeyDoubleValue(String key, double value) {
		this.k = key;
		this.v = value;
	}
	
	
	
	public String getK() {
		return k;
	}
	public void setK(String k) {
		this.k = k;
	}
	public double getV() {
		return v;
	}
	public void setV(double v) {
		this.v = v;
	}

	@Override
	public int compareTo(KeyDoubleValue o) {
		
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
		return "KeyDoubleValue [k=" + k + ", v=" + v + "]";
	}
	
	
}
