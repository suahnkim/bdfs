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
 * 입력된 n에  width 길이 만큼 0 padding한다. 
 * @param {string} n 입력값
 * @param {int} width 0 padding 길이 
 * @returns {string} 0 padding된 문자열
 */
function pad(n, width) {
  n = n + '';
  return n.length >= width ? n : new Array(width - n.length + 1).join('0') + n;
};

/**
 * 콘텐츠의 CID를 생성한다.      
 * @type Restful API 
 * @Method POST
 * @URL /cid 
 * @Response {json} 처리결과
 */
router.post('/', async function (req, res) {
  try {
    global.Logger.debug('\n/cid req: ' + JSON.stringify(req.body));
    if (global.Tools == null) {
      res.json({
        resultCode: 300,
        resultMessage: 'not logined'
      });
      return;
    };
    // var EtherTools = global.Tools.EtherTools;
    var DappTools = global.Tools.DappTools;

    var target = "";
    if (req.body.targetID.substr(0, 2).toLowerCase() == "0x") {
      target = req.body.targetID.substr(2, req.body.targetID.length).toLowerCase();
    } else {
      target = req.body.targetID.toLowerCase();
    };

    const cid = await DappTools.getCID(target);
    if (cid == -1) {
      res.json({
        resultCode: 400,
        resultMessage: 'target address is not mapped with dapp address'
      });
    } else {
      res.json({
        resultCode: 0,
        cid: "CID" + pad(cid, 13)
      });
    };
    global.Logger.debug('/cid : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('error occured: ' + error);
  }; 
});

module.exports = router;