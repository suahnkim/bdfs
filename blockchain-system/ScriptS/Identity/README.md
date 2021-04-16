# identity APIs

## 실행방법 
  - 실행 전 모듈 설치 
    > yarn
  - 실행방법 
    > node identitySvr.js

## 배포방법 
  - 윈도우용 모듈 배포 
   > pkg --out-path dist --targets node10-win-x86 identitySvr.js 
   > cp [패키지안에 미포함 모듈 명] ./dist/
     '패키지안에 미포함 모듈 명'은 pkg 명령을 수행하면 미포함된 모듈의 경로와 이름을 출력된다. 
   > cp ./conf ./dist/
   > cp ./ssl_cert ./dist 
   > cd ./dist/
   > mv app identitySvr.exe 
   > [폴더 전체를 압축]  
   
  - Linux용 모듈 배포 
   > pkg --out-path dist --targets node8-linux-x64 identitySvr.js
   > cp [패키지안에 미포함 모듈 명] ./dist/
     '패키지안에 미포함 모듈 명'은 pkg 명령을 수행하면 미포함된 모듈의 경로와 이름을 출력된다. 
   > cp -r ./conf ./dist/
   > cp -r./ssl_cert ./dist 
   > cd ./dist/
   > mv app identitySvr 
   > tar cvf identitySvr_linux.tar identitySvr *.node  
   > gzip identitySvr_linux.tar 

