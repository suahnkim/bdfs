/*****************************************************************
*                           Chainlink API 
*
* @author On-off hybrid blockchain technology development team 
* @version 2.5 
* @module ChainLink API / router 
* @history : 
*****************************************************************/

var express = require('express');
var Ether = require('../ether.js');
var Dapp = require('../dapp.js');

var router = express.Router();
global.Tools = null; // 전역 변수(initTools)

/**
 * 사용 Ethereum과 Dappchain을 초기화한다. 
 * @param {string} address 사용자 계정 ID  
 * @param {string} password 계정ID의 Private Key Password 
 * @returns {EtherTools,DappTools}; 초기화된 Class 
 */
async function initTools(address, password) {
  /* init Ethereum elements */

  // global.Logger.debug('init ethereum tools...');
  var EtherTools = await Ether.createAsync(address, password);
  
  /* init Dappchain elements */
  // global.Logger.debug('init dapp tools...' + EtherTools.getDappPrivateKey());
  var DappTools = await Dapp.createAsync(EtherTools.getDappPrivateKey());
  
  global.Logger.debug('init complete');
  return {
    EtherTools,
    DappTools
  };
};

/**
 * 사용자의 계정을 생성한다. 
 * @type Restful API 
 * @Method POST
 * @URL /account/generate  
 * @Request {string} password 생성하고자 하는 계정의 Password 
 * @Response {json} 처리결과
 */
router.post('/generate', async function (req, res) {
  try {
    global.Logger.debug('\n/account/generate req: ' + JSON.stringify(req.body));
    const password = req.body.password;
    const address = await Ether.generateAccount(password);

    var ToolsForMapping = await initTools(address[1], password);
    var EtherTools = ToolsForMapping.EtherTools;
    var DappTools = ToolsForMapping.DappTools;
    
    const mappingResult = await DappTools.SignAsync(EtherTools.getWallet());
    res.json({
      resultCode: 0,
      state: 'new',
      accountId: mappingResult.ethAddress.toLowerCase(),
      dappAddress: mappingResult.dappAddress.toLowerCase()
    });
    global.Logger.debug('/account/generate : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/account/generate error : ' + error.message);
  };
});

/**
 * 사용자의 개인키를 불러오고, 개인키를 이용하여 사용자 계정정보를 등록한다. 
 * @type Restful API 
 * @Method POST
 * @URL /account/import
 * @Request {string} privateKey 이더리움 계정의 개인키(hex 인코딩된 값) 
 * @Request {string} password 입력한 개인키의 비밀번호 
 * @Response {json} 처리결과
 */
router.post('/import', async function (req, res) {
  try { 
    global.Logger.debug('\n/account/import req: ' + JSON.stringify(req.body));
    const privateKey = req.body.privateKey;
    const password = req.body.password;

    var _privateKey = "";
    if (privateKey.substr(0, 2).toLowerCase() != "0x") {
      _privateKey = "0x" + privateKey.toLowerCase();
    } else {
      _privateKey = privateKey.toLowerCase();
    };

    const address = await Ether.importAccount(_privateKey, password);
    var ToolsForMapping = await initTools(address[1], password);
    var EtherTools = ToolsForMapping.EtherTools;
    var DappTools = ToolsForMapping.DappTools;

    const state = address[0] ? "new" : "exists";
    const mappingResult = await DappTools.SignAsync(EtherTools.getWallet());
    res.json({
      resultCode: 0,
      state,
      accountId: mappingResult.ethAddress.toLowerCase(),
      dappAddress: mappingResult.dappAddress.toLowerCase()
    });
    global.Logger.debug('/account/import : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/account/import error: ' + error.message);
  }; 
});

/**
 * 사용자가 다른 PC로 이전하기 위하여 개인키를 export한다.   
 * @type Restful API 
 * @Method POST
 * @URL /account/export 
 * @Request {string} accountId 이더리움 계정의 개인키(hex 인코딩된 값) 
 * @Request {string} password 입력한 개인키의 비밀번호 
 * @Response {json} 처리결과
 */
router.post('/export', async function (req, res) {
  try {
    global.Logger.debug('\n/account/export req: ' + JSON.stringify(req.body));
    var address = "";

    if (req.body.accountId.substr(0, 2).toLowerCase() == "0x") {
      address = req.body.accountId.substr(2, req.body.accountId.length).toLowerCase();
    } else {
      address = req.body.accountId.toLowerCase();
    };

    const password = req.body.password;
    const privateKey = await Ether.exportAccountB64(address, password);
    const publicKey = await Ether.exportAccountPubKeyB64(address, password);
    res.json({
      resultCode: 0,
      privateKey,
      publicKey
    });
    global.Logger.debug('/account/export : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/account/export error: ' + error.message);
  };
});

/**
 * 사용자 PC에서 등록되어 있는 계정의 등록을 취소한다.    
 * @type Restful API 
 * @Method POST
 * @URL /account/remove 
 * @Request {string} deleteId 삭제하고자 하는 계정 ID  
 * @Response {json} 처리결과
 */
router.post('/remove', async function (req, res) {
  try { 
    global.Logger.debug('\n/account/remove req: ' + JSON.stringify(req.body));
    var address = "";
    if (req.body.deleteId.substr(0, 2).toLowerCase() == "0x") {
      address = req.body.deleteId.substr(2, req.body.deleteId.length).toLowerCase();;
    } else {
      address = req.body.deleteId.toLowerCase();
    };

    const state = await Ether.removeAccount(address);
    res.json({
      resultCode: 0,
      state
    });
    global.Logger.debug('/account/remove : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/account/remove error: ' + error.message);
  };
});

/**
 * 사용자 PC에서 등록되어 있는 계정 목록을 반환한다.      
 * @type Restful API 
 * @Method POST
 * @URL /account/list  
 * @Response {json} 처리결과
 */
router.post('/list', async function (req, res) {
  try { 
    global.Logger.debug('\n/account/list req: ' + JSON.stringify(req.body));
    const fileList = await Ether.listAccount();
    let list = [];
    for (var i = 0; i < fileList.length; i++) {
      list.push('0x' + fileList[i].split('--')[2].toLowerCase());
    };
    res.json({
      resultCode: 0,
      list
    });
    global.Logger.debug('/account/list : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/account/list error: ' + error.message);
  };
});

/**
 * ChainLink API를 사용하기위하여 API에 로그인한다.   
 * @type Restful API 
 * @Method POST
 * @URL /account/login 
 * @Request {string} accountId 사용하고자 하는 계정의 ID  
 * @Request {string} password accountId가 사용하는 개인키의 비밀번호  
 * @Response {json} 처리결과
 */
router.post('/login', async function (req, res) {
  try { 
    global.Logger.debug('\n/account/login req: ' + JSON.stringify(req.body));
    var address = "";

    if (req.body.accountId.substr(0, 2).toLowerCase() == "0x") {
      address = req.body.accountId.substr(2, req.body.accountId.length).toLowerCase();
    } else {
      address = req.body.accountId.toLowerCase();
    };

    const password = req.body.password;
    global.Tools = await initTools(address, password);

    //generate session
    req.session.userId = address;

    res.json({
      resultCode: 0,
      state: 'succeed'
    });

    global.Logger.debug('/account/login : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/account/login error: ' + error.message);
  };
});

/**
 * ChainLink API 사용을 중단하거나, 다른 계정으로 접속하기 위하여 로그아웃처리한다.  
 * - 함수 수행전 /account/login가 실행 필요 
 * @type Restful API 
 * @Method POST
 * @URL /account/logout  
 * @Response {json} 처리결과
 */
router.post('/logout', async function (req, res) {
  try { 
    await global.Tools.DappTools.SessinClose();

    global.Logger.debug('\n/account/logout req: ' + JSON.stringify(req.body));
    global.Tools = null; // initTools data : null

    req.session.destroy(); //  destory session
    res.clearCookie('sid'); // 세션 쿠키 삭제

    res.json({
      resultCode: 0,
      state: 'succeed'
    });
    global.Logger.debug('/account/logout : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/account/logout error: ' + error.message);
    
  };
});

/**
 * 사용자의 계정이 소유하고 있는 잔고의 금액을 반환한다. 
 * - 함수 수행전 /account/login가 실행 필요
 * @type Restful API 
 * @Method POST
 * @URL /account/balance 
 * @Response {json} 처리결과
 */
router.post('/balance', async function (req, res) {
  try { 
    global.Logger.debug('\n/account/balance req: ' + JSON.stringify(req.body));

    if (Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    var EtherTools = Tools.EtherTools;
    var DappTools = Tools.DappTools;

    const ethAddress = EtherTools.getWallet().getAddressString();
    const ethBalance = await EtherTools.GetBaLanceAsync(ethAddress);
    const dappBalance = await DappTools.GetBaLanceAsync();

    res.json({
      resultCode: 0,
      ethAddress,
      ethBalance,
      dappBalance
    });
    global.Logger.debug('/account/balance : succeed');

  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/account/balance error: ' + error.message);
  };
});

module.exports = router;