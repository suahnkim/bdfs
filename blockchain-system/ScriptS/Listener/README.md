# Listener APIs 

## 실행방법 
  - 실행 전 모듈 설치 
    > yarn
  - 실행 전 환경 설정 방법 
   1. /conf/.api.json 파일을 열어 Listener의 실행포트, 실행모드를 설정한다. 
     port : Listener 실행포트(default : 55441 ) 
	 runningMod : 실행모드( 스토리지 노드용 : "STORAGE", 검색노드용 : "SEARCH" ) 
   2. /conf/.env.json 파일을 열어 로그를 설정한다. 
      log_level : 로그 설정( 디버그 : "debug", 오류 : "error" ) 
   3. /conf/.env.json 파일을 열어 이벤트를 받고자 하는 시작 블록번호를 지정한다. 
      first_data_block : 최초 이벤트를 얻고자 하는 시작 블록번호 
	  last_file_block :  현재까지 읽은 이벤트 블록번호 
	  last_revoke_block : 삭제 이벤트를 얻고자 하는 시작 블록번호 
   4. /conf/.env.json 파일을 열어 블록체인 정보를 설정한다. 
   5. /conf/.httpenv.json 파일을 열어 이벤트를 전송하고자 하는 rest api( 스토리지/검색 노드 )의 정보를 셋팅한다. 
      RegURL : rest api IP
      RegPort : rest api PORT 
      SubURL : rest api sub-url 
	
  - 실행방법 
    > node app.js   

## 배포방법 
  - 윈도우용 모듈 배포 
   > pkg --out-path dist --targets node10-win-x86 app.js 
   > cp [패키지안에 미포함 모듈 명] ./dist/
     '패키지안에 미포함 모듈 명'은 pkg 명령을 수행하면 미포함된 모듈의 경로와 이름을 출력된다. 
   > cp ./conf ./dist/ 
   > cd ./dist/
   > mv app listener.exe 
   > [폴더 전체를 압축] 
   
  - Linux용 모듈 배포 
   > pkg --out-path dist --targets node8-linux-x64 app.js
   > cp [패키지안에 미포함 모듈 명] ./dist/
     '패키지안에 미포함 모듈 명'은 pkg 명령을 수행하면 미포함된 모듈의 경로와 이름을 출력된다. 
   > cp -r ./conf ./dist/ 
   > cd ./dist/
   > mv app listener 
   > tar cvf listener_linux.tar listener *.node conf 
   > gzip listener_linux.tar 

## API 설명 
   listener api 설명서 참조 
