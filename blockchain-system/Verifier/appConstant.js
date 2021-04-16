
/*****************************************************************
*                           Verifier  
*
* @author On-off hybrid blockchain technology development team 
* @version 2.5 
* @module Verifier 
* @history : 
*****************************************************************/

module.exports = {
  //상태 코드
  "M_STATUS_CHANNEL_OPEN" : "1"  //채널 Open상태
  ,"M_STATUS_RECEIPT_RECEIVE" : "2"  //영수증 접수중
,  "M_STATUS_RECEIPT_COMPLETE" : "3"  //영수증 접수 완료
  ,"M_STATUS_CALCULATION_COMPLETE" : "4"  //정산 완료

  //chunk 수신 상태
  ,"req_STATUS_READY" : "R"  //수신 대기 
  ,"req_STATUS_COMPLETE" : "C"  //수신완료

  ,"RESULT_NOT_COMPLATE" : "0"  //처리 미완료상태
  ,"RESULT_BC_COMPLATE" : "1"  //블록체인등록완료
  ,"RESULT_TEST_COMPLATE" : "2"  //블록체인 미등록, 테스트 처리 

}
