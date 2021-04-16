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
 * 오프체인 채널 상태를 조회한다.     
 * @type Restful API 
 * @Method POST
 * @URL /validation/channel
 * @Request {string} channelId  등록된 채널의 id 
 * @Response {json} 처리결과
 */
router.post('/channel', async function (req, res) {
  try {
    global.Logger.debug('/validation/channel req: ' + JSON.stringify(req.body));
    if (global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;

    channelId = req.body.channelId;
    var state = ['invalid', 'open', 'off', 'settled'];
    const channelInfo = await DappTools.getChannelDetails(channelId);
    //(_C.receiver, _C.uTokenId, _C.key, _C.deposit, _C.collateral, _C.timestamp, _C.timeout, uint8(_C.state));
    const envInfo = await DappTools.getConfigData();

    if (channelInfo[7] == 1) {
      res.json({
        resultCode: 0,
        validity: state[channelInfo[7]],
        publicKey: channelInfo[2],
        receiptCollection: envInfo[3]
      });
    } else {
      res.json({
        resultCode: 400,
        validity: state[channelInfo[7]],
        publicKey: channelInfo[2]
      });
    };
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/validation/channel error : ' + error);
  }; 
});

/**
 * 구매 상품의 상태를 조회한다.      
 * @type Restful API 
 * @Method POST
 * @URL /validation/token
 * @Request {string} targetId  상태확인하고자 하는 구매자 id
 * @Request {string} cid  콘텐츠의 CID
 * @Response {json} 처리결과
 */
router.post('/token', async function (req, res) {
  try {
    global.Logger.debug('/validation/token req: ' + JSON.stringify(req.body));
    if (global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;

    target = req.body.targetId; 
    var cid = "";
    if (req.body.cid.substr(0, 3) == "CID") {
      cid = Number(req.body.cid.substr(3, req.body.cid.length));
    } else {
      cid = req.body.cid;
    };

    const validity = await DappTools.checkValidToken(target, cid);
    res.json({
      resultCode: 0,
      validity
    });
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/validation/token  error : ' + error);
  }; 
});

module.exports = router;