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
 * 입력된 데이터에 대하여 전자서명을 생성한다.   
 * @type Restful API 
 * @Method POST
 * @URL /dsa/sign
 * @Request {string} inData  전자서명하고자 하는 원문 데이터 
 * @Response {json} 처리결과
 */
router.post('/sign', async function (req, res) {
  try {
    global.Logger.debug('/dsa/sign req: ' + JSON.stringify(req.body));
    if (global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;
    const msg = req.body.inData;
    const sign = await DappTools.signReceipt(msg);
    res.json({
      resultCode: 0,
      signature: sign
    });
  } catch (error) {
    res.json({
      resultCode: 0,
      resultMessage: error.message
    });
    global.Logger.error('error occured: ' + error);
  }; 
});

/**
 * 입력된 전자서명값에 대하여 전자서명을 검사한다   
 * @type Restful API 
 * @Method POST
 * @URL /dsa/verify2
 * @Request {string} signature  json형태의 전자서명 값 
 * @Response {json} 처리결과
 */
router.post('/verify2', async function (req, res) {
  try {
    global.Logger.debug('/dsa/verify2 req: ' + JSON.stringify(req.body));
    if (global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;


    const signJson = JSON.parse(req.body.signature);
    const msg = await DappTools.verifyReceipt(signJson.sign, signJson.pubKey);
    if (msg == null) {
      res.json({
        resultCode: 0,
        verify: false
      });
    } else {
      res.json({
        resultCode: 0,
        verify: true,
        msg
      });
    };
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/dsa/verify2 error: ' + error);
  }; 
})

/**
 * 입력된 전자서명값에 대하여 전자서명을 검사한다   
 * @type Restful API 
 * @Method POST
 * @URL /dsa/verify
 * @Request {string} signature  전자서명 값
 * @Request {string} publicKey  전자서명값을 검증하기 위한 공개키값 
 * @Response {json} 처리결과
 */
router.post('/verify', async function (req, res) {
  try {
    global.Logger.debug('/dsa/verify req: ' + JSON.stringify(req.body));
    if (global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;

    const sign = req.body.signature;
    const pubKey = req.body.publicKey;
    const msg = await DappTools.verifyReceipt(sign, pubKey);
    if (msg == null) {
      res.json({
        resultCode: 0,
        verify: false
      });
    } else {
      res.json({
        resultCode: 0,
        verify: true,
        msg
      });
    };
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/dsa/verify error : ' + error);
  }; 
});

module.exports = router;