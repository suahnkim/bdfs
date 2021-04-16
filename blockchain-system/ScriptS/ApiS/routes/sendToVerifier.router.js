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
 * 오프체인에서 생성된 전송 영수증을 게시한다.
 * @type Restful API 
 * @Method POST
 * @URL /send-to-verifier
 * @Request {string} channelId 채널 ID
 * @Request {string} receipt 수신자가 영수증에 대하여 전자서명한 전송영수증  
 * @Response {json} 처리결과
 */
router.post('/', async function (req, res) {
  try {
    global.Logger.debug('send-to-verifier req: ' + JSON.stringify(req.body));
    if(global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;

    const channelId = req.body.channelId;
    const receipt = req.body.receipt;
    const result = await DappTools.sendAggregatedReceipt(channelId, receipt);
    // global.Logger.debug(result);
    res.json( result );
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('send-to-verifier error: ' + error);
  }; 
});


module.exports = router;