pragma solidity ^0.4.24;

contract Bconfig {
  address public owner;

  string verifierUrl = "http://127.0.0.1";
  uint verifierPort = 55444;

  uint channelOpenPeriod = 60 ;
  uint receiptCollection = 10;

  modifier restricted() {
    if (msg.sender == owner)
      _;
  }

  constructor () public {
    owner = msg.sender;
  }

  function setBconfig(string inUrl, uint inPort, uint inPeriod, uint inCol) public restricted {
    verifierUrl = inUrl;
    verifierPort = inPort;
    channelOpenPeriod = inPeriod;
    receiptCollection = inCol;
  }
  function getBconfig() view public returns (string , uint , uint , uint, address, address ) {
    return(  verifierUrl, verifierPort, channelOpenPeriod, receiptCollection, owner, msg.sender );
  }
}
