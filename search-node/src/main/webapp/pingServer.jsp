<%@ page language="java" contentType="text/html; charset=EUC-KR"
	pageEncoding="EUC-KR"
	import="org.json.JSONObject"

	
	%><%
	
	Runtime rt = Runtime.getRuntime();
	long maxMem = rt.maxMemory();
	long freeMem = rt.freeMemory();
	long totalMem = rt.totalMemory();
	long usedMem = totalMem - freeMem;
	
	JSONObject jsonObj = new JSONObject();
	
	JSONObject memInfoObj = new JSONObject();
	memInfoObj.put("maxMem", maxMem);
	memInfoObj.put("freeMem", freeMem);
	memInfoObj.put("totalMem", totalMem);
	memInfoObj.put("usedMem", usedMem);
	
	jsonObj.put("memInfo", memInfoObj);
	
	

	
	
	String simpleInfo = jsonObj.toString(4);

	out.println(simpleInfo);
		
%>