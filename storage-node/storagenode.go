package main

import (
	"bufio"
	"bytes"
	"context"
	"crypto/tls"
	"database/sql"
	"encoding/json"
	"errors"
	"fmt"
	"io/ioutil"
	"net"
	"os"
	"runtime"
	"strconv"

	//	"gx/ipfs/QmWsJXti4Nft4vXR8brjG2fJCUDWeJpwNVngyU42pByJxN/color"
	"log"
	"net/http"
	"os/exec"
	"os/user"
	"strings"
	"sync"
	"time"

	_ "github.com/go-sql-driver/mysql"
	ps "github.com/mitchellh/go-ps"
)

const gConfigFile string = "storagenode.json"

//	디버그 모드 유무
var gDebugMode bool

//	로그 파일 전역 객체
var gFile *os.File
var gLogDate time.Time
var gLogger *log.Logger

type output struct {
	buf *bytes.Buffer
	*sync.Mutex
}

func newOutput() *output {
	return &output{
		buf:   &bytes.Buffer{},
		Mutex: &sync.Mutex{},
	}
}

func (rw *output) Write(p []byte) (int, error) {
	rw.Lock()
	defer rw.Unlock()
	return rw.buf.Write(p)
}

func (rw *output) Lines() []string {
	rw.Lock()
	defer rw.Unlock()
	var lines []string
	s := bufio.NewScanner(rw.buf)
	for s.Scan() {
		if 0 < len(s.Text()) {
			lines = append(lines, s.Text())
		}
	}
	return lines
}

// httpPost HTTP Post 메소드로 호출한다.
func httpPost(URL, contentType, data string) (string, int, error) {
	keepAliveTimeout := 600 * time.Second
	timeout := 3 * time.Second
	defaultTransport := &http.Transport{
		Dial: (&net.Dialer{
			KeepAlive: keepAliveTimeout,
		}).Dial,
		MaxIdleConns:        100,
		MaxIdleConnsPerHost: 100,
		TLSClientConfig:     &tls.Config{InsecureSkipVerify: true},
	}
	client := &http.Client{
		Transport: defaultTransport,
		Timeout:   timeout,
	}
	resp, err := client.Post(URL, contentType, bytes.NewBufferString(data))
	if nil != err {
		//	[200902-jih] 에러 발생 시 resp가 nil이 된다.
		return "", 0 /*resp.StatusCode*/, err
	}
	body, _ := ioutil.ReadAll(resp.Body)
	resp.Body.Close()
	return strings.TrimSpace(string(body)), resp.StatusCode, nil
}

const (
	//	다운로드 시작
	eventDownBegin int = 0x20 + iota
	//	다운로드 실패
	eventDownFail
	//	다운로드 취소
	eventDownCancel
	//	다운로드 성공
	eventDownSuccess
)

//	dbms DBMS 정보 구조체
type dbms struct {
	//	접속 주소:포트
	Host string
	//	계정 아이디
	Account string
	//	계정 비밀번호
	Password string
	//	스키마 이름
	Schema string
}

//	config	환경 정보 구조체
type config struct {
	//	최대 다운로드 시도 횟수
	MaxDownRetry int
	//	다운로드 시작 시 타임아웃(단위:초)
	BeginTimeout int
	//	다운로드 중 무응답 타임아웃(단위:초)
	DownTimeout int
	//	온체인 계정 아이디
	AccountID string
	//	온체인 계정 비밀키 비밀번호
	Password string
	//	구매 아이디
	PurchaseID string
	//	온체인 API IP 주소
	OnchainIP string
	//	온체인 API 통신 포트
	OnchainPort int
	//	I/F 로컬 포트
	ListenPort int
	//	다운로드 유무
	SaveContent bool
	//	DBMS 정보
	DBMS dbms
}

//	Default	환경 정보 설정 초기화
func (c *config) Default() {
	c.MaxDownRetry = 3
	c.BeginTimeout = 30
	c.DownTimeout = 60
	c.PurchaseID = "0"
	c.OnchainIP = "127.0.0.1"
	c.OnchainPort = 55442
	c.ListenPort = 8085
	c.SaveContent = true
}

//	Check	환경 정보 유효성 검사
func (c *config) Check() bool {
	if "" == c.OnchainIP ||
		0 >= c.OnchainPort ||
		"" == c.PurchaseID ||
		0 >= c.ListenPort ||
		"" == c.DBMS.Host ||
		"" == c.DBMS.Account ||
		"" == c.DBMS.Password ||
		"" == c.DBMS.Schema {
		return false
	}
	return true
}

//	환경 정보 전역 객체
var gConfig config

//	content	콘텐츠 정보 구조체
type content struct {
	//	일련번호
	serial int64
	//	등록 일시
	reg time.Time
	//	통보 일시
	notify time.Time
	//	복합 콘텐츠 아이디
	ccid string
	//	복합 콘텐츠 버전
	version string
	//	복합 콘텐츠 카테고리1
	category1 string
	//	복합 콘텐츠 카테고리2
	category2 string
	//	등록자 온체인 아이디
	accountID string
	//	등록 상태
	state int
	//	현재 또는 마지막 로그 일련번호
	log int64
	//	다운로드 시도 횟수
	downTry int
	//	모드(추가/수정/삭제)
	mode string
}

//	downNotify	다운로드 정보 구조체
type downNotify struct {
	//	복합콘텐츠 CCID
	CCID string `json:"ccid"`
	//	복합콘텐츠 버전
	Version string `json:"version"`
	//	다운로드 유무
	Flag bool `json:"tflag"`
}

//	existsProcess	프로세스 실행 유무 확인
func existsProcess(appName string) (bool, error) {
	// 현재 실행 중인 프로세스를 열거하고 IPFS가 실행 중이면 종료한다.
	proc, err := ps.Processes()
	if nil != err {
		return false, fmt.Errorf("프로세스 검색 실패: %s", err)
	}
	for _, p := range proc {
		if p.Executable() == appName {
			return true, nil
		}
	}
	return false, nil
}

//	killAllIPFS	실행 중인 모든 IPFS 모두 종료
func killAllIPFS() error {
	// 현재 실행 중인 프로세스를 열거하고 IPFS가 실행 중이면 종료한다.
	proc, err := ps.Processes()
	if nil != err {
		return fmt.Errorf("IPFS 검색 실패: %s", err)
	}
	for _, p := range proc {
		if "ipfs.exe" == p.Executable() {
			ipfs, err := os.FindProcess(p.Pid())
			if nil != err {
				return fmt.Errorf("IPFS 검색 실패: %s", err)
			}
			if err = ipfs.Kill(); nil != err {
				return fmt.Errorf("IPFS 종료 실패: %s", err)
			}
		}
	}
	user, err := user.Current()
	separator := string(os.PathSeparator)
	apiPath := user.HomeDir + separator + ".ipfs" + separator + "api"
	if _, err = os.Stat(apiPath); nil == err {
		if err = os.Remove(apiPath); nil != err {
			return fmt.Errorf("`$IPFS_PATH/api` 삭제 실패: %s", err)
		}
	}
	return nil
}

const (
	Error int = 1 + iota
	Debug
	Warning
	Info
)

//	logOut	로그 콘솔 출력 및 파일 기록
func logOut(logType int, msg string) {
	prefix := ""
	switch logType {
	case Error:
		prefix = "[Error]"
	case Warning:
		prefix = "[Warning]"
	case Debug:
		prefix = "[Debug]"
	case Info:
		prefix = "[Info]"
	}
	if Debug != logType || true == gDebugMode {
		log.Println(prefix, msg)
	}

	now := time.Now().Add(time.Hour * time.Duration(9))
	//	첫 로그이거나 날짜가 바뀌면 로그 파일을 생성한다.
	if nil == gFile || gLogDate.Day() != now.Day() {
		gLogDate = now
		file, err := os.OpenFile(fmt.Sprintf("storagenode_%04d%02d%02d.log", now.Year(), int(now.Month()), now.Day()), os.O_CREATE|os.O_WRONLY|os.O_APPEND, 0666)
		if nil != err {
			panic(err)
		}
		if nil == gLogger {
			gLogger = log.New(file, "", 0 /*log.Ldate|log.Ltime*/)
		} else {
			gFile.Close()
			gLogger.SetOutput(file)
		}
		gFile = file
	}
	gLogger.Println(fmt.Sprintf("[%02d:%02d:%02d]", now.Hour(), now.Minute(), now.Second()), prefix, msg)
}

func main() {
	//	종료 시 열려진 로그 파일을 닫는다.
	defer func() {
		if nil != gFile {
			gFile.Close()
			gFile = nil
		}
	}()

	//	환경 정보를 읽고 없으면 기본 값을 저장한다.
	bin, err := ioutil.ReadFile(gConfigFile)
	if nil == err {
		err = json.Unmarshal(bin, &gConfig)
		if nil != err {
			logOut(Error, fmt.Sprintf("환경 정보 로딩 실패: %s", err))
			return
		}
	} else {
		gConfig.Default()
		jsonConfig, _ := json.Marshal(gConfig)
		err = ioutil.WriteFile(gConfigFile, jsonConfig, os.ModePerm)
		if nil == err {
			logOut(Info, "기본 환경 정보 저장")
		} else {
			logOut(Error, fmt.Sprintf("환경 정보 저장 실패: %s", err))
			return
		}
	}

	if 1 < len(os.Args) {
		var out string
		switch os.Args[1] {
		case "version":
			stdout := newOutput()
			cmd := exec.Command("ipfs", "version")
			cmd.Stdout = stdout
			if err = cmd.Start(); nil == err {
				err = cmd.Wait()
			}
			if nil == err {
				for _, line := range stdout.Lines() {
					out += line + "\n"
				}
			}
			out += "StorageNode version: 0.5-dev"
		case "config":
			if 3 > len(os.Args) {
				out = "Error: enter the subcommand"
			} else {
				switch os.Args[2] {
				case "show":
					jsonConfig, _ := json.MarshalIndent(gConfig, "", "  ")
					fmt.Println(string(jsonConfig))
					return
				case "edit":
					if "windows" == runtime.GOOS {
						cmd := exec.Command("explorer", gConfigFile)
						_ = cmd.Run()
					} else if "linux" == runtime.GOOS {
						cmd := exec.Command("/bin/sh", "-c", "sudo nano "+gConfigFile)
						_ = cmd.Run()
					}
					return
				case "update":
					resp, err := http.Get("http://127.0.0.1:" + strconv.Itoa(gConfig.ListenPort) + "/mediablockchain/config/update")
					if nil == err {
						resp.Body.Close()
					} else {
						logOut(Warning, "환경 정보 적용 실패: "+err.Error())
					}
				default:
					out = `Error: Unknown Command "` + os.Args[2] + `"`
				}
			}
		case "--help":
			out = "USAGE\nStorageNode - IPFS-based MediaBlockchain Content Distribution Node.\n"
			out += "\nSYNOPSIS\n"
			out += "storagenode [--debug=<debug>] [--help=<help>] [command] ...\n"
			out += "\nOPTIONS\n\n"
			out += "  --debug\tbool\t- Operate in debug mode.\n"
			out += "  --help\tbool\t- Show the full command help text.\n"
			out += "\nSUBCOMMANDS\n"
			out += "  TOOL COMMANDS\n"
			out += "    config\tManage configuration\n"
			out += "    version\tShow ipfs & storagenode version information"
		case "--debug":
			gDebugMode = true
			goto DEBUG_RUN
		default:
			out = `Error: Unknown Command "` + os.Args[1] + `"`
		}
		if "" != out {
			fmt.Println(out)
		}
		return
	}

DEBUG_RUN:
	mWorking := make(map[int64]*content)

	logOut(Info, "스토리지 노드 실행")

	if false == gConfig.Check() {
		logOut(Error, "유효하지 않은 환경 정보")
		return
	}

	//	온체인 리스너 실행 여부를 검사한다.
	exists, err := existsProcess("listener.exe")
	if nil == err && false == exists {
		logOut(Error, "온체인 리스너 실행 안됨")
		return
	}

	//	온체인 체인링크 실행 여부를 검사한다.
	if exists, err = existsProcess("mkOnApi.exe"); nil == err && false == exists {
		logOut(Error, "온체인 체인링크 실행 안됨")
		return
	}

	//	스토리지 노드 실행 시 모든 IPFS를 종료한다.
	if err = killAllIPFS(); nil != err {
		logOut(Error, err.Error())
		return
	}

	//	스토리지 노드 종료 시 모든 IPFS를 종료한다.
	//	강제 및 비정상적인 종료 시 호출되지 않는다.
	defer func() {
		err := killAllIPFS()
		if nil != err {
			logOut(Error, err.Error())
		}
	}()

	//	데이터베이스를 오픈한다.
	//	root:tom11223344@tcp(127.0.0.1:3306)/storage?parseTime=true
	db, err := sql.Open("mysql", fmt.Sprintf("%s:%s@tcp(%s)/%s?parseTime=true", gConfig.DBMS.Account, gConfig.DBMS.Password, gConfig.DBMS.Host, gConfig.DBMS.Schema))
	if nil != err {
		logOut(Error, fmt.Sprintf("데이터베이스 접속 실패: %s", err))
		return
	}
	defer db.Close()

	//	미 처리 콘텐츠 등록 정보를 읽는다.
	if gConfig.SaveContent {
		contents, err := db.Query(`SELECT * FROM tb_contents WHERE state IN (?,?) ORDER BY serial ASC`, 0 /*대기*/, 1 /*다운로드중*/)
		if nil != err {
			logOut(Error, fmt.Sprintf("콘텐츠 이력 정보 조회 실패: %s", err))
			return
		}
		defer contents.Close()
		for contents.Next() {
			var c content
			if err := contents.Scan(&c.serial, &c.reg, &c.notify, &c.ccid, &c.version, &c.category1, &c.category2, &c.accountID, &c.state); nil != err {
				logOut(Error, fmt.Sprintf("콘텐츠 이력 정보 조회 실패: %s", err))
				return
			}
			logOut(Info, fmt.Sprintf("미 등록 콘텐츠: [%d] %s / %s", c.serial, c.ccid, c.version))
			if 1 == c.state {
				err = db.QueryRow("SELECT COUNT(*) FROM tb_logs WHERE contents=? AND event=?", c.serial, 0x21 /*다운로드실패*/).Scan(&c.downTry)
				if nil != err {
					logOut(Error, fmt.Sprintf("콘텐츠 이력 로그 정보 조회 실패: %s", err))
					return
				}
			}
			mWorking[c.serial] = &c
		}
	}

	//	[201110-jih] HTTP 서버, 대기 객체 추가
	server := &http.Server{Addr: ":" + strconv.Itoa(gConfig.ListenPort)}
	wgServer := &sync.WaitGroup{}

	runDaemon := make(chan bool, 1)

	//	스레드로 IPFS 데몬을 실행하고 그 상태를 감시한다.
	go func() {
		prefix := "[Daemon] "
		for {
			mConfig := make(map[string]string)
			mConfig["AccountID"] = gConfig.AccountID
			mConfig["OnchainIP"] = gConfig.OnchainIP
			mConfig["OnchainPort"] = strconv.Itoa(gConfig.OnchainPort)
			mConfig["Password"] = gConfig.Password

			stdout := newOutput()
			stderr := newOutput()
			cmd := exec.Command("ipfs", "daemon")
			cmd.Stdout = stdout
			cmd.Stderr = stderr

			//	IPFS 데몬 실행 전 환경 정보를 설정한다.
			for k, v := range mConfig {
				cmd := exec.Command("ipfs", "config", "MediaBlockchain."+k, v)
				if err := cmd.Run(); nil != err {
					runDaemon <- false
					logOut(Error, fmt.Sprintf("IPFS %s 설정 실패: %s", k, err))
					goto IPFS_FAILED
				}
			}

			//	IPFS 데몬을 실행한다.
			if err = cmd.Start(); nil != err {
				//	데몬 실행 실패 시 채널 통보
				runDaemon <- false
				logOut(Error, fmt.Sprintf("IPFS Daemon 실행 실패: %s", err))
				goto IPFS_FAILED
			}
			go func() {
				logOut(Info, prefix+"IPFS 실행")
				for {
					for _, line := range stdout.Lines() {
						logOut(Debug, prefix+" "+line)
						//	IPDS 데몬이 정상적으로 실행됐는지 검사한다.
						if "Daemon is ready" == line {
							logOut(Info, prefix+"IPFS 준비됨")
							//	데몬 준비되면 채널 통보
							runDaemon <- true
						}
					}
					for _, line := range stderr.Lines() {
						logOut(Debug, prefix+line)
					}
					//	1초 대기 후 다음 작업을 처리한다.
					time.Sleep(time.Second)
				}
			}()
			cmd.Wait()
			logOut(Error, prefix+"IPFS 종료")
		IPFS_FAILED: //	IPFS 데몬 실행 실패
			//	[201110-jih] 데몬 종료 시 스토리지 노드 종료
			if err := server.Shutdown(context.TODO()); nil != err {
				panic(err)
			}
		}
	}()

	//	IPFS 클라이언트 CLI를 실행한다.
	runIPFSClient := func(name string, timeout [2]int, arg ...string) ([]string, error) {
		var console []string
		stdout := newOutput()
		stderr := newOutput()
		cmd := exec.Command(name, arg...)
		cmd.Stdout = stdout
		cmd.Stderr = stderr
		//	응용 프로그램을 실행한다.
		err := cmd.Start()
		if nil != err {
			return nil, err
		}
		//	IPFS를 실행 후 비동기로 출력 메시지를 읽는다.
		go func() {
			var end time.Time
			//	시작 시 무응답 타임아웃을 설정한다.
			if 0 < timeout[0] {
				end = time.Now().Add(time.Duration(timeout[0]) * time.Second)
			}
			for {
				prevLen := len(console)
				for _, line := range stdout.Lines() {
					logOut(Debug, line)
					console = append(console, line)
				}
				for _, line := range stderr.Lines() {
					logOut(Debug, line)
					console = append(console, line)
				}
				if nil != cmd.ProcessState && true == cmd.ProcessState.Exited() {
					break
				}
				if 0 < timeout[1] && len(console) > prevLen {
					//	다운로드 중 무응답 타임아웃을 설정한다.
					end = time.Now().Add(time.Duration(timeout[1]) * time.Second)
				}
				//	0.5초 대기 후 타임아웃을 체크한다.
				time.Sleep(500 * time.Microsecond)
				if false == end.IsZero() && time.Now().After(end) {
					//	실행 중인 IPFS를 강제 종료한다.
					if err = cmd.Process.Kill(); nil != err {
						logOut(Error, fmt.Sprintf("콘텐츠 다운로드 중지 실패: %v", err))
					} else {
						logOut(Warning, "콘텐츠 다운로드 중지")
					}
					break
				}
			}
		}()
		//	응용 프로그램이 종료할 때까지 대기한다.
		if err = cmd.Wait(); nil != err {
			if "exit status 1" == err.Error() {
				err = nil
			}
		}
		return console, err
	}

	//	IPFS 클라이언트를 실행하고 그 상태를 감시한다.
	go func() {
		var logMsg string
		var console, arg []string
		if false == <-runDaemon {
			return
		}
		prefix := "[Client] "
		find := "Saving file(s) to "
		for {
			for key, c := range mWorking {
				var fi os.FileInfo
				if "d" == c.mode {
					//	콘텐츠 고정 상태를 조회한다.
					arg = append(arg, "pin")
					arg = append(arg, "ls")
					arg = append(arg, c.version)
					logMsg = prefix + "콘텐츠 고정 조회 "
					logOut(Info, fmt.Sprintf("%s실행: %v", logMsg, arg))
					console, err = runIPFSClient("ipfs", [2]int{}, arg...)
					arg = nil
					if nil == err {
						//	콘솔 에러 메시지를 처리한다.
						for _, line := range console {
							if strings.Contains(line, "Error:") {
								//	Error: path 'QmZjUSkUFRzK9KSdyj6bcsQBef5PRaGcyjgWrUCSAYULDr' is not pinned
								err = errors.New(strings.TrimSpace(line[6:]))
								break
							} else {
								slices := strings.Split(line, " ")
								if length := len(slices); 0 < length {
									if strings.Contains(slices[length-1], "Qm") {
										//	QmZjUSkUFRzK9KSdyj6bcsQBef5PRaGcyjgWrUCSAYULDr indirect through QmaVVG95HQdu43cSgFWo3vSxR12g77z5MwZKzJAfMpbELo
										logOut(Info, fmt.Sprintf("고정 해제 버전 변경: %s -> %s", c.version, slices[length-1]))
										c.version = slices[length-1]
										break
									}
								}
							}
						}
						if nil == err {
							//	콘텐츠 고정을 해제한다.
							arg = append(arg, "pin")
							arg = append(arg, "rm")
							arg = append(arg, c.version)
							logMsg = prefix + "콘텐츠 고정 해제 "
							logOut(Info, fmt.Sprintf("%s실행: %v", logMsg, arg))
							console, err = runIPFSClient("ipfs", [2]int{}, arg...)
							arg = nil
							if nil == err {
								//	콘솔 에러 메시지를 처리한다.
								for _, line := range console {
									if strings.Contains(line, "Error:") {
										err = errors.New(strings.TrimSpace(line[6:]))
										break
									} else if strings.Contains(line, "unpinned") {
										//	unpinned QmaVVG95HQdu43cSgFWo3vSxR12g77z5MwZKzJAfMpbELo
										logOut(Info, logMsg+"성공")
										break
									}
								}
							}
						}
					}
					if nil != err {
						logOut(Warning, fmt.Sprintf("%s실패: %s", logMsg, err))
					}
					//	현재 작업을 삭제한다.
					delete(mWorking, key)
					continue
				}

				desc := ""
				event := eventDownBegin

				//	다운로드 최초 시작 시 그 상태를 변경한다.
				if 0 /*대기*/ == c.state {
					c.state = 1 /*다운로드중*/
					_, err := db.Exec("UPDATE tb_contents SET state=? WHERE serial=?", c.state, c.serial)
					if nil != err {
						logOut(Error, err.Error())
					}
				}

				//	다운로드 시작 전 로그를 추가한다.
				stmt, err := db.Exec("INSERT INTO tb_logs(event,contents) VALUES(?,?)", event, c.serial)
				if nil != err {
					logOut(Error, err.Error())
				}
				//	추가한 로그의 일련번호를 조회한다.
				c.log, err = stmt.LastInsertId()
				if nil != err {
					logOut(Error, err.Error())
				}

				begin := time.Now()
				//	콘텐츠를 다운로드 한다.
				arg = append(arg, "ccget")
				arg = append(arg, "/ccfs/"+c.ccid+"/"+c.version)
				arg = append(arg, "-p")
				arg = append(arg, gConfig.PurchaseID)
				logMsg = prefix + "콘텐츠 다운로드 "
				logOut(Info, fmt.Sprintf("%s실행: %v", logMsg, arg))
				console, err = runIPFSClient("ipfs", [2]int{gConfig.BeginTimeout, gConfig.DownTimeout}, arg...)
				arg = nil
				if nil == err {
					//	콘솔 에러 메시지를 처리한다.
					for _, line := range console {
						if strings.Contains(line, "Error:") {
							event = eventDownFail
							desc = strings.TrimSpace(line[6:])
							logOut(Warning, fmt.Sprintf("%s실패: %s", logMsg, desc))
							break
						}
					}
				} else if strings.Contains(err.Error(), "killed") {
					logOut(Warning, logMsg+"타임아웃")
					desc = "다운로드 시간 초과"
					event = eventDownCancel
				} else {
					logOut(Warning, fmt.Sprintf("%s실패: %s", logMsg, err))
					event = eventDownFail
					desc = err.Error()
				}

				//	다운로드 된 임시 파일을 삭제한다.
				for _, line := range console {
					if strings.Contains(line, find) {
						path := line[len(find):]
						if fi, err = os.Stat(path); nil == err {
							temp := "임시 파일 삭제"
							if err = os.RemoveAll(path); nil == err {
								logOut(Info, fmt.Sprintf("%s%s: %s", logMsg, temp, path))
							} else {
								logOut(Warning, fmt.Sprintf("%s%s 실패: %s(%s)", logMsg, temp, err.Error(), path))
							}
						}
						//	다운로드 완료 여부를 검사한다.
					} else if eventDownBegin == event && strings.Contains(line, "100.00%") {
						logOut(Info, fmt.Sprintf("%s성공: %dbyte(%v)", logMsg, fi.Size(), time.Now().Sub(begin)))
						event = eventDownSuccess
						break
					}
				}

				//	다운로드 종료 후 로그를 갱신한다.
				if _, err = db.Exec("UPDATE tb_logs SET event=?,end=NOW(),description=? WHERE serial=?", event, desc, c.log); nil != err {
					logOut(Error, err.Error())
				}

				c.downTry++
				if eventDownSuccess == event || gConfig.MaxDownRetry == c.downTry {
					if eventDownSuccess == event {
						//	다운로드 받은 콘텐츠를 고정한다.
						arg = append(arg, "pin")
						arg = append(arg, "add")
						arg = append(arg, c.version)
						logMsg = prefix + "콘텐츠 고정 "
						logOut(Info, fmt.Sprintf("%s실행: %v", logMsg, arg))
						console, err = runIPFSClient("ipfs", [2]int{}, arg...)
						arg = nil
						if nil != err {
							logOut(Warning, fmt.Sprintf("%s실패: %s", logMsg, err))
						} else {
							//	콘솔 에러 메시지를 처리한다.
							for _, line := range console {
								if strings.Contains(line, "Error:") {
									logOut(Warning, fmt.Sprintf("%s실패: %s", logMsg, strings.TrimSpace(line[6:])))
									break
								} else if strings.Contains(line, "pinned") {
									//	pinned QmZjUSkUFRzK9KSdyj6bcsQBef5PRaGcyjgWrUCSAYULDr recursively
									logOut(Info, logMsg+"성공")
								}
							}
						}
						//	온체인 리스너에 다운로드 완료를 통보한다.
						data, _ := json.Marshal(downNotify{CCID: c.ccid, Version: c.version, Flag: true})
						URL := "http://127.0.0.1:55441/product/setStorageNode"
						logOut(Info, fmt.Sprintf("%s\n%s", URL, string(data)))
						resp, statusCode, err := httpPost(URL, "application/json", string(data))
						if nil != err {
							logOut(Warning, fmt.Sprintf("다운로드 통보 실패: %s", err))
						} else {
							//	[200902-jih] 콘텐츠 등록 통보 확인을 위한 로그 추가
							logOut(Info, fmt.Sprintf("[%d] '%s'", statusCode, resp))
						}
						c.state = 2 /*완료*/
					} else {
						c.state = -1 /*실패*/
					}

					//	콘텐츠 등록 상태를 갱신한다.
					if _, err := db.Exec("UPDATE tb_contents SET state=? WHERE serial=?", c.state, c.serial); nil != err {
						logOut(Error, err.Error())
					}
					//	현재 작업을 삭제한다.
					delete(mWorking, key)
				}
			}
			console = nil
			//	1초 대기 후 다음 작업을 처리한다.
			time.Sleep(time.Second)
		}
	}()

	//	환경 정보를 갱신한다.
	http.HandleFunc("/mediablockchain/config/update", func(w http.ResponseWriter, r *http.Request) {
		if err := r.ParseForm(); nil != err {
			return
		}
		var new config
		bin, err := ioutil.ReadFile(gConfigFile)
		if nil == err {
			err = json.Unmarshal(bin, &new)
		}
		if nil == err {
			gConfig = new
			logOut(Info, "환경 정보 갱신")
		} else {
			logOut(Error, "환경 정보 로딩 실패: "+err.Error())
		}
	})

	//	신규 콘텐츠 등록 정보를 DB에 추가한다.
	http.HandleFunc("/mediablockchain/content/register", func(w http.ResponseWriter, r *http.Request) {
		if err := r.ParseForm(); nil != err {
			return
		}

		var c content
		var result int
		var desc string

		logOut(Debug, fmt.Sprintf("%v %v", r.URL.Path, r.Form))

		if reg := r.FormValue("reg"); 19 /*YYYY-MM-DDThh:mm:ss*/ != len(reg) {
			result = -1
			desc = "인자(reg)가 유효하지 않습니다"
		} else if "true" == r.FormValue("flag") {
			year, _ := strconv.Atoi(reg[:4])
			month, _ := strconv.Atoi(reg[5:7])
			day, _ := strconv.Atoi(reg[8:10])
			hour, _ := strconv.Atoi(reg[11:13])
			minute, _ := strconv.Atoi(reg[14:16])
			second, _ := strconv.Atoi(reg[17:19])

			c.reg = time.Date(year, time.Month(month), day, hour, minute, second, 0, time.Local)
			c.notify = time.Now()
			c.ccid = r.FormValue("ccid")
			c.version = r.FormValue("version")
			c.category1 = r.FormValue("category1")
			c.category2 = r.FormValue("category2")
			c.accountID = r.FormValue("accountid")
			c.mode = r.FormValue("mode")

			if "" == c.ccid {
				result = -1
				desc = "ccid"
			} else if "" == c.version {
				result = -1
				desc = "version"
			} else if "" == c.category1 {
				result = -1
				desc = "category1"
			} else if "" == c.accountID {
				result = -1
				desc = "accountID"
			}
			if 0 != result {
				desc = "인자(" + desc + ")가 비어있습니다"
			} else {
				var stmt sql.Result
				now := c.notify.String()
				switch c.mode {
				case "i":
					logOut(Info, fmt.Sprintf("[%s] 신규 콘텐츠 등록\n  - 등록일시: %s\n  - CCID: %s\n  - Version: %s\n  - Category1: %s\n  - Category2: %s\n  - AccountID: %s", now[:strings.IndexAny(now, ".")], c.reg, c.ccid, c.version, c.category1, c.category2, c.accountID))
				case "m":
					logOut(Info, fmt.Sprintf("[%s] 콘텐츠 수정\n  - 수정일시: %s\n  - CCID: %s\n  - Version: %s\n  - Category1: %s\n  - Category2: %s\n  - AccountID: %s", now[:strings.IndexAny(now, ".")], c.reg, c.ccid, c.version, c.category1, c.category2, c.accountID))
				case "d":
					logOut(Info, fmt.Sprintf("[%s] 콘텐츠 삭제\n  - 삭제일시: %s\n  - CCID: %s\n  - Version: %s\n  - Category1: %s\n  - Category2: %s\n  - AccountID: %s", now[:strings.IndexAny(now, ".")], c.reg, c.ccid, c.version, c.category1, c.category2, c.accountID))
				default:
				}
				if "d" == c.mode {
					var exists bool
					//	등록된 콘텐츠인지 검색한다.
					err = db.QueryRow("SELECT EXISTS(SELECT * FROM tb_contents WHERE ccid=? AND version=?)", c.ccid, c.version).Scan(&exists)
					if nil != err {
						result = -2
						desc = fmt.Sprintf("콘텐츠 조회 실패: %s", err.Error())
					} else if false == exists {
						logOut(Info, fmt.Sprintf("미 등록 콘텐츠: %s / %s", c.ccid, c.version))
					} else {
						//	작업 목록에 추가한다.
						mWorking[c.serial] = &c
					}
					//	콘텐츠 등록 정보를 DB에 추가한다.
				} else if stmt, err = db.Exec("INSERT INTO tb_contents(register,notify,ccid,version,category1,category2,accountID,state) VALUES(?,NOW(),?,?,?,?,?,?)", fmt.Sprintf("%04d-%02d-%02d %02d:%02d:%02d", c.reg.Year(), c.reg.Month(), c.reg.Day(), c.reg.Hour(), c.reg.Minute(), c.reg.Second()), c.ccid, c.version, c.category1, c.category2, c.accountID, 0 /*대기*/); nil == err {
					c.serial, err = stmt.LastInsertId()
					if nil == err && gConfig.SaveContent {
						//	작업 목록에 추가한다.
						mWorking[c.serial] = &c
					}
				}
				if nil != err {
					result = -2
					desc = fmt.Sprintf("콘텐츠 이력 추가 실패: %s", err.Error())
				}
			}
		}
		if 0 != result {
			logType := Warning
			if -2 == result {
				logType = Error
			}
			logOut(logType, fmt.Sprintf("[%d] %s", result, desc))
		}
		sResp := fmt.Sprintf(`{"result":%d,"desc":"%s"}`, result, desc)
		w.Write([]byte(sResp))
	})

	wgServer.Add(1)
	go func(wg *sync.WaitGroup) {
		defer wg.Done()
		if err := server.ListenAndServe(); http.ErrServerClosed != err {
			log.Fatal(err)
		}
	}(wgServer)

	wgServer.Wait()
	logOut(Warning, "스토리지 노드 종료")
}
