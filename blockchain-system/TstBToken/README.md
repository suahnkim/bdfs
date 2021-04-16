# 스마트컨트랙트 테스트 프로젝트 

## 실행방법 
  - 실행 전 모듈 설치 
    > yarn	
  - 스마트컨트랙트 파일 업데이트 
    > yarn update:t
	 * 수동으로 이동시 ../TruffLeBToken/contracts/ 와 ../TruffLeBIdentity/contracts 아래의 모든 파일을 ./contracts/로 이동한다. 
  - 컴파일 
    > yarn compile 
  - ganache 실행  
    > yarn ganache
  - 추가적인 창을 띠워 test 실행  
    > yarn test:t 
