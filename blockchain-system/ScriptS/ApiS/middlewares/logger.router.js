var Log4JS = require('log4js');
var Log4JSExtend = require('log4js-extend');
var express = require('express');
var Env = require('../.env.json');

var router = express.Router();

// 내부 변수
var _logHome = __dirname;

// 로그 설정
Log4JS.configure({
    appenders: { ApiLog: { type: 'file', filename: _logHome + '/onchain.log', maxLogSize: 524288, backups: 2, compress: true }
                 , EtherLog: { type: 'file', filename: _logHome + '/onchain.log', maxLogSize: 524288, backups: 2, compress: true }
                 , DAppLog: { type: 'file', filename: _logHome + '/onchain.log', maxLogSize: 524288, backups: 2, compress: true }
    },
    categories: { default: { appenders: ['ApiLog'], level: 'error' } }
});

Log4JSExtend(Log4JS, {
    path: _logHome,
    format: "at @name (@file:@line:@column)"
});

var Logger = Log4JS.getLogger('ApiLog');
var LoggerEther = Log4JS.getLogger('EtherLog');
var LoggerDapp = Log4JS.getLogger('DAppLog');

Logger.level = Env.log_level;
LoggerEther.level = Env.log_level;
LoggerDapp.level = Env.log_level;

Logger.debug('=============Logger Success===========');



module.exports = router;