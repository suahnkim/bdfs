// +build !linux

package mb

import (
	"bytes"
	"crypto/rand"
	"crypto/sha256"
	"crypto/tls"
	"database/sql"
	"encoding/base64"
	"encoding/hex"
	"encoding/json"
	"errors"
	"fmt"
	logging "gx/ipfs/QmbkT7eMTyXfpeyB3ZMxxcxg7XH8t6uXp49jqzz4HB7BGF/go-log"
	"io/ioutil"
	"net/http"
	"net/url"
	"os"
	"sort"
	"strconv"
	"strings"
	"sync"
	"time"

	cid "gx/ipfs/QmTbxNB1NwDesLmKTscr4udL2tVP7MaxvXnD1D9yX7g3PN/go-cid"
	ipld "gx/ipfs/QmZ6nzCLwGLVfRzYLpD7pW6UNuBDKEcA2imJtVpbEx2rxy/go-ipld-format"

	"github.com/kevinburke/nacl/sign"
	_ "github.com/mattn/go-sqlite3"
)

var gChID string

//	[201102-ToM] 전역 변수 추가
var gBasePath string
var gLog = logging.Logger("mb")
var gMutex = new(sync.Mutex)
var gMapMutex = new(sync.Mutex)

//	[201102-ToM] 전역 객체 추가
var gListMutex = new(sync.Mutex)

// Lock ???
func Lock() {
	gMutex.Lock()

}

// Unlock ???
func Unlock() {

	gMutex.Unlock()
}

// ChannelID 현재 개설된 채널ID를 반환한다.
func ChannelID() string {
	return gChID
}

// Clear ???
func Clear() {
	for k, v := range gOffchain.channels {
		v.Clear()
		delete(gOffchain.channels, k)
	}
	/*for k, v := range gOffchain.params {
		v.Clear()
		delete(gOffchain.params, k)
	}*/
}

// RespBase 기본 응답 구조체
type RespBase struct {
	Type string `json:"type"`
	Data string `json:"data"`
}

// Postfix 블록 응답 구조체
type Postfix struct {
	Cid   cid.Cid `json:"cid"`
	ChID  string  `json:"channelId"`
	AccID string  `json:"accountID"`
}

// MBConfig 미디어블록체인 환경 정보 구조체
type MBConfig struct {
	// 온체인 아이디
	AccID string `json:"AccountID"`
	// 온체인 아이디 비밀키 비밀번호
	Password string
	// 온체인 API 통신 IP 주소
	OnchainIP string
	// 온체인 API 통신 포트
	OnchainPort int
}

// gConfig 미디어블록체인 환경 정보 포인터
var gConfig *MBConfig

// Verify 미디어블록체인 환경 정보 유효성을 검사한다.
// [반환] error	에러 발생 시 내용
func (cfg MBConfig) Verify() error {
	if "" == cfg.AccID {
		return errors.New("온체인 아이디가 비어있습니다")
	} else if "" == cfg.Password {
		return errors.New("온체인 아이디 비밀키 비밀번호가 비어있습니다")
	} else if 0 >= cfg.OnchainPort {
		return errors.New("온체인 API 통신 포트가 유효하지 않습니다")
	} else if "" == cfg.OnchainIP {
		cfg.OnchainIP = "127.0.0.1"
	}
	return nil
}

// Receipt 전송 영수증 구조체
type Receipt struct {
	// 전송자 온체인 아이디
	From string
	// 수신자 온체인 아이디
	To string
	// 콘텐츠 내 파일 아이디
	File string
	// 파일 내 다운로드 된 블록 인덱스
	Chunks string
	// 블록 인덱스 합산 값
	Total int
	// 동기화 객체
	Sync *sync.Mutex `json:"-"`
	// 파일 기준 수신 내역 전자서명 값
	Sign string `json:"-"`
	// 전송 영수증 게시 재시도 횟수
	Retry int `json:"-"`
	// 전송 영수증 부분합 횟수
	SubTotal int `json:"-"`
	// 전송 영수증 생성 일시
	Creation time.Time `json:"-"`
	// 전송 영수증 마지막 갱신 일시
	LastUpdate time.Time `json:"-"`
}

type mReceiptByFile map[string]*Receipt
type mReceiptByAccount map[string]*mReceiptByFile

// Channel 오프체인 채널 정보 구조체
type Channel struct {
	// 채널 아이디
	ID string
	// 전송 영수증 공개키
	PublicKey sign.PublicKey
	// 전송 영수증 개인키
	PrivateKey sign.PrivateKey
	// 전송 영수증 부분합 조건
	SubTotal int
	// 최상위 링크 객체 포인터
	Link *ipld.Link
	// [191113-ToM] 발행된 전송 영수증 목록(중복 발행 방지)
	Issued map[cid.Cid]string
	// 파일 기준 링크 객체 목록
	Blocks map[cid.Cid]*ipld.Link
	// 온체인ID 별 전송 영수증 목록
	Receipts mReceiptByAccount
	// [200617-ToM] 온체인채널이력 번호
	No int64
}

// findChildLink ???
// [반환] *ipld.Link ???
// [반환] int ???
// [인자] pLink	???
// [인자] c	???
func findChildLink(pLink *ipld.Link, c cid.Cid) (*ipld.Link, int) {
	for iIndex, pChild := range pLink.Childs {
		if nil != pChild {
			if pChild.Cid == c {
				return pChild, iIndex
			}
			pFound, iIndex := findChildLink(pChild, c)
			if nil != pFound {
				return pFound, iIndex
			}
		}
	}
	return nil, -1
}

// Clear ???
func (ch *Channel) Clear() {
	for k := range ch.Blocks {
		delete(ch.Blocks, k)
	}
	for k, v := range ch.Receipts {
		for k2 := range *v {
			delete(*v, k2)
		}
		delete(ch.Receipts, k)
	}
}

// GetReceipt ???
// [반환] *Receipt 전송 영수증 객체 포인터
// [반환] bool 신규 영수증 유무
// [인자] partner 상대 온체인 아이디
// [인자] file 파일 블록 아이디
func (ch *Channel) GetReceipt(partner, file string) (*Receipt, bool) {
	var New bool
	var receipt *Receipt

	gMapMutex.Lock()
	//	온체인 아이디로 전송 영수증 목록을 얻는다.
	pmReceipt, exists := ch.Receipts[partner]
	if false == exists {
		mReceipt := make(mReceiptByFile)
		pmReceipt = &mReceipt
		ch.Receipts[partner] = pmReceipt
	} else {
		//	파일 아이디로 전송 영수증을 얻는다.
		receipt, exists = (*pmReceipt)[file]
	}
	if false == exists {
		now := time.Now()
		receipt = &Receipt{File: file, SubTotal: 0, Creation: now, LastUpdate: now, Sync: new(sync.Mutex)}
		(*pmReceipt)[file] = receipt
		New = true
	}
	gMapMutex.Unlock()
	return receipt, New
}

// ReceiveReceipt 전송 영수증 수신을 처리한다.
// [반환] error 에러 발생 시 설명
// [인자] chID	오프체인 채널 아이디
// [인자] resp 수신 데이터
func ReceiveReceipt(chID string, resp *RespBase) error {
	var receipt Receipt

	data, err := base64.StdEncoding.DecodeString(resp.Data)
	if nil != err {
		err = fmt.Errorf("전송 영수증 Base64 디코딩 실패: %s", err)
	} else if channel := GetChannelFromChID(chID); nil == channel {
		err = fmt.Errorf("알 수 없는 채널: %s", chID)
	} else if false == sign.Verify(data, channel.PublicKey) {
		err = errors.New("전송 영수증 서명 검증 실패")
	} else if err = json.Unmarshal(data[sign.SignatureSize:], &receipt); nil != err {
		err = fmt.Errorf("전송 영수증 JSON 디코딩 실패: %s", err)
	} else {
		var stmt *sql.Stmt
		var chNo, blkNo int64
		var result sql.Result

		if err = gOffchain.db.QueryRow("SELECT no FROM tb_channel WHERE channel_id=?", chID).Scan(&chNo); nil != err {
			err = fmt.Errorf("채널 조회 실패: %s (%s)", err, chID)
		} else if err = gOffchain.db.QueryRow("SELECT no FROM tb_blocks WHERE cid=? LIMIT 1", chID).Scan(&blkNo); nil != err {
			if sql.ErrNoRows != err {
				err = fmt.Errorf("블록 조회 실패: %s (%s)", err, chID)
			} else if stmt, err = gOffchain.db.Prepare("INSERT INTO tb_blocks(cid,creation) VALUES(?,?)"); nil != err {
				err = fmt.Errorf("블록 추가 실패: %s", err)
			} else {
				if result, err = stmt.Exec(chID, time.Now().String()); nil != err {
					err = fmt.Errorf("블록 추가 실패(2): %s", err)
				} else if blkNo, err = result.LastInsertId(); nil != err {
					err = fmt.Errorf("블록 추가 실패(3): %s", err)
				}
				stmt.Close()
			}
		} else if stmt, err = gOffchain.db.Prepare("INSERT INTO tb_receipts(channel_no,block_no,flag,receipt,date,time) VALUES(?,?,?,?,?,?)"); nil != err {
			err = fmt.Errorf("전송 영수증 수신 이력 추가 실패: %s", err)
		} else {
			jReceipt, _ := json.Marshal(receipt)
			now := time.Now()
			if _, err = stmt.Exec(chNo, blkNo, 2 /*수신*/, string(jReceipt), fmt.Sprintf("%04d-%02d-%02d", now.Year(), now.Month(), now.Day()), fmt.Sprintf("%02d:%02d:%02d", now.Hour(), now.Minute(), now.Second())); nil != err {
				err = fmt.Errorf("전송 영수증 수신 이력 추가 실패(2): %s", err)
			}
			stmt.Close()
		}
		if nil != err {
			fmt.Printf("● %s\n", err)
		}

		//	수신자 온체인 및 파일 아이디로 마지막 전송 영수증을 얻는다.
		last, new := channel.GetReceipt(receipt.To, receipt.File)
		//	[190919-ToM] 전송 영수증 작업 시 영수증 별로 직렬화한다.
		last.Sync.Lock()
		//	[190919-ToM] 마지막 게시한 전송 영수증보다 이전이면 게시될 수 없다.
		if true == new || receipt.Total > last.Total {
			last.Chunks = receipt.Chunks
			last.Total = receipt.Total
			last.Sign = resp.Data
			last.LastUpdate = time.Now()
			last.SubTotal++
		} else if receipt.Total < last.Total {
			last.SubTotal++
		}
		//	전송 영수증 부분합 조건과 같거나 크면 온체인에 게시한다.
		if last.SubTotal >= channel.SubTotal {
			if err = PublishReceipt(channel.ID, last.Sign); nil == err {
				last.SubTotal = 0
				//	fmt.Println("● 전송 영수증 게시 성공")
			} else {
				fmt.Println("● 전송 영수증 게시 실패:", err)
			}
		}
		last.Sync.Unlock()
	}
	return err
}

func (ch *Channel) SendBlock(key cid.Cid, accID string) error {
	var stmt *sql.Stmt
	var result sql.Result
	var accNo, blkNo int64

	//	[200617-ToM] 온체인 계정을 추가한다.
	err := gOffchain.db.QueryRow("SELECT no FROM tb_account WHERE onchain_id=? LIMIT 1", accID).Scan(&accNo)
	if nil != err {
		if sql.ErrNoRows == err {
			err = nil
		} else {
			err = fmt.Errorf("계정 조회 실패: %s", err)
		}
	}
	if nil == err && 0 /*계정없음*/ == accNo {
		if stmt, err = gOffchain.db.Prepare("INSERT INTO tb_account(onchain_id,creation) VALUES(?,?)"); nil != err {
			err = fmt.Errorf("계정 추가 실패: %s", err)
		} else {
			if result, err = stmt.Exec(accID, time.Now().String()); nil != err {
				err = fmt.Errorf("계정 추가 실패(2): %s", err)
			} else if accNo, err = result.LastInsertId(); nil != err {
				err = fmt.Errorf("계정 추가 실패(3): %s", err)
			}
			stmt.Close()
		}
	}
	if nil != err {
		goto DB_FAIL
	}
	//	[200617-ToM] 블록을 추가한다.
	if err = gOffchain.db.QueryRow("SELECT no FROM tb_blocks WHERE cid=? LIMIT 1", key.String()).Scan(&blkNo); nil != err {
		if sql.ErrNoRows == err {
			err = nil
		} else {
			err = fmt.Errorf("블록 조회 실패: %s", err)
		}
	}
	if nil == err && 0 == blkNo {
		if stmt, err = gOffchain.db.Prepare("INSERT INTO tb_blocks(cid,creation) VALUES(?,?)"); nil != err {
			err = fmt.Errorf("블록 추가 실패: %s", err)
		} else {
			if result, err = stmt.Exec(key.String(), time.Now().String()); nil != err {
				err = fmt.Errorf("블록 추가 실패(2): %s", err)
			} else if blkNo, err = result.LastInsertId(); nil != err {
				err = fmt.Errorf("블록 추가 실패(3): %s", err)
			}
			stmt.Close()
		}
	}
	if nil != err {
		goto DB_FAIL
	}
	//	[200928-ToM] 데이터 송신 이력을 추가한다.
	if stmt, err = gOffchain.db.Prepare("INSERT INTO tb_share_data(connect_no,account_no,channel_no,block_no,date,time,direction,overlap,receipt) VALUES(?,?,?,?,?,?,?,?,?)"); nil != err {
		err = fmt.Errorf("데이터 송신 이력 추가 실패: %s", err)
	} else {
		now := time.Now()
		if _, err = stmt.Exec(gOffchain.connect, accNo, ch.No, blkNo, fmt.Sprintf("%04d-%02d-%02d", now.Year(), now.Month(), now.Day()), fmt.Sprintf("%02d:%02d:%02d", now.Hour(), now.Minute(), now.Second()), 1 /*송신*/, 0 /*중복아님*/, 0); nil != err {
			err = fmt.Errorf("데이터 송신 이력 추가 실패(2): %s", err)
		}
		stmt.Close()
	}

DB_FAIL:
	if nil != err {
		fmt.Printf("● %s\n", err)
	}
	return err
}

// ReceiveBlock 데이터 블록 수신을 처리한다.
// [반환] string 수신 블록의 발행할 전송 영수증
// [인자] key 수신 블록 아이디
// [인자] accID	온체인 아이디
func (ch *Channel) ReceiveBlock(key cid.Cid, accID string) string {
	var chunks []int
	var stmt *sql.Stmt
	var found *ipld.Link
	var result sql.Result
	var accNo, blkNo int64
	var chunk, begin, end int

	//	[200617-ToM] 온체인 계정을 추가한다.
	err := gOffchain.db.QueryRow("SELECT no FROM tb_account WHERE onchain_id=? LIMIT 1", accID).Scan(&accNo)
	if nil != err {
		if sql.ErrNoRows == err {
			err = nil
		} else {
			err = fmt.Errorf("계정 조회 실패: %s", err)
		}
	}
	if nil == err && 0 /*계정없음*/ == accNo {
		if stmt, err = gOffchain.db.Prepare("INSERT INTO tb_account(onchain_id,creation) VALUES(?,?)"); nil != err {
			err = fmt.Errorf("계정 추가 실패: %s", err)
		} else {
			if result, err = stmt.Exec(accID, time.Now().String()); nil != err {
				err = fmt.Errorf("계정 추가 실패(2): %s", err)
			} else if accNo, err = result.LastInsertId(); nil != err {
				err = fmt.Errorf("계정 추가 실패(3): %s", err)
			}
			stmt.Close()
		}
	}
	if nil != err {
		goto DB_FAIL
	}
	//	[200617-ToM] 블록을 추가한다.
	if err = gOffchain.db.QueryRow("SELECT no FROM tb_blocks WHERE cid=? LIMIT 1", key.String()).Scan(&blkNo); nil != err {
		if sql.ErrNoRows == err {
			err = nil
		} else {
			err = fmt.Errorf("블록 조회 실패: %s", err)
		}
	}
	if nil == err && 0 == blkNo {
		if stmt, err = gOffchain.db.Prepare("INSERT INTO tb_blocks(cid,creation) VALUES(?,?)"); nil != err {
			err = fmt.Errorf("블록 추가 실패: %s", err)
		} else {
			if result, err = stmt.Exec(key.String(), time.Now().String()); nil != err {
				err = fmt.Errorf("블록 추가 실패(2): %s", err)
			} else if blkNo, err = result.LastInsertId(); nil != err {
				err = fmt.Errorf("블록 추가 실패(3): %s", err)
			}
			stmt.Close()
		}
	}
	if nil != err {
		goto DB_FAIL
	}
	//	[200617-ToM] 데이터 수신 이력을 추가한다.
	if stmt, err = gOffchain.db.Prepare("INSERT INTO tb_share_data(connect_no,account_no,channel_no,block_no,date,time,direction,overlap,receipt) VALUES(?,?,?,?,?,?,?,?,?)"); nil != err {
		err = fmt.Errorf("데이터 수신 이력 추가 실패: %s", err)
	} else {
		now := time.Now()
		if _, err = stmt.Exec(gOffchain.connect, accNo, ch.No, blkNo, fmt.Sprintf("%04d-%02d-%02d", now.Year(), now.Month(), now.Day()), fmt.Sprintf("%02d:%02d:%02d", now.Hour(), now.Minute(), now.Second()), 2 /*수신*/, 0 /*중복아님*/, 1 /*영수증발행*/); nil != err {
			err = fmt.Errorf("데이터 수신 이력 추가 실패(2): %s", err)
		}
		stmt.Close()
	}

DB_FAIL:
	if nil != err {
		fmt.Printf("● %s\n", err)
	}
	if _, exists := ch.Issued[key]; exists {
		return ""
	}

FIND_BLOCK: //	1. 블록 아이디로 파일 및 인덱스를 검색한다.
	for _, block := range ch.Blocks {
		if block.Cid == key {
			found = block
			chunk = 1
			break
		}
		for index, child := range block.Childs {
			if child.Cid == key {
				found = block
				chunk = 2 + index
				//	2중 루프를 빠져나간다.
				break FIND_BLOCK
			}
		}
	}
	if nil == found {
		panic("● 전송 영수증 발행 실패: 찾을 수 없는 블록")
	}

	//	2. 전송자 온체인 및 파일 아이디로 전송 영수증을 검색한다.
	receipt, New := ch.GetReceipt(accID, found.Cid.String())
	if true == New {
		receipt.From = accID
		receipt.To = Config().AccID
	}

	//	3. 인코딩 된 블록 인덱스 목록을 디코딩한다.
	chunks = append(chunks, chunk)
	if "" != receipt.Chunks {
		sChunks := strings.Split(receipt.Chunks, ",")
		for _, sChunk := range sChunks {
			if index := strings.Index(sChunk, "-"); -1 != index {
				begin, _ = strconv.Atoi(sChunk[:index])
				end, _ = strconv.Atoi(sChunk[1+index:])
				for ; begin <= end; begin++ {
					chunks = append(chunks, begin)
				}
			} else {
				chunk, _ = strconv.Atoi(sChunk)
				chunks = append(chunks, chunk)
			}
		}
	}
	//	블록 인덱스를 오름차순 정렬한다.
	sort.Sort(sort.IntSlice(chunks))

	//	4. 다운로드 된 블록의 인덱스 목록을 인코딩한다.
	begin = 0
	end = 0
	receipt.Chunks = ""
	for index := 0; len(chunks) > index; index++ {
		if 0 == begin {
			begin = chunks[index]
			end = begin
		} else if 1+end == chunks[index] {
			end = chunks[index]
		} else {
			if receipt.Chunks += strconv.Itoa(begin); 1+begin < end {
				receipt.Chunks += "-"
			} else {
				receipt.Chunks += ","
			}
			if begin < end {
				receipt.Chunks += strconv.Itoa(end) + ","
			}
			begin = 0
			end = 0
			index--
		}
	}
	if 0 < begin {
		receipt.Chunks += strconv.Itoa(begin)
		if begin < end {
			if 1+begin < end {
				receipt.Chunks += "-"
			} else {
				receipt.Chunks += ","
			}
			receipt.Chunks += strconv.Itoa(end)
		}
	}
	receipt.Chunks = strings.TrimRight(receipt.Chunks, ",")
	//	[190919-ToM] 최신 전송 영수증을 구분하기 위해 모든 블록 인덱스를 합산한다.
	receipt.Total = 0
	for _, chunk := range chunks {
		receipt.Total += chunk
	}
	receipt.LastUpdate = time.Now()

	//	4. 전송 영수증 작성하고 서명한다.
	jReceipt, _ := json.Marshal(receipt)
	b64Receipt := base64.StdEncoding.EncodeToString(sign.Sign(jReceipt, ch.PrivateKey))

	//	[191113-ToM] 전송 영수증 발행 정보를 추가한다.
	ch.Issued[key] = accID

	//	[200617-ToM] 전송 영수증 발행 이력을 추가한다.
	if 0 < blkNo {
		if stmt, err = gOffchain.db.Prepare("INSERT INTO tb_receipts(channel_no,block_no,flag,receipt,date,time) VALUES(?,?,?,?,?,?)"); nil != err {
			err = fmt.Errorf("전송 영수증 발행 이력 추가 실패: %s", err)
		} else {
			now := time.Now()
			if _, err = stmt.Exec(ch.No, blkNo, 1 /*발행*/, jReceipt, fmt.Sprintf("%04d-%02d-%02d", now.Year(), now.Month(), now.Day()), fmt.Sprintf("%02d:%02d:%02d", now.Hour(), now.Minute(), now.Second())); nil != err {
				err = fmt.Errorf("전송 영수증 발행 이력 추가 실패(2): %s", err)
			}
			stmt.Close()
		}
	}

	return b64Receipt
}

// offchain 오프체인 정보 구조체
type offchain struct {
	db       *sql.DB
	account  int64
	connect  int64
	channels map[string]*Channel
}

// 오프체인 전역 객체
var gOffchain offchain

// FileExists 파일 유무를 반환한다.
func FileExists(path string) bool {
	if _, err := os.Stat(path); nil != err {
		if os.IsNotExist(err) {
			return false
		}
	}
	return true
}

// InitChannel 오프체인 채널을 초기화한다.
func InitChannel(path string) error {
	var err error

	gOffchain.channels = make(map[string]*Channel)

	// [201102-ToM] 기본 디렉터리 경로를 설정한다.
	gBasePath = path
	// [200622-ToM] 데이터베이스 파일이 없으면 생성한다.
	path += `\ipfs_mb.db`
	if false == FileExists(path) {
		if gOffchain.db, err = sql.Open("sqlite3", path); nil != err {
		} else if _, err = gOffchain.db.Exec(`CREATE TABLE "tb_account" (
			"no" INTEGER PRIMARY KEY AUTOINCREMENT,
			"onchain_id" TEXT NOT NULL,
			"creation" TEXT NOT NULL)`); nil != err {
		} else if _, err = gOffchain.db.Exec(`CREATE TABLE "tb_blocks" (
			"no" INTEGER PRIMARY KEY AUTOINCREMENT,
			"cid" TEXT NOT NULL,
			"creation" TEXT NOT NULL)`); nil != err {
		} else if _, err = gOffchain.db.Exec(`CREATE TABLE "tb_channel" (
			"no"	INTEGER PRIMARY KEY AUTOINCREMENT,
			"account_no"	INTEGER NOT NULL,
			"block_no"	INTEGER DEFAULT -1,
			"state"	INTEGER NOT NULL,
			"desc"	TEXT,
			"channel_id"	TEXT NOT NULL,
			"date"	TEXT NOT NULL,
			"time"	TEXT NOT NULL,
			"flag"	INTEGER NOT NULL,
			"elapsed"	INTEGER NOT NULL,
			"purchase_id"	TEXT,
			FOREIGN KEY("account_no") REFERENCES "tb_account"("no"))`); nil != err {
		} else if _, err = gOffchain.db.Exec(`CREATE TABLE "tb_connect" (
			"no"	INTEGER PRIMARY KEY AUTOINCREMENT,
			"account_no"	INTEGER NOT NULL,
			"state"	NUMERIC NOT NULL,
			"desc"	TEXT,
			"date"	TEXT NOT NULL,
			"time"	TEXT NOT NULL,
			"password"	TEXT NOT NULL,
			FOREIGN KEY("account_no") REFERENCES "tb_account"("no"))`); nil != err {
		} else if _, err = gOffchain.db.Exec(`CREATE TABLE "tb_receipts" (
			"no"	INTEGER PRIMARY KEY AUTOINCREMENT,
			"channel_no"	INTEGER NOT NULL,
			"block_no"	INTEGER DEFAULT -1,
			"flag"	INTEGER NOT NULL,
			"receipt"	TEXT NOT NULL,
			"date"	TEXT NOT NULL,
			"time"	TEXT NOT NULL,
			FOREIGN KEY("channel_no") REFERENCES "tb_channel"("no"),
			FOREIGN KEY("block_no") REFERENCES "tb_blocks"("no"))`); nil != err {
		} else if _, err = gOffchain.db.Exec(`CREATE TABLE "tb_share_data" (
			"no"	INTEGER PRIMARY KEY AUTOINCREMENT,
			"connect_no"	INTEGER NOT NULL,
			"account_no"	INTEGER NOT NULL,
			"channel_no"	INTEGER NOT NULL,
			"block_no"	INTEGER NOT NULL,
			"date"	TEXT NOT NULL,
			"time"	TEXT NOT NULL,
			"direction"	INTEGER NOT NULL,
			"overlap"	INTEGER NOT NULL,
			"receipt"	INTEGER NOT NULL DEFAULT 0,
			FOREIGN KEY("connect_no") REFERENCES "tb_connect"("no"),
			FOREIGN KEY("block_no") REFERENCES "tb_blocks"("no"),
			FOREIGN KEY("account_no") REFERENCES "tb_account"("no"),
			FOREIGN KEY("channel_no") REFERENCES "tb_channel"("no"))`); nil != err {
		} else if _, err = gOffchain.db.Exec(`CREATE TABLE "tb_config" (
			"version" TEXT NOT NULL,
			"creation" TEXT NOT NULL)`); nil != err {
		} else if _, err = gOffchain.db.Exec(`CREATE TABLE "tb_session" (
			"no"	INTEGER,
			"session"	TEXT NOT NULL,
			"creation"	TEXT NOT NULL,
			PRIMARY KEY("no"))`); nil != err {
		} else if _, err = gOffchain.db.Exec(`CREATE INDEX "account_idx1" ON "tb_account" (
			"onchain_id")`); nil != err {
		}
		if nil == err {
			var stmt *sql.Stmt
			if stmt, err = gOffchain.db.Prepare("INSERT INTO tb_config VALUES(?,?)"); nil == err {
				_, err = stmt.Exec("0.1", time.Now())
			}
		}
		if nil != err {
			err = fmt.Errorf("DB 생성 실패: %s", err)
			//	오픈된 DB 객체를 닫는다.
			if nil != gOffchain.db {
				gOffchain.db.Close()
				gOffchain.db = nil
			}
			//	생성이 실패한 DB 파일을 삭제한다.
			os.Remove(path)
		}
	} else if gOffchain.db, err = sql.Open("sqlite3", path); nil != err {
		err = fmt.Errorf("DB 오픈 실패: %s", err)
	} else {
		var version string
		if err = gOffchain.db.QueryRow("SELECT version FROM tb_config").Scan(&version); nil != err {
			err = fmt.Errorf("DB 버전 조회 실패: %s", err)
		} else if "0.1" != version {
			err = fmt.Errorf("미 지원 DB 버전: %s", version)
		}
	}
	if nil != err {
		//	주기적으로 미 게시된 전송 영수증을 온체인에 게시한다.
		go func() {
			for {
				for _, ch := range gOffchain.channels {
					for _, receipts := range ch.Receipts {
						for _, receipt := range *receipts {
							if 0 < receipt.SubTotal && time.Now().After(receipt.LastUpdate.Add(time.Second*time.Duration(60))) {
								if err := PublishReceipt(ch.ID, receipt.Sign); nil != err {
									fmt.Println("● 전송 영수증 만료 게시 실패:", err)
									//	전송 영수증 게시는 최대 3회 재시도한다.
									if receipt.Retry++; 3 == receipt.Retry {
										receipt.SubTotal = 0
									}
								} else {
									receipt.SubTotal = 0
									//	fmt.Println("● 전송 영수증 만료 게시")
								}
							}
						}
					}
				}
				time.Sleep(time.Second)
			}
		}()
	}
	return err
}

// newChannel ???
// [반환] *Channel 오프체인 채널 정보 포인터
// [인자] chID	오프체인 채널 아이디
// [인자] publicKey	전송 영수증 공개키
// [인자] privateKey 전송 영수증 개인키
// [인자] link 루트 링크 객체 포인터
// [인자] files	파일 기준 링크 객체 목록
func newChannel(chID string, publicKey sign.PublicKey, privateKey sign.PrivateKey, link *ipld.Link, blocks map[cid.Cid]*ipld.Link) *Channel {

	channel := Channel{ID: chID, Link: link, Blocks: blocks, PublicKey: publicKey, PrivateKey: privateKey, Issued: make(map[cid.Cid]string), Receipts: make(mReceiptByAccount)}
	gOffchain.channels[chID] = &channel
	return &channel
}

// GetChannelFromChID 오프체인 채널 정보 포인터를 검색한다.
// [반환] *Channel 오프체인 채널 정보 포인터
// [인자] chID	오프체인 채널 아이디
func GetChannelFromChID(chID string) *Channel {

	if channel, exists := gOffchain.channels[chID]; exists {
		return channel
	}
	return nil
}

// GetChannelFromCid 오프체인 채널 정보 포인터를 검색한다.
// [반환] *Channel 오프체인 채널 정보 포인터
// [인자] c	블록 아이디
func GetChannelFromCid(c cid.Cid) *Channel {

	for _, ch := range gOffchain.channels {
		//	[190918-ToM] null 값이 될 수 있다.
		if nil != ch.Link {
			if ch.Link.Cid == c {
				return ch
			}
			if link, _ := findChildLink(ch.Link, c); nil != link {
				return ch
			}
		}
	}
	return nil
}

// existLink ???
// [반환] bool	검색 유무
// [인자] c	검색할 블록 아이디
// [인자] parent	상위 링크 객체 포인터
func existLink(c cid.Cid, parent *ipld.Link) bool {

	if parent.Cid == c {
		return true
	}
	for _, link := range parent.Childs {
		if true == existLink(c, link) {
			return true
		}
	}
	return false
}

// LoginResult 로그인 결과 구조체
type LoginResult struct {
	// 결과 코드
	ResultCode int `protobuf:"varint,1,opt,name=resultCode,proto3" json:"resultCode,omitempty"`
	// 결과 코드 별 설명
	ResultMessage string `protobuf:"varint,2,opt,name=state,proto3" json:"state,omitempty"`
}

// OpenResult 오프체인 채널 오픈 결과 구조체
type OpenResult struct {
	// 결과 코드
	ResultCode int `protobuf:"varint,1,opt,name=resultCode,proto3" json:"resultCode,omitempty,string"`
	// 결과 코드 별 설명
	ResultMessage string `protobuf:"varint,2,opt,name=state,proto3" json:"state,omitempty"`
	// 개설된 채널 아이디
	ChannelID string `protobuf:"varint,3,opt,name=channelId,proto3" json:"channelId,omitempty"`
	// 채널 오픈 주기(?)
	ChannelOpenPeriod string `protobuf:"varint,4,opt,name=channelOpenPeriod,proto3" json:"channelOpenPeriod,omitempty"`
}

// VerifyResult 오프체인 채널 검증 결과 구조체
type VerifyResult struct {
	// 결과 코드
	ResultCode int `protobuf:"varint,1,opt,name=resultCode,proto3" json:"resultCode,omitempty,string"`
	// 결과 코드 별 메시지
	ResultMessage string `protobuf:"varint,2,opt,name=resultMessage,proto3" json:"resultMessage,omitempty"`
	// 전송 영수증 검증키
	VerifyKey string `protobuf:"varint,3,opt,name=publicKey,proto3" json:"publicKey,omitempty"`
	// 전송 영수증 부분합 조건
	ReceiptCollection int `protobuf:"varint,4,opt,name=receiptCollection,proto3" json:"receiptCollection,omitempty,string"`
	// 채널 상태
	validity string `protobuf:"varint,5,opt,name=validity,proto3" json:"validity,omitempty"`
}

// ReadFile ???
// [반환] []byte ???
// [반환] error	에러 발생 시 내용
// [인자] filename 파일 경로
func ReadFile(filename string) ([]byte, error) {
	return ioutil.ReadFile(filename)
}

// HttpGet HTTP I/F를 Get 메소드로 호출한다.
// [반환] string 응답 데이터
// [반환] error	에러 발생 시 내용
// [인자] URL 호출 주소
func httpGet(URL string) (string, error) {
	tr := &http.Transport{
		TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
	}
	client := &http.Client{Transport: tr}
	response, err := client.Get(URL)
	if nil != err {
		return "", err
	}
	defer response.Body.Close()

	content, _ := ioutil.ReadAll(response.Body)
	return strings.TrimSpace(string(content)), nil
}

// HttpPost HTTP I/F를 Post 메소드로 호출한다.
// [반환] string 응답 데이터
// [반환] error	에러 발생 시 내용
// [인자] URL 호출 주소
// [인자] data 전송 데이터
func httpPost(URL, data string) (string, error) {
	tr := &http.Transport{
		TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
	}
	client := &http.Client{Transport: tr}
	resp, err := client.Post(URL, "application/x-www-form-urlencoded", bytes.NewBufferString(data))
	if nil != err {
		fmt.Printf("  => 실패: '%s'\n", err)
		return "", err
	}

	content, _ := ioutil.ReadAll(resp.Body)
	resp.Body.Close()
	fmt.Printf("  => '%s'\n", string(content))
	return strings.TrimSpace(string(content)), nil
}

// SetConfig 환경 정보를 설정한다.
// [인자] cfg 환경 정보 포인터
func SetConfig(cfg *MBConfig) {

	gConfig = cfg
}

// Config 환경 정보를 반환한다.
// [반환] *MBConfig 환경 정보 포인터
func Config() *MBConfig {

	return gConfig
}

// Login 온체인에 로그인한다.
// [반환] error	에러 발생 시 내용
func Login() error {

	var state int
	var err [2]error
	var stmt *sql.Stmt
	var result sql.Result
	var data, desc string
	var resp map[string]interface{}

	params := url.Values{}
	params.Add("accountId", gConfig.AccID)
	params.Add("password", gConfig.Password)

	//	온체인에 로그인한다.
	//	[201112-ToM] 에러 처리 로직 강화
	if data, err[0] = httpPost(fmt.Sprintf("http://%s:%d/account/login", gConfig.OnchainIP, gConfig.OnchainPort), params.Encode()); nil != err[0] {
		state = -1
	} else if err[0] = json.Unmarshal([]byte(data), &resp); nil != err[0] {
		state = -2
	} else if resultCode, exists := resp["resultCode"].(float64); false == exists {
		state = -3
		err[0] = errors.New("항목(resultCode) 없음")
	} else if 0 /*성공*/ != resultCode {
		state = -4
		resultMsg, _ := resp["resultMessage"].(string)
		err[0] = fmt.Errorf("[%.0f] %s", resultCode, resultMsg)
	}
	if nil != err[0] {
		err[0] = fmt.Errorf("온체인 로그인 실패(%d): %s", state, err[0])
	}

	//	[200616-ToM] 온체인 계정을 추가한다.
	if err[1] = gOffchain.db.QueryRow("SELECT no FROM tb_account WHERE onchain_id=? LIMIT 1", gConfig.AccID).Scan(&gOffchain.account); nil != err[1] {
		if sql.ErrNoRows == err[1] {
			err[1] = nil
		} else {
			err[1] = fmt.Errorf("계정 조회 실패: %s", err[1])
		}
	}
	if nil == err[1] && 0 == gOffchain.account {
		if stmt, err[1] = gOffchain.db.Prepare("INSERT INTO tb_account(onchain_id,creation) VALUES(?,?)"); nil != err[1] {
			err[1] = fmt.Errorf("계정 추가 실패: %s", err[1])
		} else {
			if result, err[1] = stmt.Exec(gConfig.AccID, time.Now().String()); nil != err[1] {
				err[1] = fmt.Errorf("계정 추가 실패(2): %s", err[1])
			} else if gOffchain.account, err[1] = result.LastInsertId(); nil != err[1] {
				err[1] = fmt.Errorf("계정 추가 실패(3): %s", err[1])
			}
			stmt.Close()
		}
	}
	if nil == err[1] {
		//	[200616-ToM] 접속 이력을 추가한다.
		if stmt, err[1] = gOffchain.db.Prepare("INSERT INTO tb_connect(account_no,state,desc,date,time,password) VALUES(?,?,?,?,?,?)"); nil != err[1] {
			err[1] = fmt.Errorf("접속 이력 추가 실패: %s", err[1])
		} else {
			state = 0 /*성공*/
			if nil != err[0] {
				state = -1 /*실패*/
				desc = err[0].Error()
			}
			//	[200623-ToM] 온체인 계정 비밀번호를 해싱하고 16진수로 저장한다.
			hash := sha256.New()
			hash.Write([]byte(gConfig.Password))
			now := time.Now()
			if result, err[1] = stmt.Exec(gOffchain.account, state, desc, fmt.Sprintf("%04d-%02d-%02d", now.Year(), now.Month(), now.Day()), fmt.Sprintf("%02d:%02d:%02d", now.Hour(), now.Minute(), now.Second()), hex.EncodeToString(hash.Sum(nil))); nil != err[1] {
				err[1] = fmt.Errorf("접속 이력 추가 실패(2): %s", err[1])
			} else if gOffchain.connect, err[1] = result.LastInsertId(); nil != err[1] {
				err[1] = fmt.Errorf("접속 이력 추가 실패(3): %s", err[1])
			}
			stmt.Close()
		}
	}
	if nil != err[1] {
		fmt.Printf("● %s\n", err[1])
	}
	return err[0]
}

// Logout 온체인에서 로그아웃한다.
// [반환] error 에러 발생 시 내용
func Logout() error {

	return nil
}

// OpenChannel 오프체인 채널을 개설한다.
// [반환] *Channel	채널 정보 포인터
// [반환] error	에러 발생 시 내용
// [인자] pid	결제 아이디
// [인자] link	루트 링크 객체 포인터
// [인자] blocks	파일 기준 링크 객체 목록
// [인자] chunkList	다운로드 대상 청크 목록 파일 경로
func OpenChannel(pid string, link *ipld.Link, blocks map[cid.Cid]*ipld.Link, chunkList string) (*Channel, error) {
	var state int
	var exists bool
	var err [2]error
	var stmt *sql.Stmt
	var newCh *Channel
	var result sql.Result
	var blkNo, chNo int64
	var resultCode float64
	var data, desc, chID string
	var resp map[string]interface{}

	publicKey, privateKey, _ := sign.Keypair(rand.Reader)
	params := url.Values{}
	params.Add("purchaseId", pid)
	params.Add("publicKey", base64.StdEncoding.EncodeToString([]byte(publicKey)))
	params.Add("downChunkList", chunkList)

	//	채널을 개설한다.
	begin := time.Now()
	if data, err[0] = httpPost(fmt.Sprintf("http://%s:%d/register/channelOpen", gConfig.OnchainIP, gConfig.OnchainPort), params.Encode()); nil != err[0] {
		err[0] = fmt.Errorf("채널 개설 실패: %s", err[0])
	} else if err[0] = json.Unmarshal([]byte(data), &resp); nil != err[0] {
		err[0] = fmt.Errorf("채널 개설 응답 분석 실패: %s", err[0])
	} else if resultCode, exists = resp["resultCode"].(float64); false == exists {
		err[0] = errors.New("항목(resultCode) 없음")
	} else if 0 /*성공*/ != resultCode {
		resultMsg, _ := resp["resultMessage"].(string)
		err[0] = fmt.Errorf("[%.0f] %s", resultCode, resultMsg)
	} else if chID, exists = resp["channelId"].(string); false == exists {
		err[0] = errors.New("항목(channelId) 없음")
	} else {
		//	channelOpenPeriod: 채널 오픈 주기?
		//	오프체인 채널 정보를 생성한다.
		newCh = newChannel(chID, publicKey, privateKey, link, blocks)
		gChID = chID
	}
	//	채널 개설 소요시간을 계산한다.
	elapsed := time.Now().Sub(begin)

	//	[200616-ToM] 블록을 추가한다.
	if err[1] = gOffchain.db.QueryRow("SELECT no FROM tb_blocks WHERE cid=? LIMIT 1", link.Cid.String()).Scan(&blkNo); nil != err[1] {
		if sql.ErrNoRows == err[1] {
			err[1] = nil
		} else {
			err[1] = fmt.Errorf("블록 조회 실패: %s", err[1])
		}
	}
	if nil == err[1] && 0 == blkNo {
		if stmt, err[1] = gOffchain.db.Prepare("INSERT INTO tb_blocks(cid,creation) VALUES(?,?)"); nil != err[1] {
			err[1] = fmt.Errorf("블록 추가 실패: %s", err[1])
		} else {
			if result, err[1] = stmt.Exec(link.Cid.String(), time.Now().String()); nil != err[1] {
				err[1] = fmt.Errorf("블록 추가 실패(2): %s", err[1])
			} else if blkNo, err[1] = result.LastInsertId(); nil != err[1] {
				err[1] = fmt.Errorf("블록 추가 실패(3): %s", err[1])
			}
			stmt.Close()
		}
	}
	if nil == err[1] {
		//	[200616-ToM] 채널 개설 이력을 추가한다.
		if stmt, err[1] = gOffchain.db.Prepare("INSERT INTO tb_channel(account_no,block_no,state,desc,channel_id,date,time,purchase_id,flag,elapsed) VALUES(?,?,?,?,?,?,?,?,?,?)"); nil != err[1] {
			err[1] = fmt.Errorf("채널 개설 이력 추가 실패: %s", err[1])
		} else {
			if nil != err[0] {
				state = -1 /*실패*/
				desc = err[0].Error()
			}
			now := time.Now()
			if result, err[1] = stmt.Exec(gOffchain.account, blkNo, state, desc, chID, fmt.Sprintf("%04d-%02d-%02d", now.Year(), now.Month(), now.Day()), fmt.Sprintf("%02d:%02d:%02d", now.Hour(), now.Minute(), now.Second()), pid, 1 /*채널개설*/, elapsed.String()); nil != err[1] {
				err[1] = fmt.Errorf("채널 개설 이력 추가 실패(2): %s", err[1])
			} else if chNo, err[1] = result.LastInsertId(); nil != err[1] {
				err[1] = fmt.Errorf("채널 개설 이력 추가 실패(3): %s", err[1])
			} else if nil != newCh {
				newCh.No = chNo
			}
			stmt.Close()
		}
	}
	if nil != err[1] {
		fmt.Printf("● %s\n", err[1])
	}
	return newCh, err[0]
}

// VerifyChannel 개설된 오프체인 채널을 검증한다.
// [반환] error	에러 발생 시 내용
// [인자] chID	채널 ID
// [인자] accID	상대 온체인 ID
// [인자] c	요청 블록 ID
func VerifyChannel(chID, accID string, c cid.Cid) (*Channel, error) {
	var exists bool
	var err [2]error
	var stmt *sql.Stmt
	var channel *Channel
	var result sql.Result
	var resultCode float64
	var state, subTotal int
	var accNo, blkNo, chNo int64
	var resp map[string]interface{}
	var data, desc, verifyKey, collection, validity string

	if nil == gOffchain.channels {
		return nil, errors.New("채널 객체 초기화 안됨")
	}

	params := url.Values{}
	params.Add("channelId", chID)

	//	채널을 검증한다.
	begin := time.Now()
	if data, err[0] = httpPost(fmt.Sprintf("http://%s:%d/validation/channel", gConfig.OnchainIP, gConfig.OnchainPort), params.Encode()); nil != err[0] {
		err[0] = fmt.Errorf("채널 검증 실패: %s", err[0])
	} else if err[0] = json.Unmarshal([]byte(data), &resp); nil != err[0] {
		err[0] = fmt.Errorf("채널 검증 응답 분석 실패: %s", err[0])
	} else if resultCode, exists = resp["resultCode"].(float64); false == exists {
		err[0] = errors.New("항목(resultCode) 없음")
	} else if 0 /*성공*/ != resultCode {
		resultMsg, _ := resp["resultMessage"].(string)
		err[0] = fmt.Errorf("[%.0f] %s", resultCode, resultMsg)
	} else if verifyKey, exists = resp["publicKey"].(string); false == exists {
		err[0] = errors.New("항목(publicKey) 없음")
	} else if collection, exists = resp["receiptCollection"].(string); false == exists {
		err[0] = errors.New("항목(receiptCollection) 없음")
	} else if validity, exists = resp["validity"].(string); false == exists {
		err[0] = errors.New("항목(validity) 없음")
	} else if "open" != validity {
		err[0] = fmt.Errorf("채널 검증 실패: %s", validity)
	} else {
		subTotal, _ = strconv.Atoi(collection)
		// [ToM] 오프체인 채널 정보를 설정한다.
		if channel, exists = gOffchain.channels[chID]; false == exists {
			channel = &Channel{ID: chID, SubTotal: subTotal, Receipts: make(mReceiptByAccount)}
			channel.PublicKey, _ = base64.StdEncoding.DecodeString(verifyKey)
			gOffchain.channels[chID] = channel
		}
	}
	//	채널 검증 소요시간을 계산한다.
	elapsed := time.Now().Sub(begin)

	//	[200616-ToM] 온체인 계정을 추가한다.
	if err[1] = gOffchain.db.QueryRow("SELECT no FROM tb_account WHERE onchain_id=? LIMIT 1", accID).Scan(&accNo); nil != err[1] {
		if sql.ErrNoRows == err[1] {
			err[1] = nil
		} else {
			err[1] = fmt.Errorf("계정 조회 실패: %s", err[1])
		}
	}
	if nil == err[1] && 0 /*계정없음*/ == accNo {
		if stmt, err[1] = gOffchain.db.Prepare("INSERT INTO tb_account(onchain_id,creation) VALUES(?,?)"); nil != err[1] {
			err[1] = fmt.Errorf("계정 추가 실패: %s", err[1])
		} else {
			if result, err[1] = stmt.Exec(accID, time.Now().String()); nil != err[1] {
				err[1] = fmt.Errorf("계정 추가 실패(2): %s", err[1])
			} else if gOffchain.account, err[1] = result.LastInsertId(); nil != err[1] {
				err[1] = fmt.Errorf("계정 추가 실패(3): %s", err[1])
			}
			stmt.Close()
		}
	}
	if nil != err[1] {
		goto DB_FAIL
	}
	//	[200616-ToM] 블록을 추가한다.
	if err[1] = gOffchain.db.QueryRow("SELECT no FROM tb_blocks WHERE cid=? LIMIT 1", c.String()).Scan(&blkNo); nil != err[1] {
		if sql.ErrNoRows == err[1] {
			err[1] = nil
		} else {
			err[1] = fmt.Errorf("블록 조회 실패: %s", err[1])
		}
	}
	if nil == err[1] && 0 /*블록없음*/ == blkNo {
		if stmt, err[1] = gOffchain.db.Prepare("INSERT INTO tb_blocks(cid,creation) VALUES(?,?)"); nil != err[1] {
			err[1] = fmt.Errorf("블록 추가 실패: %s", err[1])
		} else {
			if result, err[1] = stmt.Exec(c.String(), time.Now().String()); nil != err[1] {
				err[1] = fmt.Errorf("블록 추가 실패(2): %s", err[1])
			} else if blkNo, err[1] = result.LastInsertId(); nil != err[1] {
				err[1] = fmt.Errorf("블록 추가 실패(3): %s", err[1])
			}
			stmt.Close()
		}
	}
	if nil != err[1] {
		goto DB_FAIL
	}
	//	[200616-ToM] 채널 검증 이력을 추가한다.
	if stmt, err[1] = gOffchain.db.Prepare("INSERT INTO tb_channel(account_no,block_no,state,desc,channel_id,date,time,flag,elapsed) VALUES(?,?,?,?,?,?,?,?,?)"); nil != err[1] {
		err[1] = fmt.Errorf("채널 검증 이력 추가 실패: %s", err[1])
	} else {
		if nil != err[0] {
			state = -1 /*실패*/
			desc = err[0].Error()
		}
		now := time.Now()
		if result, err[1] = stmt.Exec(accNo, blkNo, state, desc, chID, fmt.Sprintf("%04d-%02d-%02d", now.Year(), now.Month(), now.Day()), fmt.Sprintf("%02d:%02d:%02d", now.Hour(), now.Minute(), now.Second()), 2 /*채널검증*/, elapsed.String()); nil != err[1] {
			err[1] = fmt.Errorf("채널 검증 이력 추가 실패(2): %s", err[1])
		} else if chNo, err[1] = result.LastInsertId(); nil != err[1] {
			err[1] = fmt.Errorf("채널 검증 이력 추가 실패(3): %s", err[1])
		} else if nil != channel {
			channel.No = chNo
		}
		stmt.Close()
	}
DB_FAIL:
	if nil != err[1] {
		fmt.Printf("● %s\n", err[1])
	}
	return channel, err[0]
}

// PublishReceipt ???
// [반환] error	에러 발생 시 내용
// [인자] chID	오프체인 채널 아이디
// [인자] receipt	서명된 전송 영수증
func PublishReceipt(chID, receipt string) error {

	//	fmt.Println("● 전송 영수증 게시")

	params := url.Values{}
	params.Add("channelId", chID)
	params.Add("receipt", receipt)
	resp, err := httpPost(fmt.Sprintf("http://%s:%d/send-to-verifier", gConfig.OnchainIP, gConfig.OnchainPort), params.Encode())
	if nil != err {
		return err
	}
	var result map[string]interface{}
	// 오프체인 채널 검증 결과를 분석한다.
	if err = json.Unmarshal([]byte(resp), &result); nil != err {
		return err
	}
	code, exists := result["resultCode"].(float64)
	if false == exists {
		return errors.New("There is no [resultCode] entry")
	}
	if 0 != code {
		msg, exists := result["resultMessage"].(string)
		if false == exists {
			return errors.New("There is no [resultMessage] entry")
		}
		return errors.New(msg)
	}
	state, exists := result["state"].(string)
	if false == exists {
		return errors.New("There is no [state] entry")
	}
	if "succeed" != state {
		return errors.New("Invalid transfer receipt")
	}
	return nil
}

// SortByDirAndFile ???
// [인자] parent 최상위 또는 상위 링크 객체 포인터
// [인자] childs 하위 객체 포인터 배열
// [인자] target 정렬 된 버퍼
func SortByDirAndFile(parent *ipld.Link, childs []*ipld.Link, target *map[cid.Cid]*ipld.Link) {
	//	1. 상위 블록을 추가한다.
	key := parent.Cid
	_, exists := (*target)[key]
	if false == exists {
		(*target)[key] = &ipld.Link{Cid: key, Name: parent.Name, Size: parent.Size, Err: parent.Err}
	}

	for _, child := range childs {
		if nil != child {
			if "" != child.Name {
				//	2. 이름이 있으면 폴더 or 파일이므로 재귀호출한다.
				SortByDirAndFile(child, child.Childs, target)
			} else {
				//	3. 이름이 없으면 그룹 또는 파일 데이터 블록이다.
				//	그룹 블록은 데이터 블록으로 배열에 추가한다.
				find := (*target)[key]
				find.Childs = append(find.Childs, &ipld.Link{Cid: child.Cid, Name: child.Name, Size: child.Size, Err: child.Err})
				if 0 < len(child.Childs) {
					//	4. 그룹의 자식 블록은 파일 블록을 키로 추가한다.
					SortByDirAndFile(parent, child.Childs, target)
				}
			}
		}
	}
}

// MakeDownloadChunks 다운로드 대상 청크 목록을 작성한다.
// [반환] string 다운로드 청크 목록
// [인자] ccid 복합콘텐츠 ID
// [인자] version 복합콘텐츠 버전
// [인자] mLinks ???
func MakeDownloadChunks(ccid, version cid.Cid, mLinks *map[cid.Cid]*ipld.Link) string {
	var list, blocks string
	var begin, end, iMax int

	for _, parent := range *mLinks {
		if nil == parent.Err {
			begin = -1
		} else {
			begin = 1
		}

		iMax = len(parent.Childs)
		for iIndex, child := range parent.Childs {
			if -1 == begin && nil != child.Err {
				begin = 2 + iIndex
			}
			if -1 != begin && (nil == child.Err || 1+iIndex == iMax) {
				if nil == child.Err {
					end = iIndex - 1
				} else {
					end = iIndex
				}
				end += 2
				//	다운로드 받을 블록 목록을 작성한다.
				if begin == end {
					blocks += fmt.Sprintf("%d,", begin)
				} else if 1+begin == end {
					blocks += fmt.Sprintf("%d,%d,", begin, end)
				} else {
					blocks += fmt.Sprintf("%d-%d,", begin, end)
				}
				begin = -1
			}
		}
		if -1 != begin {
			blocks += fmt.Sprintf("%d", begin)
		}
		if "" != blocks {
			list += fmt.Sprintf("%v\t%s\r\n", parent.Cid, strings.TrimRight(blocks, ","))
			blocks = ""
		}
	}
	if "" != list {
		list = ccid.String() + "/" + version.String() + "\r\n" + list
	}
	return list
}

// findLinkByCid ???
// [반환] *ipld.Link ???
// [인자] links	???
// [인자] c	???
func findLinkByCid(links []*ipld.Link, c cid.Cid) *ipld.Link {
	for _, link := range links {
		if nil != link {
			if link.Cid == c {
				return link
			}
			if 0 < len(link.Childs) {
				link = findLinkByCid(link.Childs, c)
				if nil != link {
					return link
				}
			}
		}
	}
	return nil
}

// findLinkByPath ???
// [반환] *ipld.Link ???
// [인자] links	???
// [인자] path	???
func findLinkByPath(links []*ipld.Link, path string) *ipld.Link {
	for _, link := range links {
		if nil != link {
			if link.Name == path {
				return link
			}
			if 0 < len(link.Childs) {
				if link := findLinkByPath(link.Childs, path); nil != link {
					return link
				}
			}
		}
	}
	return nil
}

// FindLinkByPaths ???
// [반환] *ipld.Link ???
// [반환] error	???
// [인자] links	???
// [인자] c	???
// [인자] paths	???
func FindLinkByPaths(links []*ipld.Link, c cid.Cid, paths []string) (*ipld.Link, error) {
	if "" != c.String() {
		if link := findLinkByCid(links, c); nil != link {
			return link, nil
		}
	}
	iCount := len(paths)
	for iIndex := 0; iIndex < iCount; iIndex++ {
		pLink := findLinkByPath(links, paths[iIndex])
		if nil != pLink {
			if 1+iIndex == iCount {
				return pLink, nil
			}
			links = pLink.Childs
		}
	}
	return nil, fmt.Errorf("No links found")
}

// ReadBlockList 콘텐츠 블록 목록을 읽는다.
// [반환] string 콘텐츠 블록 목록
// [반환] error	실패 시 에러(성공:nil)
// [인자] c	콘텐츠 아이디
func ReadBlockList(c cid.Cid) ([]byte, error) {
	gListMutex.Lock()
	defer gListMutex.Unlock()
	//	목록 저장 폴더를 생성한다.
	path := gBasePath + "/blocklist/"
	_, err := os.Stat(path)
	if os.IsNotExist(err) {
		err = os.MkdirAll(path, os.ModePerm)
	} else {
		err = nil
	}
	if nil != err {
		return nil, fmt.Errorf("폴더 생성 실패: %s", err)
	}
	path += c.String()
	//	목록 파일이 없으면 생략한다.
	if false == FileExists(path) {
		return nil, errors.New("목록 없음")
	}
	//	목록 파일을 읽는다.
	blockList, err := ioutil.ReadFile(path)
	if nil != err {
		return nil, err
	}
	return blockList, nil
}

// WriteBlockList 콘텐츠 블록 목록을 작성한다.
// [반환] error	실패 시 에러(성공:nil)
// [인자] c	콘텐츠 아이디
// [인자] blockList	콘텐츠 블록 목록
func WriteBlockList(c cid.Cid, blockList string) error {
	gListMutex.Lock()
	defer gListMutex.Unlock()
	//	목록이 비어있으면 생략한다.
	if 0 == len(blockList) {
		return nil
	}
	//	목록 저장 폴더를 생성한다.
	path := gBasePath + "/blocklist/"
	_, err := os.Stat(path)
	if os.IsNotExist(err) {
		err = os.MkdirAll(path, os.ModePerm)
	} else {
		err = nil
	}
	if nil != err {
		return fmt.Errorf("폴더 생성 실패: %s", err)
	}
	path += c.String()
	//	목록 파일이 있으면 생략한다.
	if FileExists(path) {
		return nil
	}
	//	목록 파일을 생성한다.
	err = ioutil.WriteFile(path, []byte(blockList), os.ModePerm)
	if nil != err {
	}
	return err
}
