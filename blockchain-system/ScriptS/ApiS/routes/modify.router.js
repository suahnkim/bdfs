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
router.post('/data', async function (req, res) {
  try {
    global.Logger.debug('/modify/data req: ' + JSON.stringify(req.body));
    if(global.Tools == null) {
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

    if(dataid == null || dataid == "" ) {
      res.json({
        resultCode: 300,
        resultMessage: 'dataId is empty.'
      }); 
      global.Logger.error('/modify/data error: dataId is empty.');
      return;
    };

    let dataInfo = await DappTools.getDataDetailsWithId(dataid); 

    //set distributor
    if(targetDist == null || targetDist=="" ) {
      targetDist = dataInfo[8];
    };

    //set content buyer
    if(targetUser == null || targetUser=="" ) {
      targetUser = dataInfo[9];
    };
    if(UsageRestriction == null || UsageRestriction=="" ) {
      UsageRestriction = await DappTools.getDataAtDetailsID(dataid);
    }; 
    const rstdataId = await DappTools.modifyData(dataid, info, targetDist, targetUser, UsageRestriction);
    res.json({
      resultCode: 0,
      dataId:rstdataId
    });

    global.Logger.debug('/modify/data : succeed');
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/modify/data error: ' + error);
  }; 
});

module.exports = router;