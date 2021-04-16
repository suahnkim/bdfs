# 온체인 APIs

## 실행방법 
  - 실행 전 모듈 설치 
    > yarn
  - 실행 전 환경 설정 방법 
   1. /conf/.env.json 파일을 열어 Listener의 실행포트, 실행모드를 설정한다. 
     httpPort : http 실행포트(default : 55442 ) 
	 httpsPort : https 실행포트(default : 55443 )
	 procCnt : 프로세스 실행 갯수(default 1) 
	 sessionTimeout : 세션 종료 시간 	 
   2. /conf/.env.json 파일을 열어 로그를 설정한다. 
      log_level : 로그 설정( 디버그 : "debug", 오류 : "error" ) 	  
   3. /conf/.env.json 파일을 열어 블록체인 정보를 설정한다. 
      chain_id, write_url, read_url, blockchain_url
   4. /conf/.env.json 파일을 열어 https서버 실행을 위한 인증서 정보를 설정한다. 
      rootca_crt : root 인증서
      server_crt : https 인증서
      server_key : https 개인키 
   5. /conf/.env.json 파일을 열어 hotwallet, Verifier 서버정보를 설정한다. 
     hot_wallet_url : hotwallet url
	 hot_wallet_port : hotwallet port 
	 verifier_url : verifier url 
	 verifier_port : verifier port 
   
  - 실행방법 
    > node app.js [option] [option value] 	
  - 실행 옵션 
    -?, -help               Show help
    -runMode                Use running mode
    -node                   Use node program
    -win32                  Use users folder(only windows os)");
    -homePath [home path]   Set Home path
    -httpPort [http port]   Set http port
    -httpsPort [https port] Set https port
    -ProcNo [process count] Set process count
             process count : 0~cpu number
			          'max' = cpu number

## 배포방법 
  - 윈도우용 모듈 배포 
   > pkg --out-path dist --targets node10-win-x86 app.js 
   > cp [패키지안에 미포함 모듈 명] ./dist/
     '패키지안에 미포함 모듈 명'은 pkg 명령을 수행하면 미포함된 모듈의 경로와 이름을 출력된다. 
   > cp ./conf ./dist/
   > cp ./ssl_cert ./dist 
   > cd ./dist/
   > mv app mkonapi.exe 
   > [폴더 전체를 압축] 
   
  - Linux용 모듈 배포 
   > pkg --out-path dist --targets node8-linux-x64 app.js
   > cp [패키지안에 미포함 모듈 명] ./dist/
     '패키지안에 미포함 모듈 명'은 pkg 명령을 수행하면 미포함된 모듈의 경로와 이름을 출력된다. 
   > cp -r ./conf ./dist/
   > cp -r./ssl_cert ./dist 
   > cd ./dist/
   > mv app mkonapi 
   > tar cvf chainlink_linux.tar mkonapi *.node ssl_cert conf 
   > gzip chainlink_linux.tar 

## API 설명 
   chainlink api 설명서 참조 
