package tom.mediabc.search.core;

import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.concurrent.TimeUnit;

import org.apache.http.HttpHost;
import org.elasticsearch.action.delete.DeleteRequest;
import org.elasticsearch.action.delete.DeleteResponse;
import org.elasticsearch.action.index.IndexRequest;
import org.elasticsearch.action.index.IndexResponse;
import org.elasticsearch.action.search.SearchRequest;
import org.elasticsearch.action.search.SearchResponse;
import org.elasticsearch.action.update.UpdateRequest;
import org.elasticsearch.action.update.UpdateResponse;
import org.elasticsearch.client.RequestOptions;
import org.elasticsearch.client.RestClient;
import org.elasticsearch.client.RestHighLevelClient;
import org.elasticsearch.common.unit.TimeValue;
import org.elasticsearch.common.xcontent.XContentType;
import org.elasticsearch.index.query.BoolQueryBuilder;
import org.elasticsearch.index.query.QueryBuilders;
import org.elasticsearch.index.query.RangeQueryBuilder;
import org.elasticsearch.script.Script;
import org.elasticsearch.script.ScriptType;
import org.elasticsearch.search.aggregations.AggregationBuilders;
import org.elasticsearch.search.aggregations.bucket.terms.TermsAggregationBuilder;
import org.elasticsearch.search.builder.SearchSourceBuilder;
import org.elasticsearch.search.sort.FieldSortBuilder;
import org.elasticsearch.search.sort.ScoreSortBuilder;
import org.elasticsearch.search.sort.SortOrder;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;

import tom.common.basic.ObjectMapperInstance;
import tom.common.configuration.Configuration;
import tom.common.configuration.LoggerName;
import tom.common.util.KeyStringValue;
import tom.mediabc.search.restapi.SearchParam;
import tom.mediabc.search.vo.cc.BasicMetaVO;
import tom.mediabc.search.vo.cc.EsCContentVO;

public class ESManagerForMovieMetaV1 {

	public static final String INDEX_ORI = "ccontents_original";
	public static final String INDEX_SVC = "ccontents_service";
	
	public static final String TYPE  = "cc";
	private static ESManagerForMovieMetaV1 instance = null;
	private Logger log = LoggerFactory.getLogger(LoggerName.SVC);
	
	
	private ESManagerForMovieMetaV1() {	
    }
	
    public static synchronized ESManagerForMovieMetaV1 getInstance() {
        if (instance == null) {
            instance = new ESManagerForMovieMetaV1();
        }
        return instance;
    }
    
    
    
    
    public IndexResponse createOtUpdateMeta(String index, EsCContentVO esCc) throws IOException {
    	
    	RestHighLevelClient client = null;
    	try {
    		ArrayList<BasicMetaVO> metaContainerArr = esCc.getMetaContainer();
    		for(int i=0; i<metaContainerArr.size(); i++) {
    			metaContainerArr.get(i).setContribution(null);
    			metaContainerArr.get(i).setIdentifier(null);
    			metaContainerArr.get(i).setTitle(null);
    			metaContainerArr.get(i).setFormat(null);
    		}
    		
    		ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
    		client = genClient();
    		IndexRequest request = new IndexRequest(index);
    		request.id(esCc.getCcid() + "-" + esCc.getVersion());
    		
    		log.debug(mapper.writeValueAsString(esCc));
    		
    		request.source(mapper.writeValueAsString(esCc).getBytes("UTF-8"), XContentType.JSON);
    		return client.index(request, RequestOptions.DEFAULT);
    		
    	} finally {
    		close(client);
    	}
    }
    
    
    public UpdateResponse updateCcStatus(String index, String ccid, String version, String status) throws IOException {
    	RestHighLevelClient client = null;
    	try {
    		UpdateRequest request = new UpdateRequest(index, ccid + "-" + version);
    		request.script(new Script("ctx._source.status = \""+status+"\""));

    		client = genClient();
    		return client.update(request, RequestOptions.DEFAULT);
    	} finally {
    		close(client);
    	}    	
    }
    
    
    public UpdateResponse updateMetadata(String index, String ccid, String version, BasicMetaVO bMeta) throws IOException {
    	RestHighLevelClient client = null;
    	try {
    		UpdateRequest request = new UpdateRequest(index, ccid + "-" + version);
    		
    		String script = "for (int i=0; i<ctx._source.meta_container.size(); i++) {"
    				+ "		if(ctx._source.meta_container[i].meta_seq == params.metaSeq) {"
    				+ "			ctx._source.meta_container[i] = params.data"
    				+ "		}"
    				+ "}";
    		
    		//String script = "ctx._source.meta_container.add(params.data)";
    		
    		
    		ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
    		String metaJsonStr = mapper.writeValueAsString(bMeta);
    		TypeReference<HashMap<String,Object>> typeRef = new TypeReference<HashMap<String,Object>>() {};
    		HashMap<String, Object> map = mapper.readValue(metaJsonStr, typeRef);
    		
    		HashMap<String, Object> param = new HashMap<String, Object>();
    		param.put("metaSeq", bMeta.getMetaSeq());
    		param.put("data", map);
    		
    		request.script(new Script(ScriptType.INLINE, "painless", script, param));
    		
    		client = genClient();
    		return client.update(request, RequestOptions.DEFAULT);
    	} finally {
    		close(client);
    	}    	
    }
    
    public UpdateResponse addMetadata(String index, String ccid, String version, BasicMetaVO bMeta) throws IOException {
    	RestHighLevelClient client = null;
    	try {
    		UpdateRequest request = new UpdateRequest(index, ccid + "-" + version);
    		/*
    		String script = "for (int i=0; i<ctx._source.meta_container.size(); i++) {"
    				+ "		if(ctx._source.meta_container[i].meta_seq == params.metaSeq) {"
    				+ "			ctx._source.meta_container[i].meta_class = params.data"
    				+ "		}"
    				+ "}";
    		*/
    		String script = "ctx._source.meta_container.add(params.data)";
    		
    		
    		ObjectMapper mapper = ObjectMapperInstance.getInstance().getMapper();
    		String metaJsonStr = mapper.writeValueAsString(bMeta);
    		TypeReference<HashMap<String,Object>> typeRef = new TypeReference<HashMap<String,Object>>() {};
    		HashMap<String, Object> map = mapper.readValue(metaJsonStr, typeRef);
    		
    		HashMap<String, Object> param = new HashMap<String, Object>();
    		param.put("data", map);
    		
    		request.script(new Script(ScriptType.INLINE, "painless", script, param));
    		
    		client = genClient();
    		return client.update(request, RequestOptions.DEFAULT);
    	} finally {
    		close(client);
    	}    	
    }
    
    public UpdateResponse removeMetadata(String index, String ccid, String version, String metaSeq) throws IOException {
    	RestHighLevelClient client = null;
    	try {
    		UpdateRequest request = new UpdateRequest(index, ccid + "-" + version);
    		String script = "ctx._source.meta_container.removeIf("
    				+ "item -> item.meta_seq == params.metaSeq"
    				+ ")";
    		HashMap<String, Object> param = new HashMap<String, Object>();
    		param.put("metaSeq", metaSeq);
    		
    		request.script(new Script(ScriptType.INLINE, "painless", script, param));
    		
    		client = genClient();
    		return client.update(request, RequestOptions.DEFAULT);
    	} finally {
    		close(client);
    	}    	
    }
    
    
    
    
    public SearchResponse groupByCount(int tid, String index, String key) throws IOException {
    	RestHighLevelClient client = null;
    	try {
    		
    		SearchRequest request = new SearchRequest(index);
    		SearchSourceBuilder sourceBuilder = new SearchSourceBuilder(); 
    		
    		if(INDEX_SVC.equals(index)) {
    			BoolQueryBuilder statusBQ = QueryBuilders.boolQuery();
    			statusBQ.should(QueryBuilders.matchQuery("status", "service"));	
    			statusBQ.should(QueryBuilders.matchQuery("status", "ready"));	
    			//boolQ.must(statusBQ);	
    			sourceBuilder.query(statusBQ);
    		}
    		sourceBuilder.size(0); 
    		
    		TermsAggregationBuilder tab = AggregationBuilders.terms("group_by_state");
    		tab.field(key);
    		
    		sourceBuilder.aggregation(tab);
    		request.source(sourceBuilder);
    		
    		client = genClient();
    		return client.search(request, RequestOptions.DEFAULT);    		
    	} finally {
    		close(client);
    	}  	
    }
    
    
    public DeleteResponse delete(String index, String ccid, String version) throws IOException {
    	
    	RestHighLevelClient client = null;
    	try {
    		DeleteRequest request = new DeleteRequest(index);
    		request.id(ccid + "-" + version);
    		client = genClient();
    		return client.delete(request, RequestOptions.DEFAULT);
    	} finally {
    		close(client);
    	}
    }
    
    public SearchResponse search(int tid, String index, SearchParam sParam) throws IOException {
    	
    	RestHighLevelClient client = null;
    	try {
    		
    		ArrayList<KeyStringValue> fields = sParam.getFields();
    		
    		SearchRequest request = new SearchRequest(index);
    		SearchSourceBuilder sourceBuilder = new SearchSourceBuilder(); 
    		
    		BoolQueryBuilder boolQ = QueryBuilders.boolQuery();
    		
    		//CCID, VERSION
    		if(sParam.getCcid() != null) {
    			boolQ.must(QueryBuilders.matchQuery("ccid", sParam.getCcid()));
    			log.debug("["+tid+"]    es -> must ccid ["+sParam.getCcid()+"]");
    		}
    		if(sParam.getVersion() != null ) {
    			boolQ.must(QueryBuilders.matchQuery("version", sParam.getVersion()));
    			log.debug("["+tid+"]    es -> must version ["+sParam.getVersion()+"]");
    		}
    		if(sParam.getMetaSeq() != null ) {
    			boolQ.must(QueryBuilders.matchQuery("meta_container.meta_seq", sParam.getMetaSeq()));
    			log.debug("["+tid+"]    es -> must meta_seq ["+sParam.getMetaSeq()+"]");
    		}
    		
    		if(sParam.getCcStatus() != null && sParam.getCcStatus().equals("all")==false) {
    			
    			BoolQueryBuilder statusBQ = QueryBuilders.boolQuery();
    			String ccStatus = sParam.getCcStatus();
    			String[] ccStatusField = ccStatus.split(",");
    			for(int i=0; i<ccStatusField.length; i++) {
    				statusBQ.should(QueryBuilders.matchQuery("status", ccStatusField[i]));	
    				log.debug("["+tid+"]    es -> should status ["+ccStatusField[i]+"]");
    			}
    			boolQ.must(statusBQ);
    		}
    		
    		//keyword
    		if(sParam.getKeyword() != null && sParam.getKeyword().trim().equals("")==false) {
    			
    			BoolQueryBuilder statusBQ = QueryBuilders.boolQuery();
    			
    			statusBQ.should(QueryBuilders.matchQuery("meta_container.metadata.title.korean_field",    sParam.getKeyword()));	
    			statusBQ.should(QueryBuilders.matchQuery("meta_container.metadata.title.english_field",    sParam.getKeyword()));	
    			statusBQ.should(QueryBuilders.matchQuery("meta_container.metadata.synopsis.korean_field", sParam.getKeyword()));	
    			statusBQ.should(QueryBuilders.matchQuery("meta_container.metadata.synopsis.english_field", sParam.getKeyword()));	
    			statusBQ.should(QueryBuilders.matchQuery("meta_container.metadata.crew.name", sParam.getKeyword()));
    			statusBQ.should(QueryBuilders.matchQuery("meta_container.metadata.cast.name", sParam.getKeyword()));
    			
    			boolQ.must(statusBQ);
    		}
    		
    		
    		if(sParam.getFromDate() != null || sParam.getToDate() != null) {
    			RangeQueryBuilder rqb = QueryBuilders.rangeQuery("owner_reg_date").format("yyyy-MM-dd HH:mm:ss");
    			if(sParam.getFromDate() != null) {
    				rqb.from(sParam.getFromDate());	
    			}
    			if(sParam.getToDate() != null) {
    				rqb.to(sParam.getToDate());	
    			}
    			boolQ.must(rqb);
    			log.debug("["+tid+"]    es -> must owner_reg_date ["+rqb+"]");
    		}
    		
    		
    		for(int i=0; fields!=null && i<fields.size(); i++) {
    			String key   = fields.get(i).getK();
    			String value = fields.get(i).getV();
    			if(key.equals("crew")) {
    				boolQ.must(QueryBuilders.matchQuery("meta_container.metadata.crew.name", value));
    				
    				log.debug("["+tid+"]    es -> must crew.name ["+value+"]");
    				
    			} else if (key.equals("cast")) {
    				boolQ.must(QueryBuilders.matchQuery("meta_container.metadata.cast.name", value));
    				log.debug("["+tid+"]    es -> must cast.name ["+value+"]");
    				
    			} else if(key.equals("content_type")) {
    				boolQ.must(QueryBuilders.matchQuery("meta_container.content-type", value));
    				log.debug("["+tid+"]    es -> must content-type ["+value+"]");
    				
    				
    			} else if (key.equals("title")) {
    				BoolQueryBuilder statusBQ = QueryBuilders.boolQuery();
        			statusBQ.should(QueryBuilders.matchQuery("meta_container.metadata.title.korean_field",    value));	
        			statusBQ.should(QueryBuilders.matchQuery("meta_container.metadata.title.english_field",   value));	
        			boolQ.must(statusBQ);
    				log.debug("["+tid+"]    es -> must title ["+value+"]");
    				
    				
    			} else if (key.equals("synopsis")) {
    				BoolQueryBuilder statusBQ = QueryBuilders.boolQuery();
        			statusBQ.should(QueryBuilders.matchQuery("meta_container.metadata.synopsis.korean_field",  value));	
        			statusBQ.should(QueryBuilders.matchQuery("meta_container.metadata.synopsis.english_field", value));	
        			boolQ.must(statusBQ);
    				log.debug("["+tid+"]    es -> must synopsis ["+value+"]");
    				
    				
    			} else {
    				boolQ.must(QueryBuilders.matchQuery("meta_container.metadata." + key, value));
    				
    				log.debug("["+tid+"]    es -> must "+key+" ["+value+"]");
    			}
    		}
    		
    		if(boolQ.must() == null || boolQ.must().size() == 0) {
    			
    			if(sParam.getCcStatus() != null && sParam.getCcStatus().equals("all")) {
    				//PASS
    			} else {
    				log.debug("["+tid+"] must is zero.. search all service data");	
        			BoolQueryBuilder statusBQ = QueryBuilders.boolQuery();
        			statusBQ.should(QueryBuilders.matchQuery("status", "service"));	
    				log.debug("["+tid+"]    es -> should status [service]");
        			boolQ.must(statusBQ);	
    			}
    		} 
    		
    		sourceBuilder.query(boolQ);	
    		
    		
    		if("owner_reg_date".equals(sParam.getSortField())) {
    			FieldSortBuilder sortBuilder = new FieldSortBuilder("owner_reg_date");
    			if("asc".equals(sParam.getSortOrder())) {
    				sortBuilder.order(SortOrder.ASC);
    			} else if ("desc".equals(sParam.getSortOrder())) {
    				sortBuilder.order(SortOrder.DESC);
    			}
    			sourceBuilder.sort(sortBuilder);
    		}
    		if("title".equals(sParam.getSortField())) {
    			FieldSortBuilder sortBuilder = new FieldSortBuilder("meta_container.metadata.title");
    			if("asc".equals(sParam.getSortOrder())) {
    				sortBuilder.order(SortOrder.ASC);
    			} else if ("desc".equals(sParam.getSortOrder())) {
    				sortBuilder.order(SortOrder.DESC);
    			}
    			sourceBuilder.sort(sortBuilder);
    		}
    		if("score".equals(sParam.getSortField())) {
    			ScoreSortBuilder sortBuilder = new ScoreSortBuilder();
    			if("asc".equals(sParam.getSortOrder())) {
    				sortBuilder.order(SortOrder.ASC);
    			} else if ("desc".equals(sParam.getSortOrder())) {
    				sortBuilder.order(SortOrder.DESC);
    			}
    			sourceBuilder.sort(sortBuilder);
    		}
    		
    		
    		
    		
    		
    		int from = (sParam.getNowPage() -1) * sParam.getRowPerPage();
    		
    		sourceBuilder.from(from); 
    		sourceBuilder.size(sParam.getRowPerPage()); 
    		sourceBuilder.timeout(new TimeValue(60, TimeUnit.SECONDS)); 
    		
    		
    		request.source(sourceBuilder);
    		
    		
    		
    		
    		client = genClient();
    		return client.search(request, RequestOptions.DEFAULT);
    	} finally {
    		close(client);
    	}
    }
    
    
    
    private RestHighLevelClient genClient() {
    	String host   = Configuration.getInstance().getStringExtra("elasticsearch.host");
    	int port      = Configuration.getInstance().getIntegerExtra("elasticsearch.port", 80);
    	String schema = Configuration.getInstance().getStringExtra("elasticsearch.schema");


    	
    	return new RestHighLevelClient(
    	        RestClient.builder(
    	                new HttpHost(host, port, schema)));
    }
    
    
    
    
    private void close(RestHighLevelClient client) {
    	if(client != null) {
    		try {
				client.close();
			} catch (IOException e) {
				e.printStackTrace();
			}
    	}
    }
    
    
    /**



--DELETE index
curl -H 'Content-Type: application/json' -XDELETE 'http://localhost:9200/ccontents_original'
curl -H 'Content-Type: application/json' -XDELETE 'http://localhost:9200/ccontents_service'




--CREATE index
curl -H 'Content-Type: application/json' -XPUT 'http://localhost:9200/ccontents_service' -d '
{
"settings": {
  "index": {
    "analysis": {
      "normalizer": {
        "my_normalizer": {
          "type": "custom",
          "char_filter": [],
          "filter": ["lowercase", "asciifolding"]
        }
      }
    }
  }
},
  "mappings" : {
      "properties" : {
      	  "ccid" :                    { "type" : "keyword" },
          "version" :                 { "type" : "keyword" },
          "status" :                  { "type" : "keyword" },
          "owner_id" :                { "type" : "keyword" },
          "owner_reg_date" :          { "type" : "date", "format": "yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"},
          "meta_container" : {
          	"properties" : {
				  "meta_seq" :                { "type" : "long" },
		          "content-type" :            { "type" : "keyword"},
          		  "meta-type" :               { "type" : "keyword"},
          		  "meta_class" :              { "type" : "keyword"},
          		  "target" :                  { "type" : "keyword"},
          		  "last_modify" :             { "type" : "date", "format": "yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"},
          		  "metadata" : {
          		  	  "properties" : {
	          		  	  "vender_id" :               { "type" : "keyword" },
				          "theatrical_release_date" : { "type" : "keyword" },
				          "rating" :                  { "type" : "keyword" },
						  "country" :                 { "type" : "keyword", "normalizer": "my_normalizer"},
				          "original_spoken_locale" :  { "type" : "keyword", "normalizer": "my_normalizer"},
						  "genre" :                   { "type" : "keyword", "normalizer": "my_normalizer"},
						  
				          "production_company" :      { "type" : "text", "analyzer": "nori"},
				          "copyright_cline" :         { "type" : "text", "analyzer": "nori"},
				          "title" :                   {
				          		"type" : "text", 
				          		"fielddata" : true,
				          		"fields": {
						            "korean_field": {
						              "analyzer": "nori",
						              "type": "text"
						            },
						            "english_field": {
						              "analyzer": "english",
						              "type": "text"
						            }
				        		}
				          },
				          "synopsis" :                { 
				        		"type" : "text",
				        		"fields": {
						            "korean_field": {
						              "analyzer": "nori",
						              "type": "text"
						            },
						            "english_field": {
						              "analyzer": "english",
						              "type": "text"
						            }
				        		}
				          },
				
				          "cast" : {
				        	"properties" : {
				        		"artist_id" :         { "type" : "keyword" },
				        		"name" :              { "type" : "text", "analyzer": "nori"},
				        		"cast_name" :         { "type" : "text", "analyzer": "nori"}
				        	}
				          },
				          "crew" : {
				        	"properties" : {
				        		"artist_id" :         { "type" : "keyword" },
				        		"name" :              { "type" : "text", "analyzer": "nori"},
				        		"role" :              { "type" : "text", "analyzer": "nori"}
				        	}
				          },
				          "artwork" : {
				        	"properties" : {
				        		"title" :                  { "type" : "keyword" },
				        		"file_name" :              { "type" : "keyword"},
				        		"file_size" :              { "type" : "long"},
				        		"rep" :                    { "type" : "keyword"},
				        		"height" :                 { "type" : "long"},
				        		"width" :                  { "type" : "long"},
				        		"format" :                 { "type" : "keyword"}
				        	}
				          }
          		  	  }
          		  }
          	}
          }
      }
  }
}'




     * */
}
