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
 * 등록된 콘텐츠가 스토리지 노드와 검색노드에서 콘텐츠 다운로드가 완료되었음을 설정한다
 * @type Restful API 
 * @Method POST
 * @URL /product/setreceive
 * @Request {string} ccid 콘텐츠의 CCID
 * @Request {string} version 콘텐츠의 VERSION
 * @Request {string} tflag 스토리지 노드에서 수신완료여부
 * @Request {string} sflag 검색노드에서 수신완료 여부 
 * @Response {json} 처리결과
 */
router.post('/setreceive', async function (req, res) {
  try {
    global.Logger.debug('\n/product/setreceive req: ' + JSON.stringify(req.body));

    let ccid = req.body.ccid;
    let version = req.body.version;
    let searchflag = req.body.sflag;
    let storeflag = req.body.tflag;
    var DappTools = global.Tools.DappTools;

    let sflag = false
    let tflag = false
    if (searchflag == "1") {
      sflag = true
    }
    if (storeflag == "1") {
      tflag = true
    }

    await DappTools.setReceiveData(ccid, version, sflag, tflag);
    res.json({
      resultCode: 0,
      result: "succeed"
    });

  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/product/setreceive error : ' + error);
  }; 
});

/**
 * 등록된 콘텐츠가 검색노드에서 콘텐츠 다운로드가 완료되었음을 설정한다
 * @type Restful API 
 * @Method POST
 * @URL /product/setSearchNode
 * @Request {string} ccid 콘텐츠의 CCID
 * @Request {string} version 콘텐츠의 VERSION 
 * @Request {string} sflag 검색노드에서 수신완료 여부 
 * @Response {json} 처리결과
 */
router.post('/setSearchNode', async function (req, res) {
  try {
    global.Logger.debug('\n/product/setSearchNode req: ' + JSON.stringify(req.body));

    let ccid = req.body.ccid;
    let version = req.body.version;
    let searchflag = req.body.sflag;
    var DappTools = global.Tools.DappTools;

    let sflag = false
    if (searchflag == "1") {
      sflag = true
    }

    let rst = await DappTools.isReceiveData(ccid, version);
    if (rst[2] == true) {
      res.json({
        resultCode: 0,
        result: "succeed"
      }); 
      return;
    }

    await DappTools.setSearchReceiveData(ccid, version, sflag);
    res.json({
      resultCode: 0,
      result: "succeed"
    });
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/product/setSearchNode error : ' + error.message);
  }; 
});

/**
 * 등록된 콘텐츠가 스토리지 노드에서 콘텐츠 다운로드가 완료되었음을 설정한다
 * @type Restful API 
 * @Method POST
 * @URL /product/setStorageNode
 * @Request {string} ccid 콘텐츠의 CCID
 * @Request {string} version 콘텐츠의 VERSION 
 * @Request {string} tflag 스토리지노드에서 수신완료 여부 
 * @Response {json} 처리결과
 */
router.post('/setStorageNode', async function (req, res) {
  try {
    global.Logger.debug('/product/setStorageNode req: ' + JSON.stringify(req.body));

    let ccid = req.body.ccid;
    let version = req.body.version;
    let storeflag = req.body.tflag;
    var DappTools = global.Tools.DappTools;

    let tflag = false
    if (storeflag == "1") {
      tflag = true
    }

    let rst = await DappTools.isReceiveData(ccid, version);
    if (rst[3] == true) {
      res.json({
        resultCode: 0,
        result: "succeed"
      }); 
      return;
    }

    await DappTools.setStorageReceiveData(ccid, version, tflag);
    res.json({
      resultCode: 0,
      result: "succeed"
    });
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/product/setStorageNode error : ' + error.message);
  }; 
});

/**
 * 등록된 콘텐츠의 초도 배포처리가 완료되었는지 확인한다. 
 * @type Restful API 
 * @Method POST
 * @URL /product/isreceive
 * @Request {string} ccid 콘텐츠의 CCID
 * @Request {string} version 콘텐츠의 VERSION  
 * @Response {json} 처리결과
 */
router.post('/isreceive', async function (req, res) {
  try {
    global.Logger.debug('/product/isreceive req: ' + JSON.stringify(req.body));

    let ccid = req.body.ccid;
    let version = req.body.version;
    var DappTools = global.Tools.DappTools;

    let rst = await DappTools.isReceiveData(ccid, version);
    global.Logger.debug('res: ' + rst);
    res.json({
      resultCode: 0,
      search: rst[2],
      storage: rst[3]
    });
  } catch (error) {
    res.json({
      resultCode: 500,
      resultMessage: error.message
    });
    global.Logger.error('/product/isreceive error : ' + error.message);
  }; 
});

module.exports = router;