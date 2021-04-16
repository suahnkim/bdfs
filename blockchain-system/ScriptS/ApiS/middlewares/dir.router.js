const path = require('path');
const fs = require('fs');

var express = require('express');
var Ether = require('../ether.js');
var Dapp = require('../dapp.js');

var router = express.Router();

// 상수값 정의
const _programName = "maChain";

// 인자값 정의
var _homePath = "";
var _isRunNode = "";

// 내부변수
var _logHome = __dirname;
var _sessionHome = "";

if(process.argv[i] == "-node") {
    _isRunNode = process.argv[i];
};
if(process.argv[i] == "-win32") {
    _isRunNode = process.argv[i];
};

// get homedir
var homePath = "";
function safeMakeFolder(fol) {
  if( !fs.existsSync(fol)) {
    fs.mkdirSync(fol);
  };
};

if(_homePath == "") {
    if(_isRunNode == "-win32"){
        if(process.platform == "win32"){
            var _localHome = require('os').homedir();
            _localHome += path.sep + "AppData" +  path.sep +"LocalLow" + path.sep + _programName;
            homePath = _localHome;
        }else {
          homePath =  path.dirname(process.argv[0]);
        };
    }else if(path.win32.basename(process.argv[0]) == "node" || path.win32.basename(process.argv[0]) == "node.exe"  || _isRunNode == "-node"){
      console.log("run node proigram");
      homePath =  __dirname;
    }else{
        homePath =  path.dirname(process.argv[0]);
    };
}else{
    homePath = _homePath;
};

// 기본 폴더 만들기
safeMakeFolder(homePath);  // 제품 홈디렉토리
_logHome = homePath + path.sep +"logs";
safeMakeFolder(_logHome); // 로그 폴더
var _certHome = homePath + path.sep +"keystore";
safeMakeFolder(_certHome); // 인증서 폴더
_sessionHome = homePath + path.sep +"sessions";
safeMakeFolder(_sessionHome); // 인증서 폴더

module.exports = router;