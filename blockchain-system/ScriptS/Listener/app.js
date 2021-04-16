/*****************************************************************
*                           Listener  
*
* @author On-off hybrid blockchain technology development team 
* @version 2.5 
* @module Listener 
* @history : 
*****************************************************************/

const Web3 = require('web3');
const jsonBChannel = require('../../TruffLeBToken/build/contracts/BToken.json');
const jsonBmsp = require('../../TruffLeBToken/build/contracts/BMSP.json');
const jsonBProduct = require('../../TruffLeBToken/build/contracts/BProduct.json')

const fs = require('fs')
const request = require('request');
const date = require('date-and-time');

const CONTENT_DATA_INSERT = "i";  //Content data Insert 
const CONTENT_DATA_MODIFY = "m";  //Content data Modify 
const CONTENT_DATA_DELETE = "d";  //Content data Modify 

var Log4JS = require('log4js')
Log4JS.configure({
  appenders: { Listener: { type: 'file', filename: './logs/listener.log', maxLogSize: 524288, backups: 2, compress: true } },
  categories: { default: { appenders: ['Listener'], level: 'error' } }
})

var Logger = Log4JS.getLogger('Listener')
var Env = JSON.parse(fs.readFileSync('./conf/.env.json', 'utf8'))
var ApiEnv = JSON.parse(fs.readFileSync('./conf/.api.json', 'utf8'))

Logger.level = Env.log_level

const {
  NonceTxMiddleware,
  SignedTxMiddleware,
  Client,
  Address,
  LocalAddress,
  LoomProvider,
  CryptoUtils,
  Contracts,
  Web3Signer
} = require('loom-js/dist')

const PrivateKey = CryptoUtils.generatePrivateKey()
const PubLicKey = CryptoUtils.publicKeyFromPrivateKey(PrivateKey)
const CLient = new Client(
  //  'extdev-plasma-us1',
  //  'wss://extdev-plasma-us1.dappchains.com/websocket',
  //  'wss://extdev-plasma-us1.dappchains.com/queryws'
  Env.chain_id,
  Env.write_url,
  Env.read_url
)

CLient.on('error', msg => {
  Logger.error("Client error:" + msg)
})

const WWW3 = new Web3(new LoomProvider(CLient, PrivateKey))
CLient.txMiddleware = [
  new NonceTxMiddleware(PubLicKey, CLient),
  new SignedTxMiddleware(PrivateKey)
]

const NetworkID = Object.keys(jsonBChannel.networks)[0]
const Addr = LocalAddress.fromPublicKey(PubLicKey).toString()
const Con = new WWW3.eth.Contract(
  jsonBChannel.abi,
  jsonBChannel.networks[NetworkID].address, {
  Addr
}
)
const ConBmsp = new WWW3.eth.Contract(
  jsonBmsp.abi,
  jsonBmsp.networks[NetworkID].address, {
  Addr
}
)

const BProduct = new WWW3.eth.Contract(
  jsonBProduct.abi,
  jsonBProduct.networks[NetworkID].address, {
  Addr
}
)

async function saveNoRegData(param) {
  var jsonRData = new Array()
  try {
    var data = fs.readFileSync('./conf/.reData.json', 'utf8')
    jsonRData = JSON.parse(data)
  } catch (err) { }

  jsonRData.push(param)
  const unique = jsonRData.filter((value, idx, arr) => arr.indexOf(value) === idx)

  fs.writeFileSync('./conf/.reData.json', JSON.stringify(unique), 'utf8')
}

async function readNoRegData() {
  try {
    var remainData = fs.readFileSync('./conf/.reData.json', 'utf8')
  } catch (err) { }
  return remainData
}

async function removeNoRegData(param) {
  var jsonRData = new Array()
  try {
    var data = fs.readFileSync('./conf/.reData.json', 'utf8')
    jsonRData = JSON.parse(data)
  } catch (err) { }

  const idx = jsonRData.indexOf(param)
  if (idx > -1) jsonRData.splice(idx, 1)

  fs.writeFileSync('./conf/.reData.json', JSON.stringify(jsonRData), 'utf8')
}

async function remainDataProcess() {
  var remainData = await readNoRegData()

  if (remainData == null || remainData == "") {
    return
  }
  var jsonRData = JSON.parse(remainData)
  for (var i = 0; i < jsonRData.length; i++) {
    getFileInfoByDataId(jsonRData[i], CONTENT_DATA_INSERT)
    removeNoRegData(jsonRData[i])
  }
}

async function httpUtil_req(did, param) {
  try {
    const httpenv = fs.readFileSync('./conf/.httpenv.json', 'utf8')
    var jsonEnv = JSON.parse(httpenv)
    const options = {
      uri: jsonEnv.RegURL + ":" + jsonEnv.RegPort + jsonEnv.SubURL,
      qs: param
    };

    Logger.debug("htttp options: " + JSON.stringify(options))
    request(options, function (err, response, body) {
      if (err) {
        Logger.error("http error =" + err)
        saveNoRegData(did)
        return false
      }
      if (!err && response.statusCode == 200) {
        if (IsJsonString(body) == true) {
          var reqObj = JSON.parse(body)
          if (reqObj.result != 0) {
            Logger.error("http error =" + reqObj.desc)
            return false
          } else {
            Logger.debug("data reg ok")
          }
        } else {
          Logger.error("http response error " + body)
        }
      } else {
        Logger.error("http response response Code : " + response.statusCode)
        saveNoRegData(did)
      }
    })
  } catch (err) {
    Logger.error('error: ' + err)
    saveNoRegData(did)
    return false
  }
  return true
}

function sleep(ms) {
  return new Promise(resolve => { setTimeout(resolve, ms) })
}

async function readEnv() {
  return JSON.parse(fs.readFileSync('./conf/.env.json', 'utf8'))
}

async function writeEnv(obj) {
  fs.writeFileSync('./conf/.env.json', JSON.stringify(obj), 'utf8')
}

async function getFileInfo(fileId) {
  let fileInfo = await Con.methods.getFileDetails1(fileId).call({ from: Addr })
  Logger.debug(JSON.stringify({
    data_id: fileInfo[0],
    chunks: fileInfo[1]
  }))
  return true
}

function IsJsonString(str) {
  try {
    var json = JSON.parse(str);
    return (typeof json === 'object');
  } catch (e) {
    return false;
  }
}
function convertReqData(dataInfo, modeFlag) {

  let infodayStr = ""
  let category1data = ""
  let category2data = ""
  let flagdata = "true"

  let dataInfoJson = ""
  if (IsJsonString(dataInfo[7]) == false) {
    let buff = new Buffer(dataInfo[7], 'base64')
    dataInfoJson = buff.toString('ascii')
    Logger.debug("[convertReqData] base64 info: " + dataInfoJson)
  } else {
    dataInfoJson = dataInfo[7]
  }
  if (IsJsonString(dataInfoJson) == true) {
    let infoData = JSON.parse(dataInfoJson)
    const infoday = date.parse(infoData.date, 'YYYYMMDDHHmmss');
    infodayStr = date.format(infoday, 'YYYY-MM-DDTHH:mm:ss');
    if (dataInfo[6] == false) {
      flagdata = "false"
    }
    category2data = infoData.cat2
    category1data = infoData.cat1
  }

  let data = new Object();
  data.reg = infodayStr  //등록일시
  data.ccid = dataInfo[2] //콘 텐 츠 아 이디
  data.version = dataInfo[3] //콘 텐 츠 버 전
  data.category1 = category1data  //카 테 고리 1
  data.category2 = category2data  //카 테 고리 2
  data.accountid = dataInfo[0]  // 콘 텐 츠 등 록 자 온 체 인 아 이디
  data.flag = flagdata  // 콘 텐 츠 등 록 자 온 체 인 아 이디
  data.mode = modeFlag  // 추가/변경 구분자 'i':추가, 'm';수정, 'd':삭제
  return data
}

//검색노드 및 스토리지 노드로 정보를 정송 
async function getFileInfoByDataId(dataId, modeFlag) {
  let dataInfo = await Con.methods.getDataDetails(dataId).call({ from: Addr })

  //ipfs로 전송
  try {
    var ret = await httpUtil_req(dataId, convertReqData(dataInfo, modeFlag))
    if (ret == false) {
      Logger.error("[getFileInfoByDataId] data reg fail ")
      return 0;
    }
  } catch (error) {
    Logger.error('httpUtil_req convertReqData: ' + error.message)
    return 0;
  }

  //파일 다운로드 결과 등록 
  if (modeFlag == CONTENT_DATA_INSERT) {
    try {

      let resultFlag = await BProduct.methods.isReceive(dataInfo[2], dataInfo[3]).call({
        from: Addr
      })
      Logger.debug("setReceive: resultFlag" + JSON.stringify(resultFlag))

      if (resultFlag[0] == "" || resultFlag[0] == null) {
        let tx = await BProduct.methods.setReceive(dataInfo[2], dataInfo[3], false, false)
          .send({
            from: Addr
          })
          .on("receipt", function (receipt) {
            Logger.debug("receipt: " + JSON.stringify(receipt))
          })
          .on("error", function (error) {
            Logger.error("setReceive error occured: " + error)
          })
        Logger.debug("setReceive: set contents")
      } else {
        Logger.debug("setReceive: aready exist contents")
      }
    } catch (error) {
      Logger.error('setReceive occured: ' + error.message)
    }
  }

  let lastBlock = parseFloat(dataInfo[5])
  return lastBlock
}

//과거 미처리 data 읽기
async function getPastDatas() {
  var dataLength = 0;
  if (ApiEnv.runningMod == "STORAGE") {
    dataLength = await Con.methods.getDataLength().call({ from: Addr })
  } else {
    dataLength = await BProduct.methods.getDataLength().call({ from: Addr })
  }

  var firstBlock = 10000000000000
  var lastBlock = 0

  Logger.debug("[getPastDatas] dataLength: " + dataLength)
  for (var i = 0; i < dataLength; i++) {
    //스토리지 모드 
    if (ApiEnv.runningMod == "STORAGE") {
      let dataId = await Con.methods._DIDs(i).call({ from: Addr })
      Logger.debug("[getPastDatas] dataId: " + dataId)

      block = await getFileInfoByDataId(dataId, CONTENT_DATA_INSERT)
      Logger.debug("[getPastDatas] block: " + block)
    } else {
      let dataId = await BProduct.methods._DIDs(i).call({ from: Addr })
      Logger.debug("[getPastDatas] dataId: " + dataId)

      block = await getFileInfoByDataId(dataId, CONTENT_DATA_INSERT)
      Logger.debug("[getPastDatas] block: " + block)
    }

    if (block < firstBlock) {
      firstBlock = block
    }
    if (block > lastBlock) {
      lastBlock = block
    }
  }
  return { firstBlock, lastBlock }
}

var checkEventCount = 200
var orgEventInterval = Env.event_interval
var atOnceFlag = false
//data 등록 정보 읽기
async function getPastNewFileEvents(lastBlock) {

  let fromBlock = lastBlock + 1
  let returnLastBlock = lastBlock

  if (atOnceFlag == false) {
    Env.event_interval = 50
    atOnceFlag = true
  }

  /** add for limit events count **/
  const latest = await WWW3.eth.getBlock('latest')
  const orgBlockNo = parseInt(latest.blockNumber, 16)

  if ((lastBlock + checkEventCount) > orgBlockNo) {
    checkEventCount = 100
    Env.event_interval = orgEventInterval
    return returnLastBlock
  }

  /** add for limit events count **/
  // Logger.debug('[fromBlock]: ' + fromBlock + ", [toBlock]: " + (fromBlock+checkEventCount))
  const events = await Con.getPastEvents('NewID', {
    fromBlock,
    // 'toBlock': 'latest'
    /** add for limit events count **/
    'toBlock': fromBlock + checkEventCount
    /** add for limit events count **/
  }
    , function (error, events) {
      // Logger.debug( "error events:" + events);
      if (error != null) {
        Logger.error("getPastNewFileEvents events error:" + error);
      }
    })


  if (events == "") {
    /** add for limit events count **/
    returnLastBlock = lastBlock + checkEventCount + 1
    /** add for limit events count **/
    Logger.debug('[returnLastBlock]: ' + returnLastBlock)
    return returnLastBlock
  } else {
    Logger.debug("file events: " + JSON.stringify(events))
    Logger.debug("file events no: " + events.length)
  }


  //for(var i = 0; i < events.length; i++) {
  let block = 0
  for (let i = events.length - 1; i >= 0; i--) {
    //    Logger.debug("file events: " + JSON.stringify(events[i]))
    Logger.debug("file events type: " + events[i].returnValues.flag)
    //스토리지 모드 

    if (ApiEnv.runningMod == "STORAGE") {
      // 신규 DATA 추가
      if (events[i].returnValues.flag === '1') {
        block = await getFileInfoByDataId(events[i].returnValues.Id, CONTENT_DATA_INSERT)
        if (block > returnLastBlock) {
          returnLastBlock = block
        }
      }
    } else { //검색노드 
      // 신규 DATA 추가
      if (events[i].returnValues.flag === '7') {

        let resultFlag = await BProduct.methods.isReceiveD(events[i].returnValues.Id).call({
          from: Addr
        })
        if (resultFlag[3] == true) {
          block = await getFileInfoByDataId(events[i].returnValues.Id, CONTENT_DATA_INSERT)
          if (block > returnLastBlock) {
            returnLastBlock = block
          }
        }
      }
    }

    // 변경 DATA 추가
    if (events[i].returnValues.flag === '6') {
      block = await getFileInfoByDataId(events[i].returnValues.Id, CONTENT_DATA_MODIFY)
      if (block > returnLastBlock) {
        returnLastBlock = block
      }
    }
    Logger.debug("file block: " + block + ", returnLastBlock:" + returnLastBlock)
  }

  returnLastBlock = lastBlock + checkEventCount + 1
  Logger.debug('[returnLastBlock]: ' + returnLastBlock)
  return returnLastBlock
}


/** 
 * data 삭제 이벤트 정보 읽기
 */
var atOnceFlag_Del = false
async function getPastRevokeEvents(lastRevokeBlock) {

  let fromRevokeBlock = lastRevokeBlock + 1
  let returnlastRevokeBlock = lastRevokeBlock
  if (atOnceFlag_Del == false) {
    Env.event_interval = 500
    atOnceFlag_Del = true
  }

  const latestRevoke = await WWW3.eth.getBlock('latest')
  const orgRevokeBlockNo = parseInt(latestRevoke.blockNumber, 16)
  if ((lastRevokeBlock + checkEventCount) > orgRevokeBlockNo) {
    checkEventCount = 5
    Env.event_interval = orgEventInterval
    return returnlastRevokeBlock
  }

  const events = await ConBmsp.getPastEvents('RevokeID', {
    'fromBlock': fromRevokeBlock
    // 'toBlock': 'latest'
    /** add for limit events count **/
    , 'toBlock': fromRevokeBlock + checkEventCount
    /** add for limit events count **/
  }
    , function (error, events) {
      if (error != null) {
        Logger.error("getPastEvents events error:" + error);
      }
    })

  if (events == "") {
    /** add for limit events count **/
    returnLastBlock = lastRevokeBlock + checkEventCount

    return returnLastBlock
  } else {
    Logger.debug("revoke events: " + JSON.stringify(events))
  }
  Logger.debug("getPastRevokeEvents 3")
  for (var i = 0; i < events.length; i++) {
    // DATA 삭제 이벤트
    if (events[i].returnValues.flag === '0') {
      Logger.debug(JSON.stringify(events[i].returnValues.Id))
      block = await getFileInfoByDataId(events[i].returnValues.Id, CONTENT_DATA_DELETE)
    }
  }

  if (events.length) {
    returnLastBlock = parseInt(events[events.length - 1].blockNumber)
  }
  return returnLastBlock
}

 
/**
 * Listener 메일 실행함수 
 */
async function getEvents() {
  //step1 환경정보 읽기
  var env = await readEnv()
  Logger.debug("[getEvents] file env: " + JSON.stringify(env))

  let firstBlock = env.first_data_block
  let lastFileBlock = env.last_file_block
  let lastRevokeBlock = env.last_revoke_block

  //step2 최초 기동인 경우 받지 못한 데이터를 모두 받아온다.
  Logger.debug("lastFileBlock : " + lastFileBlock)
  if (!lastFileBlock) {
    Logger.debug("[lastFileBlock] ")
    let block = await getPastDatas()
    firstBlock = block.firstBlock
    lastFileBlock = block.lastBlock
  }

  //step3 기존 데이터 못받아 오면 오류처리한다.
  if (!lastFileBlock) {
    Logger.debug("[getEvents] No past Data")
    return
  }

  //step4 환경정보에 마지막으로 읽은 블럭을 저장한다.
  env.first_data_block = firstBlock
  env.last_file_block = lastFileBlock
  await writeEnv(env)

  if (!lastRevokeBlock) {
    lastRevokeBlock = firstBlock
  }


  Logger.debug("start server")
  //step5 데몬 생성
  var remainDataRetryCount = 0
  while (1) {
    try {
      //data 등록 정보를 읽어 처리
      // Logger.debug("[lastFileBlock :] " + lastFileBlock)
      lastFileBlock = await getPastNewFileEvents(lastFileBlock)

      //data 삭제처리 정보를 읽어 처리
      lastRevokeBlock = await getPastRevokeEvents(lastRevokeBlock)


      Logger.debug("[lastFileBlock :] " + lastFileBlock + " [lastRevokeBlock :] " + lastRevokeBlock)

      // let littleBlock = lastFileBlock < lastRevokeBlock ? lastFileBlock : lastRevokeBlock
      // let largeBlock = lastFileBlock < lastRevokeBlock ? lastRevokeBlock : lastFileBlock
      // if(largeBlock >= littleBlock) {
      //   lastFileBlock = largeBlock
      //   lastRevokeBlock = largeBlock
      //   env.last_file_block = lastFileBlock
      //   env.last_revoke_block = lastRevokeBlock
      //   // Logger.debug("[save lastFileBlock :] " + lastFileBlock)
      //   writeEnv(env)
      // }

      env.last_file_block = lastFileBlock
      env.last_revoke_block = lastRevokeBlock
      writeEnv(env)

      //http오류로 처리못한 데이터 처리 재시도
      if (remainDataRetryCount >= 10) {
        remainDataRetryCount = 0
        remainDataProcess()
      }
      remainDataRetryCount++

      await sleep(Env.event_interval)
    } catch (err) {
      Logger.error('error: ' + err)
      remainDataRetryCount = 0
      await sleep(Env.event_interval)
    }
  }
}

/**
 * 로그 폴더 생성 
 */
async function chkEnv() {
  var logDir = "./logs"
  if (!fs.existsSync(logDir)) {
    fs.mkdirSync(logDir);
  }
}


//시작 지점
// main start 시점

//========================= HTTP SERVER  =========================
var express = require('express');
var cors = require('cors');
var bodyParser = require('body-parser');
const path = require('path');

var app = express();
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({
  extended: true
}));


/**
* 검색노드 수신 설정 
* @type Restful API 
* @Method POST
* @URL /product/setSearchNode
* @Response {json} 처리결과
*/
app.post('/product/setSearchNode', async function (req, res) {
  try {
    Logger.debug('/setSearchNode req: ' + JSON.stringify(req.body))

    let ccid = req.body.ccid;
    let version = req.body.version;
    let searchflag = req.body.sflag;

    let sflag = false
    if (searchflag == "1") {
      sflag = true
    }

    Logger.debug('/setSearchNode sflag: ' + sflag)
    let tx = await BProduct.methods.setSearchReceive(ccid, version, sflag)
      .send({
        from: Addr
      })
      .on("receipt", function (receipt) {
        Logger.debug("receipt: " + JSON.stringify(receipt))
      })
      .on("error", function (error) {
        Logger.error("setSearchNode error occured: " + error)
      })

    res.json({
      resultCode: 0,
      result: "succeed"
    });

    Logger.debug('/setSearchNode : succeed')
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    })
    Logger.error('error occured: ' + error.message)
  }
})

/**
* 스토리지 노드 수신 설정 
* @type Restful API 
* @Method POST
* @URL /product/setStorageNode
* @Response {json} 처리결과
*/
app.post('/product/setStorageNode', async function (req, res) {
  try {
    Logger.debug('/setStorageNode req: ' + JSON.stringify(req.body))

    let ccid = req.body.ccid;
    let version = req.body.version;
    let storeflag = req.body.tflag;

    let tflag = false
    if (storeflag == "1") {
      tflag = true
    }

    Logger.debug('/setStorageNode tflag: ' + tflag)
    let resultFlag = await BProduct.methods.isReceive(ccid, version).call({
      from: Addr
    })

    //스토리지 노드가 false인경우만 처리 
    if (resultFlag[3] == false) {
      let tx = await BProduct.methods.setStorageReceive(ccid, version, tflag)
        .send({
          from: Addr
        })
        .on("receipt", function (receipt) {
          Logger.debug("receipt: " + JSON.stringify(receipt))
        })
        .on("error", function (error) {
          Logger.error("setStorageNode error occured: " + error)
        })

      res.json({
        resultCode: 0,
        result: "succeed"
      });
    }
    Logger.debug('/setStorageNode : succeed')
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    })
    Logger.error('error occured: ' + error.message)
  }
})

/**
* 수신여부 체크 
* @type Restful API 
* @Method POST
* @URL /product/isReceive
* @Response {json} 처리결과
*/
app.post('/product/isReceive', async function (req, res) {
  try {
    Logger.debug('/isReceive req: ' + JSON.stringify(req.body))

    let ccid = req.body.ccid;
    let version = req.body.version;

    let resultFlag = await BProduct.methods.isReceive(ccid, version).call({
      from: Addr
    })

    Logger.debug('res: ' + JSON.stringify(resultFlag));
    res.json({
      resultCode: 0,
      search: resultFlag[2],
      storage: resultFlag[3]
    });
    Logger.debug('/isReceive : succeed')
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    })
    Logger.error('error occured: ' + error.message)
  }
})

//------------- HTTP SERVER START -------------
let _httpPort = ApiEnv.port
app.listen(_httpPort, () => {
  Logger.debug('http server listening on port ' + _httpPort);
});

chkEnv()
getEvents()



