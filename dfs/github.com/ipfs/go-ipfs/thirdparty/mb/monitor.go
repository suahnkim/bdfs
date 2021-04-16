// +build !linux

package mb

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"io/ioutil"
	"math/rand"
	"net/http"
	"os"
	"path/filepath"
	"sort"
	"strconv"
	"strings"
	"time"

	_ "github.com/mattn/go-sqlite3"
)

type peer struct {
	Addr      string
	Peer      string
	Latency   string
	Muxer     string
	Direction int
}

type peers struct {
	Peers []peer
}

var gOnchainID string
var gmSessions = make(map[string]time.Time)

func min(v1, v2 int) int {
	if v1 < v2 {
		return v1
	}
	return v2
}

func max(v1, v2 int) int {
	if v1 < v2 {
		return v2
	}
	return v1
}

func getContentType(localPath string) string {
	var contentType string
	switch filepath.Ext(localPath) {
	case ".html":
		contentType = "text/html"
	case ".css":
		contentType = "text/css"
	case ".js":
		contentType = "application/javascript"
	case ".png":
		contentType = "image/png"
	case ".jpg":
		contentType = "image/jpeg"
	default:
		contentType = "text/plain"
	}
	return contentType
}

func respLogin(html string, db *sql.DB) (string, error) {
	var onchainID string
	err := db.QueryRow("SELECT onchain_id FROM tb_account account, (SELECT account_no FROM tb_connect WHERE state=? ORDER BY no DESC LIMIT 1) connect WHERE account.no=connect.account_no", 0 /*성공*/).Scan(&onchainID)
	if nil != err {
		err = fmt.Errorf("접속 이력 조회 실패: %s", err)
		html = ""
	} else {
		html = strings.Replace(html, "{%1}", onchainID, 1)
	}
	return html, err
}

func dirSizeGB(path string) float64 {
	var size int64
	filepath.Walk(path, func(path string, file os.FileInfo, err error) error {
		if false == file.IsDir() {
			size += file.Size()
		}
		return nil
	})
	return float64(size) / 1024.0 / 1024.0 / 1024.0
}

func respHome(html, rootPath string, storageMax float64, db *sql.DB) (string, error) {
	var conns string
	resp, err := httpGet("http://127.0.0.1:5001/api/v0/swarm/peers?encoding=json&stream-channels=true")
	if nil != err {
		fmt.Println("노드 조회 실패:", err)
	} else {
		var _peers peers
		if err = json.Unmarshal([]byte(resp), &_peers); nil != err {
			fmt.Println("노드 조회 실패(2):", err)
		} else {
			for index, peer := range _peers.Peers {
				conns += fmt.Sprintf("<tr><td>%d</td><td>%s</td><td>%s</td></tr>", 1+index, peer.Addr, peer.Peer)
			}
		}
	}

	html = strings.Replace(html, "{%1}", gOnchainID, 1)
	//	블록 스토리지 저장 공간을 계산한다.
	size := dirSizeGB(rootPath)
	html = strings.Replace(html, "{%2}", fmt.Sprintf("%.1fGB / %.1fGB", size, storageMax), 1)
	html = strings.Replace(html, "{%3}", fmt.Sprintf("%.0f", size/storageMax*100), 1)
	html = strings.Replace(html, "{%4}", conns, 1)
	return html, nil
}

func respConnect(html, id, begin, end string, wantPage int, db *sql.DB) (string, error) {
	var err error
	var rs *sql.Rows
	var no, total int
	var between [2]int
	var query [2]string
	var _date, _time, onchainID, table, paging string

	query[0] = "SELECT ROW_NUMBER() OVER(ORDER BY conn.no) AS no,acc.onchain_id,conn.date,conn.time FROM tb_connect conn, (SELECT no,onchain_id FROM tb_account) AS acc WHERE acc.no=conn.account_no"
	if "" != id || "" != begin || "" != end {
		if "" != begin {
			query[0] += fmt.Sprintf(" AND conn.date>='%s'", begin)
		}
		if "" != end {
			query[0] += fmt.Sprintf(" AND conn.date<='%s'", end)
		}
		if "" != id {
			query[0] += fmt.Sprintf(" AND acc.onchain_id='%s'", id)
		}
	}
	query[1] = fmt.Sprintf("SELECT COUNT(*) FROM (%s)", query[0])
	if err = db.QueryRow(query[1]).Scan(&total); nil != err {
		goto DB_FAIL
	}
	if 0 == total {
		goto NO_DATA
	}

	if -1 /*마지막페이지*/ == wantPage {
		between[1] = total % 10
		between[0] = 1
	} else {
		between[1] = total - 10*(wantPage-1)
		between[0] = total - (10*wantPage - 1)
		if 1 > between[0] {
			between[0] = 1
		}
	}
	query[1] = fmt.Sprintf("SELECT * FROM (%s) AS conn WHERE conn.no BETWEEN %d AND %d ORDER BY conn.no DESC", query[0], between[0], between[1])
	if rs, err = db.Query(query[1]); nil != err {
		goto DB_FAIL
	}
	for rs.Next() {
		if err = rs.Scan(&no, &onchainID, &_date, &_time); nil != err {
			break
		}
		table += fmt.Sprintf("<tr><td>%d</td><td>%s %s</td><td>%s</td><td>%s %s</td></tr>", no, _date, _time, onchainID, "", "")
	}
	rs.Close()
	//	1 페이지 이상인지 검사한다.
	if 10 < total {
		lastPage := total / 10
		if 0 < total%10 {
			lastPage++
		}
		if -1 /*마지막페이지*/ == wantPage {
			wantPage = lastPage
		}
		paging = fmt.Sprintf(`<div class="page_wrap" id="paging" name="paging"><ul class="page_list">
		<li><a href="#" class="pf" title="First" onclick="doSearch(1);"><span class="pf_p fas fa-angle-double-left"></span></a></li>
		<li><a href="#" class="pf" title="Previous" onclick="doSearch(%d);"><span class="pf_pre fas fa-angle-left"></span></a></li>`, max(wantPage-1, 1))
		pageNo := wantPage / 10
		if 0 == wantPage%10 {
			pageNo--
		}
		pageNo = 1 + 10*pageNo
		for count := 0; count < 10; count++ {
			if pageNo == wantPage {
				paging += fmt.Sprintf(`<li><a href="#" class="pnum pon" onclick="doSearch(%d);">%d</a></li>`, pageNo, pageNo)
			} else {
				paging += fmt.Sprintf(`<li><a href="#" class="pnum" onclick="doSearch(%d);">%d</a></li>`, pageNo, pageNo)
			}
			if pageNo == lastPage {
				break
			}
			pageNo++
		}
		paging += fmt.Sprintf(`<li><a href="#" class="pf" title="Next" onclick="doSearch(%d);"><span class="pf_nex fas fa-angle-right"></span></a></li>
		<li><a href="#" class="pf" title="Last" onclick="doSearch(-1);"><span class="pf_f fas fa-angle-double-right"></span></a></li></ul></div>`, min(1+wantPage, lastPage))
	}
DB_FAIL:
NO_DATA:
	//	접속자를 패치한다.
	html = strings.Replace(html, "{%1}", gOnchainID, 1)
	//	검색 조건을 패치한다.
	html = strings.Replace(html, "{%2}", id, 1)
	html = strings.Replace(html, "{%3}", begin, 1)
	html = strings.Replace(html, "{%4}", end, 1)
	//	검색 결과를 패치한다.
	html = strings.Replace(html, "{%5}", table, 1)
	//	페이징 결과를 패치한다.
	html = strings.Replace(html, "{%6}", paging, 1)
	return html, nil
}

func respChannel(html, _type, id, begin, end string, wantPage int, db *sql.DB) (string, error) {
	var err error
	var rs *sql.Rows
	var between [2]int
	var query [2]string
	var no, total, flag int
	var purchaseID sql.NullString
	var _date, _time, channelID, table, paging, elapsed, _flag, cid string

	query[0] = "SELECT ROW_NUMBER() OVER(ORDER BY ch.no) AS no,ch.date,ch.time,ch.channel_id,ch.flag,ch.elapsed,blk.cid,ch.purchase_id FROM tb_blocks blk, (SELECT * FROM tb_channel ORDER BY no desc) AS ch WHERE blk.no=ch.block_no"
	if "" != id || "" != begin || "" != end {
		if "" != begin {
			query[0] += fmt.Sprintf(" AND ch.date>='%s'", begin)
		}
		if "" != end {
			query[0] += fmt.Sprintf(" AND ch.date<='%s'", end)
		}
		if "" != id {
			switch _type {
			case "1": //	채널ID
				query[0] += fmt.Sprintf(" AND ch.channel_id='%s'", id)
			case "2": //	콘텐츠ID
				query[0] += fmt.Sprintf(" AND blk.cid='%s'", id)
			case "3": //	결제ID
				query[0] += fmt.Sprintf(" AND ch.purchase_id='%s'", id)
			}
		}
	}
	query[1] = fmt.Sprintf("SELECT COUNT(*) FROM (%s)", query[0])
	if err = db.QueryRow(query[1]).Scan(&total); nil != err {
		goto DB_FAIL
	}
	if 0 == total {
		goto NO_DATA
	}

	if -1 /*마지막페이지*/ == wantPage {
		between[1] = total % 10
		between[0] = 1
	} else {
		between[1] = total - 10*(wantPage-1)
		between[0] = total - (10*wantPage - 1)
		if 1 > between[0] {
			between[0] = 1
		}
	}
	query[1] = fmt.Sprintf("SELECT * FROM (%s) AS ch WHERE ch.no BETWEEN %d AND %d ORDER BY ch.no DESC", query[0], between[0], between[1])
	if rs, err = db.Query(query[1]); nil != err {
		goto DB_FAIL
	}
	for rs.Next() {
		if err = rs.Scan(&no, &_date, &_time, &channelID, &flag, &elapsed, &cid, &purchaseID); nil != err {
			break
		}
		if 1 /*개설*/ == flag {
			_flag = "개설"
		} else {
			_flag = "검증"
		}
		elapsed, _ := time.ParseDuration(elapsed)
		table += fmt.Sprintf(`<tr><td>%d</td><td>%s<br>%s</td><td>%s</td><td>%s</td><td>%.0f초</td><td>%s</td><td>%s</td></tr>`, no, _date, _time, channelID, _flag, elapsed.Seconds(), cid, purchaseID.String)
	}
	rs.Close()
	//	1 페이지 이상인지 검사한다.
	if 10 < total {
		lastPage := total / 10
		if 0 < total%10 {
			lastPage++
		}
		if -1 /*마지막페이지*/ == wantPage {
			wantPage = lastPage
		}
		paging = fmt.Sprintf(`<div class="page_wrap" id="paging" name="paging"><ul class="page_list">
		<li><a href="#" class="pf" title="First" onclick="doSearch(1);"><span class="pf_p fas fa-angle-double-left"></span></a></li>
		<li><a href="#" class="pf" title="Previous" onclick="doSearch(%d);"><span class="pf_pre fas fa-angle-left"></span></a></li>`, max(wantPage-1, 1))
		pageNo := wantPage / 10
		if 0 == wantPage%10 {
			pageNo--
		}
		pageNo = 1 + 10*pageNo
		for count := 0; count < 10; count++ {
			if pageNo == wantPage {
				paging += fmt.Sprintf(`<li><a href="#" class="pnum pon" onclick="doSearch(%d);">%d</a></li>`, pageNo, pageNo)
			} else {
				paging += fmt.Sprintf(`<li><a href="#" class="pnum" onclick="doSearch(%d);">%d</a></li>`, pageNo, pageNo)
			}
			if pageNo == lastPage {
				break
			}
			pageNo++
		}
		paging += fmt.Sprintf(`<li><a href="#" class="pf" title="Next" onclick="doSearch(%d);"><span class="pf_nex fas fa-angle-right"></span></a></li>
		<li><a href="#" class="pf" title="Last" onclick="doSearch(-1);"><span class="pf_f fas fa-angle-double-right"></span></a></li></ul></div>`, min(1+wantPage, lastPage))
	}
DB_FAIL:
NO_DATA:
	//	접속자를 패치한다.
	html = strings.Replace(html, "{%1}", gOnchainID, 1)
	//	검색 조건을 패치한다.
	html = strings.Replace(html, "{%2}", id, 1)
	html = strings.Replace(html, "{%3}", begin, 1)
	html = strings.Replace(html, "{%4}", end, 1)
	//	검색 결과를 패치한다.
	html = strings.Replace(html, "{%5}", table, 1)
	//	페이징 결과를 패치한다.
	html = strings.Replace(html, "{%6}", paging, 1)
	return html, nil
}

func respData(html, _type, id, begin, end string, wantPage int, db *sql.DB) (string, error) {
	var err error
	var rs *sql.Rows
	var between [2]int
	var query [2]string
	var no, total, dir, receipt, overlap int
	var _date, _time, channelID, cid, onchainID, table, paging, _dir, _overlap string

	query[0] = "SELECT ROW_NUMBER() OVER(ORDER BY data.no) AS no,data.date,data.time,data.direction,ch.channel_id,blk.cid,acc.onchain_id,data.receipt,data.overlap FROM tb_share_data data, (SELECT no,channel_id,block_no FROM tb_channel) ch,(SELECT no,cid FROM tb_blocks) blk,(SELECT no,onchain_id FROM tb_account) acc WHERE ch.no=data.channel_no AND blk.no=data.block_no AND acc.no=data.account_no"
	if "" != id || "" != begin || "" != end {
		if "" != begin {
			query[0] += fmt.Sprintf(" AND data.date>='%s'", begin)
		}
		if "" != end {
			query[0] += fmt.Sprintf(" AND data.date<='%s'", end)
		}
		if "" != id {
			switch _type {
			case "1": //	채널ID
				query[0] += fmt.Sprintf(" AND ch.channel_id='%s'", id)
			case "2": //	블록ID
				query[0] += fmt.Sprintf(" AND blk.cid='%s'", id)
			}
		}
	}
	query[1] = fmt.Sprintf("SELECT COUNT(*) FROM (%s)", query[0])
	if err = db.QueryRow(query[1]).Scan(&total); nil != err {
		goto DB_FAIL
	}
	if 0 == total {
		goto NO_DATA
	}

	if -1 /*마지막페이지*/ == wantPage {
		between[1] = total % 10
		between[0] = 1
	} else {
		between[1] = total - 10*(wantPage-1)
		between[0] = total - (10*wantPage - 1)
		if 1 > between[0] {
			between[0] = 1
		}
	}
	query[1] = fmt.Sprintf("SELECT * FROM (%s) AS data WHERE data.no BETWEEN %d AND %d ORDER BY data.no DESC", query[0], between[0], between[1])
	if rs, err = db.Query(query[1]); nil != err {
		goto DB_FAIL
	}
	for rs.Next() {
		if err = rs.Scan(&no, &_date, &_time, &dir, &channelID, &cid, &onchainID, &receipt, &overlap); nil != err {
			break
		}
		if 1 /*송신*/ == dir {
			_dir = "송신"
		} else {
			_dir = "수신"
		}
		if 1 /*중복*/ == overlap {
			_overlap = "○"
		} else {
			_overlap = ""
		}
		table += fmt.Sprintf(`<tr><td>%d</td><td>%s<br>%s</td><td>%s</td><td><a href="/channel?type=1&id=%s">%s</a></td><td>%s</td><td>%s</td><td>%s</td></tr>`, no, _date, _time, _dir, channelID, channelID, cid, onchainID, _overlap)
	}
	rs.Close()
	//	1 페이지 이상인지 검사한다.
	if 10 < total {
		lastPage := total / 10
		if 0 < total%10 {
			lastPage++
		}
		if -1 /*마지막페이지*/ == wantPage {
			wantPage = lastPage
		}
		paging = fmt.Sprintf(`<div class="page_wrap" id="paging" name="paging"><ul class="page_list">
		<li><a href="#" class="pf" title="First" onclick="doSearch(1);"><span class="pf_p fas fa-angle-double-left"></span></a></li>
		<li><a href="#" class="pf" title="Previous" onclick="doSearch(%d);"><span class="pf_pre fas fa-angle-left"></span></a></li>`, max(wantPage-1, 1))
		pageNo := wantPage / 10
		if 0 == wantPage%10 {
			pageNo--
		}
		pageNo = 1 + 10*pageNo
		for count := 0; count < 10; count++ {
			if pageNo == wantPage {
				paging += fmt.Sprintf(`<li><a href="#" class="pnum pon" onclick="doSearch(%d);">%d</a></li>`, pageNo, pageNo)
			} else {
				paging += fmt.Sprintf(`<li><a href="#" class="pnum" onclick="doSearch(%d);">%d</a></li>`, pageNo, pageNo)
			}
			if pageNo == lastPage {
				break
			}
			pageNo++
		}
		paging += fmt.Sprintf(`<li><a href="#" class="pf" title="Next" onclick="doSearch(%d);"><span class="pf_nex fas fa-angle-right"></span></a></li>
		<li><a href="#" class="pf" title="Last" onclick="doSearch(-1);"><span class="pf_f fas fa-angle-double-right"></span></a></li></ul></div>`, min(1+wantPage, lastPage))
	}
DB_FAIL:
NO_DATA:
	//	접속자를 패치한다.
	html = strings.Replace(html, "{%1}", gOnchainID, 1)
	//	검색 조건을 패치한다.
	html = strings.Replace(html, "{%2}", id, 1)
	html = strings.Replace(html, "{%3}", begin, 1)
	html = strings.Replace(html, "{%4}", end, 1)
	//	검색 결과를 패치한다.
	html = strings.Replace(html, "{%5}", table, 1)
	//	페이징 결과를 패치한다.
	html = strings.Replace(html, "{%6}", paging, 1)
	return html, nil
}

func monitor(rootPath string, storageMax float64, listen int, db *sql.DB) {
	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		if err := r.ParseForm(); nil != err {
			return
		}

		if "/" == r.URL.Path {
			r.URL.Path = "/html/login.html"
		}
		localPath := "./www" + r.URL.Path
		bin, err := ioutil.ReadFile(localPath)
		if nil != err {
			w.WriteHeader(404)
			w.Write([]byte(http.StatusText(404)))
			return
		}

		html := string(bin)
		if strings.Contains(r.URL.Path, "login") {
			if html, err = respLogin(html, db); nil != err {
			}
		}

		w.Header().Add("Content-Type", getContentType(localPath))
		w.Write([]byte(html))
	})

	http.HandleFunc("/login", func(w http.ResponseWriter, r *http.Request) {
		fmt.Println(r.Host, r.RequestURI)

		if err := r.ParseForm(); nil != err {
			return
		}

		var accountNo int64
		var session, password, desc string

		result := -1 /*실패*/
		params := make(map[string]interface{})
		decoder := json.NewDecoder(r.Body)
		err := decoder.Decode(&params)
		if nil != err {
		} else if err = db.QueryRow("SELECT account_no,password FROM tb_connect WHERE state=? ORDER BY no DESC LIMIT 1", 0 /*성공*/).Scan(&accountNo, &password); nil != err {
			desc = fmt.Sprintf("접속 이력 조회 실패: %s", err)
		} else if params["password"].(string) != password {
			result = 1 /*비밀번호불일치*/
			desc = "비밀번호 불일치"
		} else if err = db.QueryRow("SELECT onchain_id FROM tb_account WHERE no=?", accountNo).Scan(&gOnchainID); nil != err {
			desc = fmt.Sprintf("계정 조회 실패: %s", err)
		} else {
			//	세션키(SHA256 -> 16진수)를 생성한다.
			var b strings.Builder
			rand.Seed(time.Now().UnixNano())
			chars := []rune("ABCDEFGHIJKLMNOPQRSTUVWXYZ" + "abcdefghijklmnopqrstuvwxyz" + "0123456789")
			for count := 0; 16 > count; count++ {
				b.WriteRune(chars[rand.Intn(len(chars))])
			}
			session = b.String()
			if stmt, err := db.Prepare("INSERT INTO tb_session(session,creation) VALUES(?,?)"); nil != err {
				desc = fmt.Sprintf("세션 추가 실패: %s", err)
			} else if _, err = stmt.Exec(session, time.Now()); nil != err {
				desc = fmt.Sprintf("세션 추가 실패(2): %s", err)
			} else {
				//	무응답 세션 타임아웃은 10분이다.
				gmSessions[session] = time.Now().Add(time.Minute * time.Duration(10))
				result = 0
			}
		}

		resp := fmt.Sprintf(`{"result":%d,"desc":"%s"`, result, desc)
		if 0 == result {
			resp += fmt.Sprintf(`,"session":"%s"`, session)
		}
		resp += "}"
		fmt.Println(resp)
		w.Write([]byte(resp))
	})

	http.HandleFunc("/logout", func(w http.ResponseWriter, r *http.Request) {
		fmt.Println(r.Host, r.RequestURI)

		if err := r.ParseForm(); nil != err {
			return
		}

		var desc string

		result := -1
		session, err := r.Cookie("session")
		if nil != err {
			return
		}
		if _, exists := gmSessions[session.Value]; false == exists {
			fmt.Println("세션 없음")
			return
		}

		gOnchainID = ""

		if stmt, err := db.Prepare("DELETE FROM tb_session WHERE session=?"); nil != err {
			desc = fmt.Sprintf("세션 삭제 실패: %s", err)
		} else if _, err = stmt.Exec(session.Value); nil != err {
			desc = fmt.Sprintf("세션 삭제 실패(2): %s", err)
		} else {
			//	세션 정보를 삭제한다.
			delete(gmSessions, session.Value)
			result = 0
		}

		w.Write([]byte(fmt.Sprintf(`{"result":%d,"desc":"%s"}`, result, desc)))
	})

	http.HandleFunc("/state", func(w http.ResponseWriter, r *http.Request) {
		fmt.Println(r.Host, r.RequestURI)

		if err := r.ParseForm(); nil != err {
			return
		}

		var html string

		//	세션 쿠키를 검색한다.
		session, err := r.Cookie("session")
		if nil != err {
			return
		}
		//	세션 정보를 검색한다.
		if _, exists := gmSessions[session.Value]; false == exists {
			fmt.Println("세션 없음")
			return
		}
		//	세션 타임아웃을 검사한다.
		dtNow := time.Now()
		//	무응답 세션 타임아웃을 연장한다.
		gmSessions[session.Value] = dtNow.Add(time.Minute * time.Duration(10))

		localPath := "./www/html/f_1_1.html"
		bin, err := ioutil.ReadFile(localPath)
		if nil != err {
			w.WriteHeader(404)
			w.Write([]byte(http.StatusText(404)))
			return
		}

		if html, err = respHome(string(bin), rootPath, storageMax, db); nil != err {
		}

		w.Header().Add("Content-Type", getContentType(localPath))
		w.Write([]byte(html))
	})

	http.HandleFunc("/js/tom.chartjs.js", func(w http.ResponseWriter, r *http.Request) {
		fmt.Println(r.Host, r.RequestURI)

		if err := r.ParseForm(); nil != err {
			return
		}

		var count int
		var data [4]string
		var keys [5][]string
		var _time, labels string
		var mCount [4]map[string]int

		//	세션 쿠키를 검색한다.
		session, err := r.Cookie("session")
		if nil != err {
			return
		}
		//	세션 정보를 검색한다.
		if _, exists := gmSessions[session.Value]; false == exists {
			fmt.Println("세션 없음")
			return
		}

		localPath := "./www" + r.URL.Path
		bin, err := ioutil.ReadFile(localPath)
		if nil != err {
			w.WriteHeader(404)
			w.Write([]byte(http.StatusText(404)))
			return
		}

		mCount[0 /*데이터송신*/] = make(map[string]int)
		mCount[1 /*데이터수신*/] = make(map[string]int)
		mCount[2 /*영수증발행*/] = make(map[string]int)
		mCount[3 /*영수증수신*/] = make(map[string]int)

		rs, err := db.Query("SELECT date,COUNT(date) FROM tb_share_data WHERE direction=? GROUP BY date", 1 /*송신*/)
		if nil != err {
		}
		for rs.Next() {
			if err = rs.Scan(&_time, &count); nil != err {
			}
			mCount[0 /*데이터송신*/][_time] = count
		}
		rs.Close()
		if rs, err = db.Query("SELECT date,COUNT(date) FROM tb_share_data WHERE direction=? GROUP BY date", 2 /*수신*/); nil != err {
		}
		for rs.Next() {
			if err = rs.Scan(&_time, &count); nil != err {
			}
			mCount[1 /*데이터수신*/][_time] = count
		}
		rs.Close()
		if rs, err = db.Query("SELECT date,COUNT(date) FROM tb_receipts WHERE flag=? GROUP BY date", 1 /*발행*/); nil != err {
		}
		for rs.Next() {
			if err = rs.Scan(&_time, &count); nil != err {
			}
			mCount[2 /*영수증발행*/][_time] = count
		}
		rs.Close()
		if rs, err = db.Query("SELECT date,COUNT(date) FROM tb_receipts WHERE flag=? GROUP BY date", 2 /*수신*/); nil != err {
		}
		for rs.Next() {
			if err = rs.Scan(&_time, &count); nil != err {
			}
			mCount[3 /*영수증수신*/][_time] = count
		}
		rs.Close()

		//	map을 키(날짜)로 정렬한다.
		mAllTimes := make(map[string]int)
		for index := 0; 4 > index; index++ {
			keys[index] = make([]string, 0, len(mCount[index]))
			for k := range mCount[index] {
				keys[index] = append(keys[index], k)
				mAllTimes[k] = 0
			}
			sort.Strings(keys[index])
		}

		if 0 < len(mAllTimes) {
			//	레이블 값을 정렬한다.
			keys[4] = make([]string, 0, len(mAllTimes))
			for k := range mAllTimes {
				keys[4] = append(keys[4], k)
			}
			sort.Strings(keys[4])

			//	차트 데이터를 작성한다.
			for _, k := range keys[4] {
				labels += `"` + k + `",`
				for index := 0; 4 > index; index++ {
					data[index] += strconv.Itoa(mCount[index][k]) + ","
				}
			}
			for index := 0; 4 > index; index++ {
				data[index] = strings.TrimRight(data[index], ",")
			}

			labels = strings.ReplaceAll(labels[:len(labels)-1], "-", ".")
		}

		js := strings.ReplaceAll(string(bin), "{%1}", labels)
		js = strings.ReplaceAll(js, "{%2}", data[1 /*데이터수신*/])
		js = strings.ReplaceAll(js, "{%3}", data[0 /*데이터송신*/])
		js = strings.ReplaceAll(js, "{%4}", data[2 /*영수증발행*/])
		js = strings.ReplaceAll(js, "{%5}", data[3 /*영수증수신*/])

		w.Header().Add("Content-Type", getContentType(localPath))
		w.Write([]byte(js))
	})

	http.HandleFunc("/connect", func(w http.ResponseWriter, r *http.Request) {
		fmt.Println(r.Host, r.RequestURI)

		if err := r.ParseForm(); nil != err {
			return
		}

		var html string

		//	세션 쿠키를 검색한다.
		session, err := r.Cookie("session")
		if nil != err {
			return
		}
		//	세션 정보를 검색한다.
		if _, exists := gmSessions[session.Value]; false == exists {
			fmt.Println("세션 없음")
			return
		}
		//	세션 타임아웃을 검사한다.
		dtNow := time.Now()
		//	무응답 세션 타임아웃을 연장한다.
		gmSessions[session.Value] = dtNow.Add(time.Minute * time.Duration(10))

		id := r.FormValue("id")
		begin := r.FormValue("begin")
		end := r.FormValue("end")

		wantPage := 1
		if page := r.FormValue("page"); "" != page {
			if wantPage, err = strconv.Atoi(page); nil != err {
				wantPage = 1
			}
		}

		localPath := "./www/html/f_2_1.html"
		bin, err := ioutil.ReadFile(localPath)
		if nil != err {
			w.WriteHeader(404)
			w.Write([]byte(http.StatusText(404)))
			return
		}

		if html, err = respConnect(string(bin), id, begin, end, wantPage, db); nil != err {
		}

		w.Header().Add("Content-Type", getContentType(localPath))
		w.Write([]byte(html))
	})

	http.HandleFunc("/channel", func(w http.ResponseWriter, r *http.Request) {
		fmt.Println(r.Host, r.RequestURI)

		if err := r.ParseForm(); nil != err {
			return
		}

		var html string

		//	세션 쿠키를 검색한다.
		session, err := r.Cookie("session")
		if nil != err {
			return
		}
		//	세션 정보를 검색한다.
		if _, exists := gmSessions[session.Value]; false == exists {
			fmt.Println("세션 없음")
			return
		}
		//	세션 타임아웃을 검사한다.
		dtNow := time.Now()
		//	무응답 세션 타임아웃을 연장한다.
		gmSessions[session.Value] = dtNow.Add(time.Minute * time.Duration(10))

		_type := r.FormValue("type")
		id := r.FormValue("id")
		begin := r.FormValue("begin")
		end := r.FormValue("end")

		wantPage := 1
		if page := r.FormValue("page"); "" != page {
			if wantPage, err = strconv.Atoi(page); nil != err {
				wantPage = 1
			}
		}

		localPath := "./www/html/f_2_2.html"
		bin, err := ioutil.ReadFile(localPath)
		if nil != err {
			w.WriteHeader(404)
			w.Write([]byte(http.StatusText(404)))
			return
		}

		if html, err = respChannel(string(bin), _type, id, begin, end, wantPage, db); nil != err {
		}

		w.Header().Add("Content-Type", getContentType(localPath))
		w.Write([]byte(html))
	})

	http.HandleFunc("/data", func(w http.ResponseWriter, r *http.Request) {
		fmt.Println(r.Host, r.RequestURI)

		if err := r.ParseForm(); nil != err {
			return
		}

		var html string

		//	세션 쿠키를 검색한다.
		session, err := r.Cookie("session")
		if nil != err {
			return
		}
		//	세션 정보를 검색한다.
		if _, exists := gmSessions[session.Value]; false == exists {
			fmt.Println("세션 없음")
			return
		}
		//	세션 타임아웃을 검사한다.
		dtNow := time.Now()
		//	무응답 세션 타임아웃을 연장한다.
		gmSessions[session.Value] = dtNow.Add(time.Minute * time.Duration(10))

		_type := r.FormValue("type")
		id := r.FormValue("id")
		begin := r.FormValue("begin")
		end := r.FormValue("end")

		wantPage := 1
		if page := r.FormValue("page"); "" != page {
			if wantPage, err = strconv.Atoi(page); nil != err {
				wantPage = 1
			}
		}

		localPath := "./www/html/f_2_3.html"
		bin, err := ioutil.ReadFile(localPath)
		if nil != err {
			w.WriteHeader(404)
			w.Write([]byte(http.StatusText(404)))
			return
		}

		if html, err = respData(string(bin), _type, id, begin, end, wantPage, db); nil != err {
		}

		w.Header().Add("Content-Type", getContentType(localPath))
		w.Write([]byte(html))
	})

	http.ListenAndServe(fmt.Sprintf("127.0.0.1:%d", listen), nil)
}

// BeginMonitor 노드 모니터 웹서비스를 실행한다.
func BeginMonitor(rootPath string, storageMax float64, listen int) error {
	var err error
	var db *sql.DB

	if db, err = sql.Open("sqlite3", rootPath+`\ipfs_mb.db`); nil != err {
		err = fmt.Errorf("DB 오픈 실패: %s", err)
	} else {
		//	노드 모니터 웹 서비스를 실행한다.
		go monitor(rootPath, storageMax, listen, db)
	}

	return err
}
