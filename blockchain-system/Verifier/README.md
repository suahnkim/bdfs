# Verifier APIs

## 실행방법 
  - 실행 전 모듈 설치 
    > yarn
  - 실행 전 환경 설정 방법 
   1. /conf/.env.json 파일을 열어 로그를 설정한다. 
     log_level : 로그 설정( 디버그 : "debug", 오류 : "error" ) 	 
   2. /conf/.env.json 파일을 열어 https서버 실행을 위한 인증서 정보를 설정한다. 
      rootca_crt : root 인증서
      server_crt : https 인증서
      server_key : https 개인키 
   3. /conf/.env.json 파일을 열어 블록체인 정보를 설정한다. 
      chain_id, write_url, read_url, blockchain_url
   4. /conf/runenv.json 파일을 열어 폴더를 설정한다. 
      upFolder : 전송영수증 파일이 업로드되는 폴더 
	  receiptFolder : 전송영수증이 보관되는 폴더 

### 전송영수증 수신 서버 
  - 실행방법 
    > node app.js [option] [option value] 	
  - 실행 옵션 
    -win32                  Use users folder(only windows os)
    -homePath [home path]   Set Home path
    -httpPort [http port]   Set http port
    -httpsPort [https port] Set https port 
    -ProcNo [process count] Set process count
           process count : 0~cpu number
                           'max' = cpu number
### 스케쥴러 
  - 실행방법 
    > node schedule.js [option] [option value] 	
  - 실행 옵션  
    -win32                  Use users folder(only windows os)
    -homePath [home path]   Set Home path
    -mode [mode]            Set Run Mode
       mode: a             Run settle mode and aggregate mode
       mode: s             Run settle mode 
       mode: g             Run aggregate mode 
    -t                      Set TEST Mode
    -s                      Single Data Process Mode
	
### 실행 예 
    > node app.js -ProcNo 10 
    > node schedule.js -mode s -s 
	> node schedule.js -mode g -s

## 배포방법 
  - 윈도우용 모듈 배포 
   > pkg --out-path dist --targets node10-win-x86 app.js
   > pkg --out-path dist --targets node10-win-x86 schedule.js 
   > cp [패키지안에 미포함 모듈 명] ./dist/
     '패키지안에 미포함 모듈 명'은 pkg 명령을 수행하면 미포함된 모듈의 경로와 이름을 출력된다. 
   > cp ./conf ./dist/ 
   > cd ./dist/
   > mv app verifier.exe 
   > mv schedule schedule.exe 
   > [폴더 전체를 압축] 
   
  - Linux용 모듈 배포 
   > pkg --out-path dist --targets node8-linux-x64 app.js
   > pkg --out-path dist --targets node10-win-x86 schedule.js 
   > cp [패키지안에 미포함 모듈 명] ./dist/
     '패키지안에 미포함 모듈 명'은 pkg 명령을 수행하면 미포함된 모듈의 경로와 이름을 출력된다. 
   > cp -r ./conf ./dist/ 
   > cd ./dist/
   > mv app verifier 
   > tar cvf verifier_linux.tar verifier schedule *.node conf 
   > gzip verifier_linux.tar 

## API 설명 
   verifier api 설명서 참조 
