/*****************************************************************
*                           Chainlink API 
*
* @author On-off hybrid blockchain technology development team 
* @version 2.5 
* @module ChainLink API / main  
* @history : 
*****************************************************************/

var express = require('express');
var cors = require('cors');
var bodyParser = require('body-parser');
const fs = require('fs');
const path = require('path');
//var Env = require('./.env.json');
var Env = JSON.parse(fs.readFileSync(getRumHome() + '/conf/.env.json', 'utf8'))

var Ether = require('./ether.js');
var Dapp = require('./dapp.js');
var Log4JS = require('log4js');
var Log4JSExtend = require('log4js-extend');
var session = require('express-session');
var sessionStore = require('session-file-store')(session);
var ipfilter = require('express-ipfilter').IpFilter;
var IpDeniedError = require('express-ipfilter').IpDeniedError;

var https = require('https');
const cluster = require('cluster');

// ============================= Routes ===========================
const configRouter = require('./routes/config.router');
const accountRouter = require('./routes/account.router');
const sendRouter = require('./routes/send.router');
const mspRouter = require('./routes/msp.router');
const cidRouter = require('./routes/cid.router');
const registerRouter = require('./routes/register.router');
const modifyRouter = require('./routes/modify.router');
const listRouter = require('./routes/list.router');
const infoRouter = require('./routes/info.router');
const validationRouter = require('./routes/validation.router');
const revokeRouter = require('./routes/revoke.router');
const signRouter = require('./routes/sign.router');
const mblockRouter = require('./routes/block.router');
const sendToRouter = require('./routes/sendToVerifier.router');
const product = require('./routes/product.router');

//상수값  정의
const _programName = "maChain";

// 입력 인자값 정의
var _isRunNode = "";
var _runMode = "";
var _httpPort = Env.httpPort;
var _httpsPort = Env.httpsPort;
var _homePath = "";
var _procCnt = Env.procCnt;

// 내부 변수
var _sessionHome = "";
var _logHome = __dirname;


//step1 인자값 분석
getParam();

//step2 홈디렉토리 설정 
setHomePath();

//step3 로그 설정
setLogger()


//step4 multiprocess 생성 

//=========================== Mother Process start  =========================
if (cluster.isMaster) {
  showStartLog()

  // console.log('ppid : ' + process.pid );
  Logger.debug('ppid : ' + process.pid);
  let numReqs = 0;

  // Count requests
  function messageHandler(msg) {
    if (msg.cmd && msg.cmd === 'notifyRequest') {
      numReqs += 1;
    };
  };

  // Start workers and listen for messages containing notifyRequest
  for (let i = 0; i < _procCnt; i++) {
    cluster.fork();
  };

  for (const id in cluster.workers) {
    cluster.workers[id].on('message', messageHandler);
  };

  cluster.on('exit', (worker, code, signal) => {
    console.log(`worker ${worker.process.pid} died`);
    cluster.fork();
  });

} else {
  //=========================== child Process start  =========================
  // console.log('pid : ' + process.pid ); 
  Logger.debug('pid : ' + process.pid);

  // 웹서버 설정 
  app.use(cors());
  app.use(bodyParser.json());
  app.use(bodyParser.urlencoded({
    extended: true
  }));

  // allowed ip address
  let ips = ['::ffff:127.0.0.1', '127.0.0.1', '::1', '::ffff:203.229.154.1']; // local host만 허용 
  app.use(ipfilter(ips, { mode: 'allow' }));
  app.use(function (err, req, res, _next) {
    let userIp = req.connection.remoteAddress;
    res.send('Access Denied');  // page view 'Access Denied'

    Logger.debug('userIp: ' + userIp);
    Logger.debug(' error.message : ' + err.message);

    if (err instanceof IpDeniedError) {
      res.status(401).end();
    } else {
      res.status(err.status || 500).end();
    };
  });

  // set session timeout 
  let sessionTimeout = Env.sessionTimeout; // session timeout, 1hour(1000*60*60)
  app.use(session({
    secret: '//*--usemysession--*//',
    resave: false,
    saveUninitialized: true,
    // cookie:{ expires : new Date(Date.now() + hour)},
    cookie: { maxAge: sessionTimeout },
    store: new sessionStore({ "path": _sessionHome })
  }));

  //------------- Routes use -------------
  app.use('/config', configRouter);
  app.use('/account', accountRouter);
  app.use('/send', sendRouter);
  app.use('/msp', mspRouter);
  app.use('/cid', cidRouter);
  app.use('/register', registerRouter);
  app.use('/list', listRouter);
  app.use('/info', infoRouter);
  app.use('/validation', validationRouter);
  app.use('/revoke', revokeRouter);
  app.use('/dsa', signRouter);
  app.use('/mediablockchain', mblockRouter);
  app.use('/modify', modifyRouter);
  app.use('/send-to-verifier', sendToRouter);
  app.use('/product', product);


  //------------- 초기화 프로그램 -------------
  //Ether, Dapp 에 초기값(홈디렉토리, 로거) 전달 
  initEnv()

  //------------- HTTP SERVER START -------------
  app.listen(_httpPort, () => {
    Logger.debug('http server listening on port ' + _httpPort);
  });

  //------------- HTTPS SERVER START -------------
  let OptionS = {
    method: 'POST',
    key: fs.readFileSync(getRumHome() + path.sep + Env.server_key).toString(),
    cert: fs.readFileSync(getRumHome() + path.sep + Env.server_crt).toString(),
    ca: fs.readFileSync(getRumHome() + path.sep + Env.rootca_crt).toString()
  }

  let HttpsServ = https.createServer(OptionS, app).listen(_httpsPort, function () {
    Logger.debug("https server listening on port " + _httpsPort);
  });
  HttpsServ.timeout = 240000;

  //------------- try connect to Loom -------------
  //주기적으로 loom에 접속하여 통신이 끊기는 현상을 방지
  setInterval(manageClientSession, 60 * 60 * 1000);
}

/**
 * 사용 환경을 초기화한다.   
 */
async function initEnv() {
  Logger.debug('init Home path...');
  await Ether.setHomeDir(_homePath);
  await Ether.setLogger(LoggerEther);
  await Dapp.setHomeDir(_homePath);
  await Dapp.setLogger(LoggerDapp);
}


/**
 * 통신 session 끊기지 않게 하기 위하여 주기적으로 동작하는 통신함수
 * 환경정보를 읽는 기능을 사용한다. 
 */
async function manageClientSession() {
  if (global.Tools != null) {
    let envInfo = await global.Tools.DappTools.getConfigData();
    Logger.debug('testConntect to onchain:' + envInfo[0] + ':' + envInfo[1]);
  }
}

/**
 * 폴더 생성함수 
 */
function safeMakeFolder(fol) {
  if (!fs.existsSync(fol)) {
    fs.mkdirSync(fol);
  };
};

/**
 * 프로그램이 실행한 폴더 위치를 찾는다. 
 * 참고: 환경정보파일을 읽어야 함으로 실제 실행된 위치가 중요  
 */
function getRumHome() {
  if (path.win32.basename(process.argv[0]) == "node" || path.win32.basename(process.argv[0]) == "node.exe") {
    return ".";
  } else {
    return path.dirname(process.argv[0]);
  }
}

/**
 * 홈 폴더를 설정한다.  
 * 참고: 윈도우의 경우 [OS설치드라이브]:/Users/[윈도우 로그인 계정 ID]/AppData/LocalLow/maChain 이 홈폴더
 *       리눅스의 경우 실행한 폴더가 홈폴더
 */
function setHomePath() {
  if (_homePath == "") {
    if (_isRunNode == "-win32") {
      if (process.platform == "win32") {
        var _localHome = require('os').homedir();
        _localHome += path.sep + "AppData" + path.sep + "LocalLow" + path.sep + _programName;
        _homePath = _localHome;
      } else {
        _homePath = path.dirname(process.argv[0]);
      };
    } else if (path.win32.basename(process.argv[0]) == "node" || path.win32.basename(process.argv[0]) == "node.exe" || _isRunNode == "-node") {
      _isRunNode = "-node"; 
      _homePath = __dirname;
    } else {
      _homePath = path.dirname(process.argv[0]);
    };
  }

  // 기본 폴더 생성 
  safeMakeFolder(_homePath);                      // 제품 홈디렉토리
  _logHome = _homePath + path.sep + "logs";
  safeMakeFolder(_logHome);                      // 로그 폴더
  var _certHome = _homePath + path.sep + "keystore";
  safeMakeFolder(_certHome);                     // 인증서 폴더
  _sessionHome = _homePath + path.sep + "sessions";
  safeMakeFolder(_sessionHome);                  // 인증서 폴더
}

/**
 * 로그 설정
 * 참고 : 
 *   로그위치 :  [홈폴더] / logs /
 *   로그파일명 : onchain.log
 *   로그파일 최대크기 : 524288 byte (512Kb) 
 *   로그파일 백업갯수 : 2개 
 */
function setLogger() {
  Log4JS.configure({
    appenders: {
      ApiLog: { type: 'file', filename: _logHome + '/onchain.log', maxLogSize: 524288, backups: 2, compress: true }
      , EtherLog: { type: 'file', filename: _logHome + '/onchain.log', maxLogSize: 524288, backups: 2, compress: true }
      , DAppLog: { type: 'file', filename: _logHome + '/onchain.log', maxLogSize: 524288, backups: 2, compress: true }
    },
    categories: { default: { appenders: ['ApiLog'], level: 'error' } }
  });
 
  Log4JSExtend(Log4JS, {
    path: _logHome,
    format: "at @name (@file:@line:@column)"
  });

  global.Logger = Log4JS.getLogger('ApiLog');
  global.LoggerEther = Log4JS.getLogger('EtherLog');
  global.LoggerDapp = Log4JS.getLogger('DAppLog');

  Logger.level = Env.log_level;
  LoggerEther.level = Env.log_level;
  LoggerDapp.level = Env.log_level;
}

/**
 * 프로그램 종료 함수 
 */
async function main_stop() {
  await process.exit(1);
}

/**
 * 사용방법 화면 표기 
 */
function getUsage() {
  console.log("Usage : mkonapi [option] [option value]");
  console.log("   -?, -help               Show help");
  console.log("   -runMode                Use running mode");
  console.log("   -node                   Use node program");
  console.log("   -win32                  Use users folder(only windows os)");
  console.log("   -homePath [home path]   Set Home path");
  console.log("   -httpPort [http port]   Set http port");
  console.log("   -httpsPort [https port] Set https port");
  console.log("   -ProcNo [process count] Set process count");
  console.log("           process count : 0~cpu number");
  console.log("                           'max' = cpu number");
}

/**
 * 프로그램 시작 메시지 
 */
function showStartLog() {
  Logger.debug('===============================================================');
  Logger.debug('http server running path:' + _homePath);
  Logger.debug('_runMode: ' + _runMode);
  Logger.debug('_homePath: ' + _homePath);
  Logger.debug('_httpsPort: ' + _httpsPort);
  Logger.debug('_httpPort: ' + _httpPort);

  console.log('===============================================================');
  console.log('http server running path:' + _homePath);
  console.log('_runMode: ' + _runMode);
  console.log('_httpsPort: ' + _httpsPort);
  console.log('_httpPort: ' + _httpPort);
  console.log('_isRunNode: ' + _isRunNode);
  console.log('_homePath: ' + _homePath);
  console.log('_logHome: ' + _logHome);

  console.log('===============================================================');
}

/**
 * 입력된 파라메터 분석 
 */
function getParam() {
  let getParmIdx = 0;
  for (getParmIdx = 0; getParmIdx < process.argv.length; getParmIdx++) {
    if (process.argv[getParmIdx] == "-help" || process.argv[getParmIdx] == "-?") {
      getUsage();
      main_stop();
      return;
    }
    if (process.argv[getParmIdx] == "-runMode") {
      _runMode = process.argv[getParmIdx + 1];
      getParmIdx++;
    };
    if (process.argv[getParmIdx] == "-httpPort") {
      _httpPort = parseInt(process.argv[getParmIdx + 1]);
      getParmIdx++;
    };
    if (process.argv[getParmIdx] == "-httpsPort") {
      _httpsPort = parseInt(process.argv[getParmIdx + 1]);
      getParmIdx++;
    };
    if (process.argv[getParmIdx] == "-homePath") {
      _homePath = process.argv[getParmIdx + 1];
      getParmIdx++;
    };
    if (process.argv[getParmIdx] == "-node") {
      _isRunNode = process.argv[getParmIdx];
    };
    if (process.argv[getParmIdx] == "-win32") {
      _isRunNode = process.argv[getParmIdx];
    };
    if (process.argv[getParmIdx] == "-ProcNo") {
      _procCnt = parseInt(process.argv[getParmIdx + 1]);
      getParmIdx++;
    };
  };
}