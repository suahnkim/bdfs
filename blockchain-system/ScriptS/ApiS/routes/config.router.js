/*****************************************************************
*                           Chainlink API 
*
* @author On-off hybrid blockchain technology development team 
* @version 2.5
* @module ChainLink API / router 
* @history : 
*****************************************************************/

var express = require('express');
var router = express.Router();

/**
 * Chainlink API 사용 환경정보를 설정
 * @type Restful API 
 * @Method POST
 * @URL /config/setconfig
 * @param {string} verifierUrl Verifier 접속주소
 * @param {string} verifierPort Verifier 접속 포트
 * @param {string} channelOpenPeriod 채널 OPEN 기간
 * @param {string} receiptCollection 전송영수증 취합 갯수
 * @Response {json} 처리결과
 */
router.post('/setconfig', async function (req, res) {
  try {
    global.Logger.debug('\n/config/setconfig req: ' + JSON.stringify(req.body));
    //Logger.debug('req: ' + req.body)
    //verifierUrl, verifierPort, channelOpenPeriod, receiptCollection
    //string inUrl, uint inPort, uint inPeriod, uint inCol
    const verifierUrl = req.body.verifierUrl;
    const verifierPort = req.body.verifierPort;
    const channelOpenPeriod = req.body.channelOpenPeriod;
    const receiptCollection = req.body.receiptCollection;
    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;

    let chunkPrice = 0;
    let depositRatio = 0;
    let timeoutMili = 0;
    chunkPrice = req.body.chunkPrice  ;
    depositRatio =  req.body.depositRatio ; 
    timeoutMili = req.body.timeoutMili ; 

    chunkPrice =  chunkPrice == undefined ? 10 : Number( chunkPrice);
    depositRatio =   depositRatio == undefined ? 100 :  Number( depositRatio); 
    timeoutMili =  timeoutMili == undefined ? 10000 :   Number( timeoutMili); 

    if (chunkPrice == 0) {
      chunkPrice = 1;
    };
    if (depositRatio == 0) {
      depositRatio = 10;
    };
    if (timeoutMili == 0) {
      timeoutMili = 10000;
    };

    await DappTools.setConfigData(verifierUrl, verifierPort, channelOpenPeriod, receiptCollection, chunkPrice, depositRatio, timeoutMili);
    res.json({
      resultCode: 0,
      result: "/config/setconfig succeed"
    });
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/config/setconfig error: ' + error.message);
  }; 
});

/**
 * Chainlink API 사용 환경정보를 반환
 * @type Restful API 
 * @Method POST
 * @URL /config/getconfig
 * @Response {json} 처리결과
 */
router.post('/getconfig', async function (req, res) {
  try {
    global.Logger.debug('\n/config/getconfig req: ' + JSON.stringify(req.body));

    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;
    const envInfo = await DappTools.getConfigData();
    // global.Logger.debug('res: ' + JSON.stringify(envInfo));

    //verifierUrl, verifierPort, channelOpenPeriod, receiptCollection
    res.json({
      resultCode: 0,
      verifierUrl: envInfo[0],
      verifierPort: envInfo[1],
      channelOpenPeriod: envInfo[2],
      receiptCollection: envInfo[3]
    });
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/config/getconfig error : ' + error);
  }; 
});

module.exports = router;