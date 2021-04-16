
pragma solidity ^0.4.24;

contract StrUtil {
    
  // function splitByLengths(string _src) pure internal returns (bytes[]) {
  //   bytes memory src = bytes(_src);
  //   require(src.length % 46 == 0);
  //   uint num = src.length / 46;
  //   bytes[] memory split = new bytes[](num);
  //   uint start = 0;
  //   for(uint i = 0; i < num; i++) {
  //     bytes memory tmp = new bytes(46);
  //     for(uint j = 0; j < 46; j++) {
  //       tmp[j] = src[start + j];
  //     }
  //     split[i] = tmp;
  //     start += 46;
  //   }
  //   return split;
  // }

  function splitByLengths2(string _src, uint len) pure internal returns (bytes[]) {
    bytes memory src = bytes(_src);
    require(src.length % len == 0);
    uint num = src.length / len;
    bytes[] memory split = new bytes[](num);
    uint start = 0;
    for(uint i = 0; i < num; i++) {
      bytes memory tmp = new bytes(len);
      for(uint j = 0; j < len; j++) {
        tmp[j] = src[start + j];
      }
      split[i] = tmp;
      start += len;
    }
    return split;
  }

}