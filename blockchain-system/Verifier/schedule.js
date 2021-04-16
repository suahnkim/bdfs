/*****************************************************************
*                           Verifier  
*
* @author On-off hybrid blockchain technology development team 
* @version 2.5 
* @module Verifier 
* @description
    - 수신된 전송영수증을 취합처리 
    - 취합이 완료된 건은 정산 및 채널 종료 처리 
* @history : 
*****************************************************************/

var express = require('express');
var cors = require('cors');
var bodyParser = require('body-parser');
var app = express();
var Utils = require('ethereumjs-util');
var Web3 = require('web3');
var jsonBChannel = require('../TruffLeBToken/build/contracts/BChannel.json')
var Nacl = require('tweetnacl')
var fs = require('fs')
const path = require('path')
var multer = require('multer');
var dateUtils = require('date-utils');
var merkle = require('merkle');

var appConstants = require('./appConstant');
var appUtil = require('./apputil.js');
const FileBit = require('./aggUtil.js')

const cluster = require('cluster');
const numCPUs = require('os').cpus().length;
const process = require('process');
//cluster.schedulingPolicy = cluster.SCHED_NONE;

var Log4JS = require('log4js');
// var Env = require('./.env.json');
var Env = JSON.parse(fs.readFileSync('./conf/.env.json', 'utf8'))
var Logger ;

var mysql_dbc = require('./db_con');
var dbPool ;


const {
  Client,
  LocalAddress,
  LoomProvider,
  CryptoUtils,
} = require('loom-js/dist')
// const {
//   NonceTxMiddleware,
//   SignedTxMiddleware,
//   Client,
//   Address,
//   LocalAddress,
//   LoomProvider,
//   CryptoUtils,
//   Contracts,
//   Web3Signer
// } = require('loom-js/dist')


//상수값  정의
const _programName = "Verifier"
const _programVersion = "1.0.0.1"
// 인자값 정의
var _httpPort = 55444
var _httpsPort = 55445
var _procCnt = 10
var _homePath = ""
var _isRunMode = ""
var _isRunNode = ""
var _isTest = false
var _isSingle = false

// 내부 변수
var _logHome = __dirname
var _uploadHome = __dirname + "/upload"

var settleManagerReady = true
var channelManagerReady = true

// 인자값 분석
getParam();

//홈디렉토리 설정
setHomePath();

//로그 설정
setLogger();



//==============================
// db connect test
let rst = dbInit()
if( rst == false ) {
  main_stop()
  return false
}else{
  Logger.info("db connect test ok!")
}



const PrivateKey = CryptoUtils.B64ToUint8Array('r5qRz4n7f6XN4mR5dFUKi3t6d8MF7MtXRoamUBvfS/oE1D9Oaulr4Wb3PfakeNk05V5G78g77u+pglz35mCK4Q==')
const PubLicKey = CryptoUtils.publicKeyFromPrivateKey(PrivateKey)

const CLient = new Client(
  // 'extdev-plasma-us1',
  // 'wss://extdev-plasma-us1.dappchains.com/websocket',
  // 'wss://extdev-plasma-us1.dappchains.com/queryws'

  // 'default',
  // 'ws://127.0.0.1:46658/websocket',
  // 'ws://127.0.0.1:46658/queryws'
  
  //'default',
  //'ws://192.168.4.177:46658/websocket',
  //'ws://192.168.4.177:46658/queryws'
  Env.chain_id,
  Env.write_url,
  Env.read_url
)
const WWW3 = new Web3(new LoomProvider(CLient, PrivateKey))

const Addr = LocalAddress.fromPublicKey(PubLicKey).toString()
const NetworkID = Object.keys(jsonBChannel.networks)[0]
const BChannelCon = new WWW3.eth.Contract(
  jsonBChannel.abi,
  jsonBChannel.networks[NetworkID].address, {
    Addr
  }
)

//------------------------------------------------------------------------
//------------------------------------------------------------------------
/**
 * 사용방법 화면 표기 
 */
function getUsage() {
  console.log( "Usage : verifier [option] [option value]");
  console.log( "   -win32                  Use users folder(only windows os)");
  console.log( "   -homePath [home path]   Set Home path" );
  console.log( "   -mode [mode]            Set Run Mode" );
  console.log( "       mode: a             Run settle mode and aggregate mode" );
  console.log( "       mode: s             Run settle mode " );
  console.log( "       mode: g             Run aggregate mode " );
  console.log( "   -t                      Set TEST Mode" );
  console.log( "   -s                      Single Data Process Mode" );

}

/**
 * 입력된 파라메터 분석 
 */
function getParam() {
  let getParmIdx=0;
  for( getParmIdx=0 ; getParmIdx < process.argv.length ; getParmIdx++ ) {
    if( process.argv[getParmIdx] == "-help" ||process.argv[getParmIdx] == "-?" ) {
      getUsage();
      main_stop();
      return;
    }
    if( process.argv[getParmIdx] == "-win32" ) {
      _isRunNode = process.argv[getParmIdx]
    }
    if( process.argv[getParmIdx] == "-homePath" ) {
      _homePath = process.argv[getParmIdx+1];
      getParmIdx++;
    }
    if( process.argv[getParmIdx] == "-mode" ) {
      _isRunMode = process.argv[getParmIdx+1];
      getParmIdx++;
    }
    if( process.argv[getParmIdx] == "-ProcNo" ) {
      if( process.argv[getParmIdx+1].toLowerCase() == "max") {
        _procCnt = numCPUs;
      }else{
        _procCnt = parseInt( process.argv[getParmIdx+1] );
      }

      getParmIdx++;
    }
    if( process.argv[getParmIdx] == "-t" ) {
      _isTest = true
    }
    if( process.argv[getParmIdx] == "-s" ) {
      _isSingle = true
    }

    
  }
}

/**
 * 폴더 생성함수 
 */
function safeMakeFolder( fol ) {
  if( !fs.existsSync( fol ) ) {
    fs.mkdirSync(fol)
    // var mkdirp = require('mkdirp')
    // await  mkdirp('./omg', function(err){
    //    console.log(err); });
    }
}

/**
 * 홈 폴더를 설정한다.  
 * 참고: 윈도우의 경우 [OS설치드라이브]:/Users/[윈도우 로그인 계정 ID]/AppData/LocalLow/maChain 이 홈폴더
 *       리눅스의 경우 실행한 폴더가 홈폴더
 */
function setHomePath() {
  if( _homePath == "" ) {
    if(_isRunNode == "-win32"  ){
        if( process.platform == "win32" ){
            let _localHome = require('os').homedir()
            _localHome += path.sep + "AppData" +  path.sep +"LocalLow" + path.sep + _programName
            _homePath = _localHome
        }else {
          _homePath =  path.dirname( process.argv[0] )
        }
    }else if( path.win32.basename(process.argv[0]) == "node" || path.win32.basename(process.argv[0]) == "node.exe" ){
      _homePath =  __dirname
    }else {
      _homePath =  path.dirname( process.argv[0] )
    }
  }
  // 기본 폴더 만들기
  safeMakeFolder(_homePath )  // 제품 홈디렉토리
  _logHome = _homePath + path.sep +"logs"
   safeMakeFolder(_logHome ) // 로그 폴더
}

/**
 * 로그 설정
 */
function setLogger() {
  Log4JS.configure({
     appenders: { Verifier: { type: 'file', filename: _logHome + '/schedule_' + _isRunMode + '.log'
        , maxLogSize: 1024000, backups: 2, compress: true }
      },
     categories: { default: { appenders: ['Verifier'], level: 'error' } }
  })

  Logger = Log4JS.getLogger('Verifier')
  Logger.level = Env.log_level
}

/**
 * 스케쥴러 종료 처리 
 */
async function main_stop() {
  await process.exit(1);
}

//==============================
// db connect
async function dbInit() {
  var dbStatus = true;
  dbPool = await  mysql_dbc.init();
  const rst = await  mysql_dbc.sim_query('SELECT 1 from dual',  null)
  if( rst == null ) {
    console.log('db error :' + await mysql_dbc.getErrMsg() );
    Logger.debug('db error :' + await mysql_dbc.getErrMsg() );
    return false;
  }
  return true;
}


//스케줄러
async function manageChannel() {
  Logger.debug("=======================================")
  Logger.debug("channelManager start")
  if (!channelManagerReady) {
    Logger.debug("channelManager in progress")
    return
  }
  channelManagerReady = false   //중복 스케줄러 처리 방지

  try {
    try {
      const connection = await dbPool.getConnection(async conn => conn);
      try {
        // await connection.beginTransaction() // START TRANSACTION

        //master table read receive receipt
        const m_query  = "select M_INFO_SEQ,CHANNEL_ID,PURCHASE_ID,C_STATUS,TOT_FILE_CNT,TOT_CHUNK_CNT,"
                + "RCV_PUB_KEY,CCID,CCID_VER,OPEN_DATE,CLOSE_DATE ,ONCHAIN_DATE ,MERKLE_ROOT ,CHANNEL_FILE ,REG_DATE "
                + " from ma_lst_master where C_STATUS = '2' "

        //master table 전송 미완료건을 찾는다.
        var [rows] = await connection.query( m_query, null ) 
        for( i=0 ; i<rows.length ; i++ ) { 
          
          //step1 전송 미완료 채널의 청크 목록을 가져온다.
          const c_query  = "select C_CHUNK_SEQ, M_INFO_SEQ, C_FILE_ID, CHUNKS_INDEX, CHUNK_CNT "
                            + "from ma_chunk_info where  M_INFO_SEQ = ? and RCV_STATUS = ? "
          var [c_rows] = await connection.query( c_query, [rows[i].M_INFO_SEQ, appConstants.req_STATUS_READY] )
                    
          //step2 파일별 처리
          for( j=0 ; j<c_rows.length ; j++ ){
            if( c_rows[j].CHUNKS_INDEX == null || c_rows[j].CHUNKS_INDEX == "" ) {
              continue;
            }

            //step3 청크 목록 정리
            var fileBit = FileBit.getInstance()
            // fileBit.init(2000);
            var filechunkMax = await appUtil.getMaxValue( c_rows[j].CHUNKS_INDEX )
            fileBit.init(filechunkMax+1);

            var arrayInfo = c_rows[j].CHUNKS_INDEX.split(",") 
            for( k=0 ; k < arrayInfo.length ; k++ ) { 
              if( arrayInfo[k] == null || arrayInfo[k].length == 0 ) {
                continue;
              }
              var aInfo = arrayInfo[k].split("-") 
              if( aInfo.length == 1 ) {
                fileBit.setChunkBit( parseFloat( arrayInfo[k] ) )
              }else{
                fileBit.setChunkBitRang( parseFloat(aInfo[0]), parseFloat(aInfo[1]) )
              }
            }
            var chunk_org = fileBit.serializeChunk() 

            // step4 파일별 전송영수증을 읽는다.
            const d_query2  = "select RECEIPT_SEQ, M_INFO_SEQ, SENDER_ID, RECEIVER_ID, C_FILE_ID, CHUNKS_INDEX, MERKLE_HASH,"
                            +" FILE_NAME, RECV_DATE"
                            +" from ma_receipt_info where M_INFO_SEQ = ? and C_FILE_ID = ? " 

            var [d_rows] = await connection.query( d_query2, [c_rows[j].M_INFO_SEQ, c_rows[j].C_FILE_ID] ) 
            var r_fileBit = FileBit.getInstance()
            r_fileBit.init(filechunkMax+1);

            // step5 파일별 전송영수증의 청크 정리
            for( k=0 ; k<d_rows.length ; k++ ){
              if( d_rows[k].CHUNKS_INDEX == null && d_rows[k].CHUNKS_INDEX == "" ) {
                continue
              } 

              //전송영수증 청크 정리
              var r_arrayInfo = d_rows[k].CHUNKS_INDEX.split(",")
              // Logger.debug("----receive receipt chunk count :" + r_arrayInfo.length )
              for( l=0 ; l < r_arrayInfo.length ; l++ ) {
                var rInfo = r_arrayInfo[l].split("-")
                if( rInfo.length == 1 ) {
                  r_fileBit.setChunkBit( parseFloat( r_arrayInfo[l] ) )
                }else{
                  r_fileBit.setChunkBitRang( parseFloat(rInfo[0]), parseFloat(rInfo[1]) )
                }
              }
            }
 
            var chunk_receipt = r_fileBit.serializeChunk()
            Logger.debug(  "----receive receipt chunk_receipt :" + chunk_receipt + ",  chunk_org     :" + chunk_org )

            //step6 파일별 수신완료 여부 체크
            if( chunk_receipt == chunk_org) {
              //전송영수증 수신완료. 
              const u_query  = "update ma_chunk_info set  RCV_STATUS = ? where C_CHUNK_SEQ = ? "
              var rst = await connection.query( u_query, [appConstants.req_STATUS_COMPLETE,c_rows[j].C_CHUNK_SEQ ] )
            }
          } //for( j=0 ; j<c_rows.length ; j++ ) loop


          //step7 현재 채널이 모두 수신완료 되었는지 검사한다.
          const ch_query  = "select RCV_STATUS, count(1) as cnt from ma_chunk_info "
                          + " where  M_INFO_SEQ = ? group by RCV_STATUS "

          Logger.debug("--check channel's all receipt received. :"  + ch_query )
          var [ch_rows] = await connection.query( ch_query, [rows[i].M_INFO_SEQ] )
          var comp_cnt = 0
          var remain_cnt = 0
          for( l=0 ; l<ch_rows.length ;l++ ){
            if(ch_rows[l].RCV_STATUS == appConstants.req_STATUS_READY ) {
              remain_cnt = ch_rows[l].cnt
            }else if(ch_rows[l].RCV_STATUS == appConstants.req_STATUS_COMPLETE ) {
              comp_cnt =  ch_rows[l].cnt
            }
          }

          Logger.debug("--check channel's all receipt received. comp_cnt  :"  + comp_cnt + ", remain_cnt:" + remain_cnt )
          if( remain_cnt == 0 && comp_cnt > 0 ) {
            // murkle root 계산
            const hash_query  = "select MERKLE_HASH from ma_receipt_info  where M_INFO_SEQ = ? " +
                      " order by RECEIPT_SEQ "
            let [hash_rows] = await connection.query( hash_query, [rows[i].M_INFO_SEQ] )

            let murkle_data = []
            for( mur_i=0 ; mur_i<hash_rows.length ;mur_i++ ) {
              murkle_data.push(hash_rows[mur_i].MERKLE_HASH)
            }

            let use_uppercase = false;  //only toLowerCase
            let merkletree = merkle('sha256', use_uppercase).sync(murkle_data);
            Logger.debug("merkletree :" + merkletree.root() )
            //현재 채널이 모두 받았음
            const ag_query  = "update ma_lst_master set C_STATUS = ?, MERKLE_ROOT = ?, RECEIPT_E_DATE=now()  where M_INFO_SEQ = ?  "
            var rst = await connection.query( ag_query, [appConstants.M_STATUS_RECEIPT_COMPLETE,merkletree.root(), rows[i].M_INFO_SEQ ] )

            Logger.debug("channel close. CHANNEL_ID:" + rows[i].CHANNEL_ID )
          }
        } //for( i=0 ; i<rows.length ; i++ ) loop

        // await connection.commit(); // COMMIT
        connection.release();

      } catch(err) {
        // await connection.rollback(); // ROLLBACK
        connection.release();
        Logger.debug('Query Error ' + err );
      }
    } catch(err) {
      Logger.debug('DB Error');
    }

    channelManagerReady = 1
  } catch (err) {
    Logger.error("error occured: " + err)
    channelManagerReady = 1
  }
    Logger.debug("channelManager end")
}


// 정산처리 및 채널 종료
async function settleChannel() { 
  // Logger.debug("settleChannel start.")
  if (!settleManagerReady) {
    Logger.debug("settleChannel in progress")
    return
  }

  settleManagerReady = false   //중복 스케줄러 처리 방지
  try {
    const connection = await dbPool.getConnection(async conn => conn);
    try {
      // await connection.beginTransaction() // START TRANSACTION

      //master table read receive receipt
      const m_query  = "select M_INFO_SEQ,CHANNEL_ID,PURCHASE_ID,C_STATUS,TOT_FILE_CNT,TOT_CHUNK_CNT,"
              + "RCV_PUB_KEY,CCID,CCID_VER,OPEN_DATE,CLOSE_DATE ,ONCHAIN_DATE ,MERKLE_ROOT ,CHANNEL_FILE ,REG_DATE "
              + " from ma_lst_master where C_STATUS = '3' "

      //master table 전송완료건을 찾는다.
      var [rows] = await connection.query( m_query, null ) 
      for( i_rows=0 ; i_rows<rows.length ; i_rows++ ) {
        // Logger.debug("=>channel receive check, channelid:" + rows[i_rows].CHANNEL_ID  )

        //(송신자,파일)별 청크갯수 구하기
        const c_query  = "select SENDER_ID, RECEIVER_ID, C_FILE_ID, GROUP_CONCAT( CHUNKS_INDEX ) as ALL_CHUNKS "
                        + " from ma_receipt_info where M_INFO_SEQ = ? group by SENDER_ID, RECEIVER_ID, C_FILE_ID "
        let [c_rows] = await connection.query( c_query, [rows[i_rows].M_INFO_SEQ] )
        Logger.debug("=>channel sender, file search count:" + c_rows.length  )
        for(j=0; j<c_rows.length; j++) { 
          let chunkMax = await appUtil.getMaxValue( c_rows[j].ALL_CHUNKS ) 
          var r_fileBit = FileBit.getInstance()
          r_fileBit.init( chunkMax+1 )

          var arrayInfo = c_rows[j].ALL_CHUNKS.split(",")
          for( k=0 ; k < arrayInfo.length ; k++ ) {
            var rInfo = arrayInfo[k].split("-")
            if( rInfo.length == 1 ) {
              r_fileBit.setChunkBit( parseFloat( arrayInfo[k] ) )
            }else{
              r_fileBit.setChunkBitRang( parseFloat(rInfo[0]), parseFloat(rInfo[1]) )
            }
          }
          let chunkCount = r_fileBit.getCount()
          // Logger.debug("=>channel sender,file chunkCount:" + chunkCount  )

          const i_query_sel  = "select count(1) as cnt from ma_receipt_result where M_INFO_SEQ=? and SENDER_ID=? and C_FILE_ID=? "
          let [i_query_rows] = await connection.query( i_query_sel, [rows[i_rows].M_INFO_SEQ, c_rows[j].SENDER_ID, c_rows[j].C_FILE_ID] )

          const i_query  = "insert into ma_receipt_result ( "
                          + "M_INFO_SEQ, SENDER_ID, RECEIVER_ID, C_FILE_ID, CHUNKS_COUNT, COMP_DATE"
                          + " ) values ( ?, ?, ?, ? ,?,  now() )  "
          const i_query_up  = "update ma_receipt_result set  CHUNKS_COUNT = ?, COMP_DATE = now() "
                          + " where M_INFO_SEQ = ? and SENDER_ID=? and C_FILE_ID=? "
          if( i_query_rows[0].cnt <= 0 ) {
            await connection.query( i_query, [rows[i_rows].M_INFO_SEQ, c_rows[j].SENDER_ID, c_rows[j].RECEIVER_ID, c_rows[j].C_FILE_ID, chunkCount ] )
          }else{
            await connection.query( i_query_up, [ chunkCount, rows[i_rows].M_INFO_SEQ, c_rows[j].SENDER_ID,  c_rows[j].C_FILE_ID ] )
          }
        } //end of for(j=0; j<c_rows.length; j++)

        // 정산처리, 채널 닫자
        const r_query  = "select SENDER_ID, sum(CHUNKS_COUNT) as TOTAL_SEND "
                        + " from ma_receipt_result where M_INFO_SEQ = ? group by SENDER_ID "
        let [r_rows] = await connection.query( r_query, [rows[i_rows].M_INFO_SEQ] )
        var senderLists = []
        var chunkLists = []
        for (l=0; l<r_rows.length ; l++) {
          senderLists[l] = r_rows[l].SENDER_ID
          chunkLists[l] = r_rows[l].TOTAL_SEND
        }

        //DB에 정산할 내용을 입력 
        var settle_query  = "insert into ma_settle_channel ( "
          + "M_INFO_SEQ, CHANNEL_ID, MERKLE_ROOT, SENDER_LISTS, CHUNK_LISTS, REG_DATE"
          + " ) values ( ?, ?, ?, ? ,?,  now() )  "
        await connection.query( settle_query, [rows[i_rows].M_INFO_SEQ, rows[i_rows].CHANNEL_ID, rows[i_rows].MERKLE_ROOT,  JSON.stringify(senderLists),  JSON.stringify(chunkLists)] )
         
        //블록체인 싱글처리 
        settle_query  = "update ma_settle_channel set SET_BC=?, MOD_DATE=now() where M_INFO_SEQ=? " 
        if( _isTest == false && _isSingle == true ) {
          try { 

            await BChannelCon.methods.settleChannel( rows[i_rows].MERKLE_ROOT, rows[i_rows].CHANNEL_ID,  senderLists, chunkLists ).send({ from: Addr })
            // Logger.debug('settleChannel complite CHANNEL_ID=',  rows[i_rows].CHANNEL_ID );
          } catch(err) {
            Logger.error('settleChannel Error ' + err );
            let settle_delete_query = "delete from ma_settle_channel where M_INFO_SEQ = ? and  SET_BC=0 " 
            continue;
          }         
          
          await connection.query( settle_query, [appConstants.RESULT_BC_COMPLATE, rows[i_rows].M_INFO_SEQ] ) 
        } else if( _isTest == true ) { 
          await connection.query( settle_query, [appConstants.RESULT_TEST_COMPLATE, rows[i_rows].M_INFO_SEQ] ) 
        }

        if( _isTest == true || _isSingle == true ) {
          const ag_query  = "update ma_lst_master set C_STATUS = ?, JOB_LOCK=0, CLOSE_DATE=now(), ONCHAIN_DATE=now()  where M_INFO_SEQ = ?  "
          var rst = await connection.query( ag_query, [appConstants.M_STATUS_CALCULATION_COMPLETE, rows[i_rows].M_INFO_SEQ ] )
        }
        // Logger.debug("settle receipt: " + rows[i_rows].CHANNEL_ID )
      } //end of for( i=0 ; i<rows.length ; i++ )


      // 배치 정산처리 
      if( _isTest == false && _isSingle == false ) {
        const settle_bc_query  = "select S_INFO_SEQ, M_INFO_SEQ,CHANNEL_ID, MERKLE_ROOT, SENDER_LISTS, CHUNK_LISTS,  REG_DATE, MOD_DATE "
        + " from ma_settle_channel where SET_BC = '0' "
        let [settle_bc_rows] = await connection.query( settle_bc_query, null )
 
        let max_bc_batch_count = 10
        let bc_batch_count = 0
        let totcnt = 0
        let cnts   = new Array();
        let mroots = ""
        let chids  = new Array();
        let senders = new Array();
        let chunks  = new Array();
        let updateRows = ""

        for( ss_rows=0 ; ss_rows<settle_bc_rows.length ; ss_rows++ ) {
          totcnt++ 
          let tmp_chunks = JSON.parse( settle_bc_rows[ss_rows].CHUNK_LISTS ) 
          let tmp_senders = JSON.parse( settle_bc_rows[ss_rows].SENDER_LISTS ) 

          cnts.push( tmp_senders.length  )
          mroots = mroots + settle_bc_rows[ss_rows].MERKLE_ROOT
          chids.push( settle_bc_rows[ss_rows].CHANNEL_ID)
          senders = senders.concat( tmp_senders )
          chunks = chunks.concat(  tmp_chunks ) 

          if(updateRows == "" ) {
            updateRows = settle_bc_rows[ss_rows].M_INFO_SEQ
          }else { 
            updateRows = updateRows + ", " + settle_bc_rows[ss_rows].M_INFO_SEQ
          } 

          if( bc_batch_count == max_bc_batch_count  ) {
            cnts.map(iii=>Number(iii))
            try {
              await BChannelCon.methods.settleChannelAll( totcnt, cnts, mroots, chids, senders, chunks ).send({ from: Addr })
            } catch(err) {
              Logger.debug('All settleChannel Error ' + err );
              totcnt = 0
              cnts.length = 0
              mroots = ""
              chids.length = 0 
              senders.length = 0
              chunks.length = 0
              bc_batch_count = 0
              updateRows = ""
              // Logger.debug("-------------------------------->" )
              continue;
            } 

            let upSettle_query  = "update ma_settle_channel set SET_BC=?, MOD_DATE=now() where M_INFO_SEQ in ( ? ) " 
            await connection.query( upSettle_query, [appConstants.RESULT_BC_COMPLATE, updateRows ] ) 

            upSettle_query  = "update ma_lst_master set C_STATUS = ?, JOB_LOCK=0, CLOSE_DATE=now(), ONCHAIN_DATE=now()  where M_INFO_SEQ in ( ? )  "
            await connection.query( upSettle_query, [appConstants.M_STATUS_CALCULATION_COMPLETE, updateRows ] )

            totcnt = 0
            cnts.length = 0
            mroots = ""
            chids.length = 0 
            senders.length = 0
            chunks.length = 0
            bc_batch_count = 0
            updateRows = ""
          }else { 
            bc_batch_count++
          }
        }
        // Logger.debug("--------------------------------" )
      }

      // await connection.commit(); // COMMIT
      connection.release();
    } catch(err) {
      await connection.rollback(); // ROLLBACK
      connection.release();
      Logger.debug('Query Error ' + err );
    }
  } catch (err) {
    Logger.error("error occured: " + err)
  }
  settleManagerReady = true
  // Logger.debug("settleChannel end.")
}

if( _isRunMode == "a" || _isRunMode == 'g') {
    setInterval(manageChannel, 10000)
}

if( _isRunMode == "a" || _isRunMode == 's') {
  setInterval(settleChannel, 10000)
}
