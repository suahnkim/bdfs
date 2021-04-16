/*****************************************************************
*                           Chainlink API 
*
* @author On-off hybrid blockchain technology development team 
* @version 2.5 
* @module ChainLink API / router 
* @history : 
*****************************************************************/

var express = require('express');
var cors = require('cors');
var FormData = require('form-data');
const fs = require('fs');
var axios = require('axios');
var router = express.Router();

/**
 * 콘텐츠 제공자가 콘텐츠 정보를 등록한다. 
 * @type Restful API 
 * @Method POST
 * @URL /register/data
 * @Request {string} cid 콘텐츠의 CID
 * @Request {string} ccid 콘텐츠의 CCID
 * @Request {string} version 콘텐츠의 VERSION
 * @Request {string} info 콘텐츠에 대한 기본 정보(json 형식) 
 * @Response {json} 처리결과
 */
router.post('/data', async function (req, res) {
  try {
    global.Logger.debug('\n/register/data req: ' + JSON.stringify(req.body));
    if (global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;

    let cid = "";
    if (req.body.cid.substr(0, 3) == "CID") {
      cid = Number(req.body.cid.substr(3, req.body.cid.length));
    } else {
      cid = req.body.cid;
    };
    const ccid = req.body.ccid;
    const version = req.body.version;
    const fee = req.body.fee;
    const file_hashes = req.body.fileHasheLists;
    const chunks = req.body.chunkLists;

    const info = req.body.info;
    let targetDist = req.body.targetDist;
    let targetUser = req.body.targetUser;
    //사용권한 0:19세이상, 1:사용그룹제한, 2.추가정보 => max 5
    let UsageRestriction = req.body.UsageRestriction;

    fileHashes = '';
    for (let i = 0; i < file_hashes.length; i++) {
      fileHashes += file_hashes[i];
    };
    //set distributor
    if (targetDist == null || targetDist == "" || targetDist == 0 || targetDist == "0") {
      targetDist = [];
    };
    //set content buyer
    if (targetUser == null || targetUser == "" || targetUser == 0 || targetUser == "0") {
      targetUser = [];
    };
    if (UsageRestriction == null || UsageRestriction == "") {
      UsageRestriction = [];
    };

    global.Logger.debug('UsageRestriction : ' + JSON.stringify(UsageRestriction));
    const dataId = await DappTools.registerData(cid, ccid, version, fee, fileHashes, chunks, info, targetDist, targetUser, UsageRestriction);
    res.json({
      resultCode: 0,
      dataId
    });

    global.Logger.debug('/register/data : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/register/data error : ' + error);
  }; 
});

/**
 * 콘텐츠 제공자가 등록한 콘텐츠 정보를 수정한다.   
 * @type Restful API 
 * @Method POST
 * @URL /modify/data
 * @Request {string} dataId 등록된 콘텐츠의 data id
 * @Request {string} info 콘텐츠에 대한 기본 정보(json 형식) 
 * @Request {string} UsageRestriction 콘텐츠의 사용 그룹을 지정
 * @Request {string} targetDist 콘텐츠를 사용할 배포자 목록
 * @Request {string} targetUser 콘텐츠를 사용할 사용자 목록 
 * @Response {json} 처리결과
 */
router.post('/modData', async function (req, res) {
  try {
    global.Logger.debug('\n/register/modData req: ' + JSON.stringify(req.body));
    if (global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;

    const dataid = req.body.dataid;
    const info = req.body.info;
    let targetDist = req.body.targetDist;
    let targetUser = req.body.targetUser;
    //사용권한 0:19세이상, 1:사용그룹제한, 2.추가정보 => max 5
    let UsageRestriction = req.body.UsageRestriction;

    if (dataid == null || dataid == "") {
      res.json({
        resultCode: 300,
        resultMessage: 'dataId is empty.'
      }); 
      return;
    };

    //set distributor
    if (targetDist == null || targetDist == "") {
      targetDist = [];
    };
    //set content buyer
    if (targetUser == null || targetUser == "") {
      targetUser = [];
    };
    if (UsageRestriction == null || UsageRestriction == "") {
      UsageRestriction = [];
    };
    const dataId = await DappTools.modifyData(dataid, info, targetDist, targetUser, UsageRestriction);
    res.json({
      resultCode: 0,
      dataId
    });

    global.Logger.debug('/register/modData : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/register/modData error : ' + error);
  }; 
});


/**
 * 사용자가 구매할 수 있는 상품의 정보(상품 목록, 구매비용 등)를 등록한다.  
 * @type Restful API 
 * @Method POST
 * @URL /register/product
 * @Request {string} ccid 콘텐츠의 CCID
 * @Request {string} version 콘텐츠의 VERSION
 * @Request {string} price 콘텐츠의 가격 
 * @Response {json} 처리결과
 */
router.post('/product', async function (req, res) {
  try {
    global.Logger.debug('\n/register/product req: ' + JSON.stringify(req.body));
    if (global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;

    const ccid = req.body.ccid;
    const version = req.body.version;
    const price = req.body.price;
    const productId = await DappTools.registerProduct(ccid, version, price);
    res.json({
      resultCode: 0,
      productId
    });
    global.Logger.debug('/register/product : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/register/product error : ' + error);
  }; 
});

/**
 * 사용자가 등록된 상품을 구입한다. 
 * @type Restful API 
 * @Method POST
 * @URL /register/buy
 * @Request {string} productId 상품 ID  
 * @Response {json} 처리결과
 */
router.post('/buy', async function (req, res) {
  try {
    global.Logger.debug('\n/register/buy req: ' + JSON.stringify(req.body));
    if (global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };

    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;

    const productId = req.body.productId;
    const purchaseId = await DappTools.buyProduct(productId);

    res.json({
      resultCode: 0,
      purchaseId
    });

    global.Logger.debug('/register/buy : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/register/buy error : ' + error);
  }; 
});

/**
 * 상품 구매 시 발급받은 토큰ID를 이용하여 콘텐츠를 다운로드 받기 위한 채널을 개설한다.  
 * @type Restful API 
 * @Method POST
 * @URL /register/channelOpen
 * @Request {string} purchaseId 구매 ID 
 * @Request {string} publicKey 전송완료영수증에 대하여 전자서명검증시 사용될 공개키
 * @Request {string} downChunkList 구매자가 다운로드 받을 파일의 Chunk List가 저장된 파일(절대경로)   
 * @Response {json} 처리결과
 */
router.post('/channelOpen', async function (req, res) {
  try {
    global.Logger.debug('\n/register/channelOpen req: ' + JSON.stringify(req.body));
    if (global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };

    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;

    const tokenId = req.body.purchaseId;
    const key = req.body.publicKey;
    const downChunk = req.body.downChunkList;

    // chunk file exist check
    var fexists = fs.existsSync(downChunk);
    if (fexists == false) {
      res.json({
        resultCode: 400,
        resultMessage: 'chunks file is not exist.'
      }); 
      return;
    };

    // count total chunk number
    let totchunkNo = await countChunks(downChunk);
    global.Logger.debug('total chunk Number:' + totchunkNo);

    // productid check
    try {
      const checkRst = await DappTools.chkTokenForChannelOpen(tokenId);
      if (checkRst == false) {
        res.json({
          resultCode: 300,
          resultMessage: "The user did not purchase the purchaseId."
        });
        global.Logger.error('/register/channelOpen error : ' + "The user did not purchase the purchaseId.");
        
        return;
      };
    } catch (error) {
      res.json({
        resultCode: 300,
        resultMessage: "PurchaseId does not exist."
      });
      global.Logger.error('/register/channelOpen PurchaseId check error : ' + error.message );
      
      return;
    };

    const envInfo = await DappTools.getConfigData();
    const verifier_url = envInfo[0] + ':' + envInfo[1];
    // global.Logger.debug('verifier_url: ' + verifier_url);

    // verifier 동작여부 체크
    var ret = await isArriveVerifier(verifier_url, tokenId);
    if (ret == false) {
      res.json({
        resultCode: 400,
        resultMessage: 'verifier not running'
      });
      
      global.Logger.error('/register/channelOpen error : verifier not running ' );
      return;
    };
    // global.Logger.debug('isArriveVerifier rst: ' + ret);

    // 이더리움 전송
    const channelId = await DappTools.channelOpen(tokenId, key, totchunkNo);
    const channelOpenPeriod = envInfo[2];

    //verifier 전송
    var formData = new FormData();
    formData.append('channel_id', channelId);
    formData.append('purchase_id', tokenId);
    formData.append('s_pubkey', key);
    formData.append('open_period', channelOpenPeriod);
    formData.append('chunk_file', fs.createReadStream(downChunk));
    ret = httpMultiPart(verifier_url, formData);
    if (ret == false) {
      res.json({
        resultCode: 400,
        resultMessage: 'verifier send error'
      }); 
      
      return;
    };

    res.json({
      resultCode: 0,
      channelId,
      channelOpenPeriod,
    });
    global.Logger.debug('/register/channelOpen : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/register/channelOpen  error : ' + error.message);
  }; 
});

/**
 * 채널 열기시 발급받은 채널 ID를 이용하여 채널을 닫는다.   
 * @type Restful API 
 * @Method POST
 * @URL register/channelClose
 * @Request {string} channelId 채널 Open시 발급 받은 채널 ID 
 * @Request {string} senderLists 콘텐츠 송신자 ID 목록
 * @Request {string} ChunkCountLists 송신자별 chunk 개수    
 * @Request {string} merkleRoot전송영수증의 hash값에 대한 merkle root 값
 * @Response {json} 처리결과
 */
router.post('/channelClose', async function (req, res) {
  try {
    global.Logger.debug('\n/register/channelClose req: ' + JSON.stringify(req.body));
    if (global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;
    const channelId = req.body.channelId;
    const ChunkCountLists = req.body.ChunkCountLists;
    const merkleRoot = req.body.merkleRoot;

    var senderLists = [];
    for (i = 0; i < req.body.senderLists.length; i++) {
      if (req.body.senderLists[i].substr(0, 2).toLowerCase() == "0x") {
        senderLists[i] = req.body.senderLists[i].toLowerCase();
      } else {
        senderLists[i] = "0x" + req.body.senderLists[i].toLowerCase();
      };
    };

    // global.Logger.debug('senderLists.length : ' + senderLists.length);
    // global.Logger.debug('senderLists : ' + senderLists);
    // global.Logger.debug('ChunkCountLists.length : ' + ChunkCountLists.length);
    // global.Logger.debug('ChunkCountLists : ' + ChunkCountLists);
    // global.Logger.debug('merkleRoot : ' + merkleRoot);

    await DappTools.channelOff(channelId, senderLists, ChunkCountLists, merkleRoot);
    res.json({
      resultCode: 0,
      resultMessage: "succeed"
    });
    global.Logger.debug('/register/channelClose : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/register/channelClose error : ' + error.message );
  }; 
});

/**
 * Chunk file을 읽어 전체 chunk갯수를 구한다.  
 * @param {string} chunkFile Chunk file(절대경로) 
 * @returns {totChunkNo}; 전체 chunk 갯수
 */
async function countChunks(chunkFile) {
  let chunkarray = fs.readFileSync(chunkFile).toString().split("\r\n");
  let chunkIn = 1;
  let totChunkNo = 0;
  for (chunkIn = 1; chunkIn < chunkarray.length; chunkIn++) {
    // global.Logger.debug('countChunks: ' + chunkarray[chunkIn]);
    let chunkDataArray = chunkarray[chunkIn].split("\t");
    if (chunkDataArray.length < 2) {
      continue;
    };
    totChunkNo += await countRangeString(chunkDataArray[1]);
  };
  return totChunkNo;
};

/**
 * 범위값에 대한 갯수 계산 
 * @param {string} numString 범위값( ','로 구분된 범위값: n, n-m 형식) 
 *     예: 1,4,5,6,10-21,45,55-399
 * @returns {number} 숫자갯수
 */
async function countRangeString(numString) {
  if (numString == null || numString == "" || numString == "undefined") {
    return 0
  }
  let totCount = 0
  let arrayInfo = numString.split(",")
  for (let i = 0; i < arrayInfo.length; i++) {
    totCount += countRange(arrayInfo[i])
  }
  return totCount
}

/**
 * 범위값에 대한 갯수 계산   
 * @param {string} numString 범위값( n-m 형식) 
 *       예: 10-21 
 * @returns {number} 숫자갯수
 */
function countRange(numString) {
  if (numString == null || numString == "" || numString == "undefined") {
    return 0
  }
  let arrayInfo = numString.split("-")
  if (arrayInfo.length == 1) {
    return 1
  } else {
    return (parseFloat(arrayInfo[1]) - parseFloat(arrayInfo[0]) + 1)
  }
}

/**
 * verifier가 정상동작하는지 체크한다. 
 * @param {string} url verifier URL 
 * @param {string} chkVal verifier가 동작중인지 체크하기 위한 검증 값
 * @returns {결과}; true/false 
 */
async function isArriveVerifier(url, chkVal) {
  global.Logger.debug("isArriveVerifier url :" + "http://" + url + '/preReq');
  if (url.substr(0, 4).toLowerCase() != "http") {
    url = "http://" + url;
  }
  try {
    const response = await axios({
      method: 'post',
      url: url + '/preReq',
      data: {
        'from_id': 'check id',
        'retVal': chkVal
      }
    });

    if (response.status == 200) {
      if (response.data.resultCode == 0) { 
        return true;
      } else {
        global.Logger.error("isArriveVerifier error = " + response.data.result);
      };
    } else {
      global.Logger.error("isArriveVerifier response.status error = " + response.status);
    };
  } catch (e) {
    global.Logger.error("isArriveVerifier.status catch error = " + e.message);
    return false;
  };
  return false;
};

/**
 * verifier로 데이터를 전송한다.  
 * @param {string} url verifier URL 
 * @param {string} formData 전송하기 위한 값 
 * @returns {결과}; true/false 
 */
async function httpMultiPart(url, formData) {
  try {
    global.Logger.debug("httpMultiPart formData : " + JSON.stringify(formData));
    if (url.substr(0, 4).toLowerCase() != "http") {
      url = "http://" + url;
    }

    const response = await axios({
      method: 'post',
      url: url + '/channelOpen',
      data: formData,
      // headers: {'Content-Type': 'multipart/form-data'}
      headers: formData.getHeaders()
    });

    global.Logger.debug("httpMultiPart res: " + JSON.stringify(response.data));
    if (response.status == 200) {
      if (response.data.resultCode == 0) {
        // global.Logger.debug("httpMultiPart send ok");
        return true;
      } else {
        global.Logger.error("httpMultiPart error = " + response.data.msg);
      };
    } else {
      global.Logger.error("httpMultiPart error status =" + response.status + ", err =" + response.statusText);
    };
  } catch (e) {
    global.Logger.error("httpMultiPart error = " + e.message);
    return false;
  };
  return false;
};

module.exports = router;