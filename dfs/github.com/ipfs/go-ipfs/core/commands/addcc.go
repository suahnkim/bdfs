package commands

import (
	"bytes"
	"encoding/json"
	"errors"
	"fmt"
	cmds "gx/ipfs/QmQtQrtNioesAWtrx8csBvfY37gTe94d6wQ3VikZUjxD39/go-ipfs-cmds"

	//"gx/ipfs/QmR8BauakNcBa3RbE4nbQu76PDiJgoQgz8AJdhJuiU4TAw/go-cid"
	cid "gx/ipfs/QmTbxNB1NwDesLmKTscr4udL2tVP7MaxvXnD1D9yX7g3PN/go-cid"

	//cmds "gx/ipfs/QmQkW9fnCsg9SLHdViiAh6qfBppodsPZVpU92dZLqYtEfs/go-ipfs-cmds"
	cmdkit "gx/ipfs/Qmde5VP1qUkyQXKCfmEUA7bP64V2HAptbJ7phuPp7jXWwg/go-ipfs-cmdkit"
	mh "gx/ipfs/QmerPMzPk1mJVowm8KgmoknWa4yCYvvugMPsgWmDNUvDLW/go-multihash"
	"io"
	"io/ioutil"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"strconv"
	"strings"
	"sync"
	"time"

	"github.com/ipfs/go-ipfs/core/commands/cmdenv"
	"github.com/ipfs/go-ipfs/thirdparty/jodaTime-master"
)

// ErrDepthLimitExceeded indicates that the max depth has been exceeded.
//var ErrDepthLimitExceeded = fmt.Errorf("depth limit exceeded")

const (
	ccidOptionName          = "ccid"
	usingCCISOptionName     = "using_ccis"
	ccisUrlOptionName       = "ccis_url"
	prvKeyPathCIDOptionName = "prv_path"
	prvPWCIDOptionName      = "prv_pw"
)

var CCAddCmd = &cmds.Command{
	Helptext: cmdkit.HelpText{
		Tagline:          "Add a ComplexContent to ipfs.",
		ShortDescription: `CCADD1 .`,
		LongDescription:  ` CCADD2 `,
	},

	Arguments: []cmdkit.Argument{
		cmdkit.StringArg("manifest_json", true, true, "The manifest file path").EnableStdin(),
		cmdkit.StringArg("result_json", false, false, "The result file path").EnableStdin(),
	},
	Options: []cmdkit.Option{
		cmds.OptionRecursivePath, // a builtin option that allows recursive paths (-r, --recursive)
		cmds.OptionDerefArgs,     // a builtin option that resolves passed in filesystem links (--dereference-args)
		cmdkit.StringOption(ccidOptionName, "CCID Option"),
		cmdkit.StringOption(usingCCISOptionName, "using ccis option"),
		cmdkit.StringOption(prvKeyPathCIDOptionName, "private key path option"),
		cmdkit.StringOption(prvPWCIDOptionName, "private key password option"),
		cmdkit.StringOption(ccisUrlOptionName, "ccis url"),
	},

	PreRun: func(req *cmds.Request, env cmds.Environment) error {
		//fmt.Println("CCADD PreRun")
		/*
			1. descriptor 파일 있는지 확인
			2. descriptor 파일 파싱
			3. descriptor 상의 파일이 존재하는지 확인
		*/

		descriptorPath := req.Arguments[0]
		_, err := os.Stat(descriptorPath)

		if err != nil {
			if os.IsNotExist(err) {
				log.Debugf("not exists descriptor[", descriptorPath, "]")
				return err
			}
			fmt.Println("file read err[", descriptorPath, "]")
		}
		log.Debugf("1. descriptor file exists")

		jsonFile, err := os.Open(descriptorPath)
		if err != nil {
			log.Debugf("descriptor open error [", descriptorPath, "]")
			return err
		}

		byteValue, err := ioutil.ReadAll(jsonFile)
		if err != nil {
			log.Debugf("descriptor read error [", descriptorPath, "]")
			return err
		}

		defer jsonFile.Close()
		var descriptor Descriptor
		err = json.Unmarshal(byteValue, &descriptor)
		if err != nil {
			log.Debugf("descriptor json parsing error")
			return err
		}
		log.Debugf("2. descriptor paring OK!")

		var fileMap map[string]string
		fileMap = make(map[string]string)

		//파일이 존재하는지 검사.
		contentsFileArr := descriptor.Contents
		for i := 0; i < len(contentsFileArr); i++ {
			_, err := os.Stat(contentsFileArr[i])
			if err != nil {
				log.Debugf("not found contents file[", contentsFileArr[i], "]")
				return err
			}

			_, exists := fileMap[contentsFileArr[i]]
			if exists {
				return errors.New("duplicate content path")
			}
			fileMap[contentsFileArr[i]] = contentsFileArr[i]
		}

		basicMetaArr := descriptor.BasicMeta
		for i := 0; i < len(basicMetaArr); i++ {
			basicMeta := basicMetaArr[i]

			//올바른 컨텐츠 파일을 참조하는지 확인
			targetFileArr := basicMeta.Target
			for j := 0; j < len(targetFileArr); j++ {

				_, exists := fileMap[targetFileArr[j]]
				if !exists {
					return errors.New("invalid target content path")
				}
			}

			//아트웍 파일이 있는지 확인
			artworkArr := basicMeta.Metadata.Artwork
			for j := 0; j < len(artworkArr); j++ {
				_, err := os.Stat(artworkArr[j].FileName)
				if err != nil {
					log.Debugf("not found artwork file[", artworkArr[j].FileName, "]")
					return err
				}
			}
		}
		log.Debugf("3. check content file exists.  ...  OK!")

		//CCIS 연동 인터페이스 파라메터 검증
		usingCCIS, _ := req.Options[usingCCISOptionName].(string)
		prvKeyPath, _ := req.Options[prvKeyPathCIDOptionName].(string)
		prvPW, _ := req.Options[prvPWCIDOptionName].(string)
		//log.Debugf(">>>> USING_CCID[%s], KEY_PATH[%s], KEYPW[%s]", usingCCIS, prvKeyPath, prvPW)
		if strings.TrimSpace(usingCCIS) == "yes" {
			_, err := os.Stat(prvKeyPath)
			if err != nil {
				log.Debugf("not found private key file")
				return err
			}

			if strings.TrimSpace(prvPW) == "" {
				return errors.New("private key password is null")
			}
		}

		return nil
	},

	Run: func(req *cmds.Request, res cmds.ResponseEmitter, env cmds.Environment) error {
		/*
			1. descriptor 파일 파싱
			2. 탬퍼러리 폴더를 생성후 파일들을 복사하여 복합컨텐츠 형식으로 만듬
		*/

		ccRootDir := ".ipfs_cc_" + strconv.FormatInt(time.Now().UnixNano(), 10)

		baseDir := ""
		if strings.HasSuffix(os.TempDir(), string(os.PathSeparator)) {
			baseDir = os.TempDir() + ccRootDir
			log.Debugf("   concate tempdir")
		} else {
			baseDir = os.TempDir() + string(os.PathSeparator) + ccRootDir
			log.Debugf("   concate tempdir and separator")
		}

		//os.MkdirAll(baseDir, os.ModePerm)
		os.MkdirAll(baseDir+"/basicMeta", os.ModePerm)
		os.MkdirAll(baseDir+"/extMeta", os.ModePerm)
		os.MkdirAll(baseDir+"/contents", os.ModePerm)
		os.MkdirAll(baseDir+"/derivedContents", os.ModePerm)
		defer os.RemoveAll(baseDir)

		log.Debugf("   baseDir[%s]", baseDir)
		log.Debugf("   os.TempDir()[%s][%s]", os.TempDir(), string(os.PathSeparator))

		descriptorPath := req.Arguments[0]
		jsonFile, _ := os.Open(descriptorPath)
		byteValue, _ := ioutil.ReadAll(jsonFile)
		defer jsonFile.Close()
		var descriptor Descriptor
		json.Unmarshal(byteValue, &descriptor)

		{
			//컨텐츠 파일 복사
			contentsFileArr := descriptor.Contents
			for i := 0; i < len(contentsFileArr); i++ {
				_, fileName := filepath.Split(contentsFileArr[i])
				input, err := ioutil.ReadFile(contentsFileArr[i])
				if err != nil {
					return err
				}
				err = ioutil.WriteFile(baseDir+"/contents/"+fileName, input, os.ModePerm)
				if err != nil {
					return err
				}
			}

			basicMetaArr := descriptor.BasicMeta
			for i := 0; i < len(basicMetaArr); i++ {
				basicMeta := basicMetaArr[i]
				//아트웍 파일 복사
				artworkArr := basicMeta.Metadata.Artwork
				for j := 0; j < len(artworkArr); j++ {
					_, fileName := filepath.Split(artworkArr[j].FileName)
					input, err := ioutil.ReadFile(artworkArr[j].FileName)
					if err != nil {
						return err
					}
					err = ioutil.WriteFile(baseDir+"/basicMeta/"+fileName, input, os.ModePerm)
					if err != nil {
						return err
					}

					fiStat, _ := os.Stat(artworkArr[j].FileName)

					artworkArr[j].FileName = "basicMeta/" + fileName
					artworkArr[j].FileSize = fiStat.Size()
				}

				targetFileArr := basicMeta.Target
				for j := 0; j < len(targetFileArr); j++ {
					_, fileName := filepath.Split(targetFileArr[j])
					targetFileArr[j] = "contents/" + fileName
				}

				basicMeta.ContentType = "video"
				jsonBytes, _ := json.MarshalIndent(basicMeta, "", "    ")
				ioutil.WriteFile(baseDir+"/basicMeta/basicMeta"+strconv.Itoa(i)+".json", jsonBytes, os.ModePerm)
			}
		}
		log.Debugf("4. Copy Complex Content.  ...  OK!")

		//var manifest Manifest
		//Manifest 파일 생성

		manifestFileArr := makeManifest(baseDir)
		addIpfsDir(req, env, baseDir, manifestFileArr)

		return nil
	},

	PostRun: cmds.PostRunMap{
		cmds.CLI: func(res cmds.Response, re cmds.ResponseEmitter) error {
			//fmt.Println("PostRunMap Run")
			return nil
		},
	},
}

func makeManifest(baseDir string) []FileInfo {
	var resultFileArr []FileInfo

	var basicMetaFileArr []FileInfo
	var extMetaFileArr []FileInfo
	var contentsFileArr []FileInfo
	var dContentsFileArr []FileInfo
	filepath.Walk(baseDir, func(path string, info os.FileInfo, err error) error {

		if !info.IsDir() {

			path = strings.Replace(path, "\\", "/", -1)
			cBaseDir := strings.Replace(baseDir, "\\", "/", -1)

			fInfo := FileInfo{}
			fInfo.Path = path[len(cBaseDir)+1:]
			fInfo.Size = info.Size()
			fInfo.Type = predictExt(info.Name())

			//fmt.Printf("path[%s] cBaseDir [%s]\n", path, cBaseDir)

			if strings.HasPrefix(path, cBaseDir+"/basicMeta") {
				basicMetaFileArr = append(basicMetaFileArr, fInfo)
			} else if strings.HasPrefix(path, cBaseDir+"/extMeta") {
				extMetaFileArr = append(extMetaFileArr, fInfo)
			} else if strings.HasPrefix(path, cBaseDir+"/contents") {
				contentsFileArr = append(contentsFileArr, fInfo)
			} else if strings.HasPrefix(path, cBaseDir+"/derivedContents") {
				dContentsFileArr = append(dContentsFileArr, fInfo)
			}

			rInfo := FileInfo{}
			rInfo.Path = path[len(cBaseDir)+1:]
			rInfo.FileSize = info.Size()
			resultFileArr = append(resultFileArr, rInfo)

			//fmt.Printf("manifest file [%s]\n", rInfo.Path)
		}
		return nil
	})

	var manifest Manifest
	manifest.BasicMeta = basicMetaFileArr
	manifest.ExtendedMeta = extMetaFileArr
	manifest.Contents = contentsFileArr
	manifest.DerivedContents = dContentsFileArr

	//fmt.Printf("set manifest file \n")

	var control ManifestControl
	control.Lastmodify = jodaTime.Format("YYYY-MM-DDTHH:mm:ssZ", time.Now())
	manifest.Control = control

	jsonBytes, _ := json.MarshalIndent(manifest, "", "    ")
	ioutil.WriteFile(baseDir+"/manifest.json", jsonBytes, os.ModePerm)

	//fmt.Printf("manifest file [%s]\n", jsonBytes)

	log.Debugf("5. Make Manifest.json  ...  OK!")
	return resultFileArr
}

func addIpfsDir(req *cmds.Request, env cmds.Environment, baseDir string, resultFileArr []FileInfo) {
	ex, _ := os.Executable()
	//폴더 등록 커멘드 실행
	cmd := exec.Command(filepath.Dir(ex)+string(os.PathSeparator)+"ipfs", "add", "-r", baseDir)
	//var stdout []byte
	var errStdout, errStderr error
	var resultLine []string
	stdoutIn, _ := cmd.StdoutPipe()
	stderrIn, _ := cmd.StderrPipe()
	err := cmd.Start()
	if err != nil {
		log.Fatalf("cmd.Start() failed with '%s'\n", err)
	}

	//resultLine = append(resultLine, "123456789")

	var wg sync.WaitGroup
	wg.Add(1)
	go func() {
		resultLine, errStdout = copyAndCapture(os.Stdout, stdoutIn)
		//copyAndCapture(os.Stdout, stdoutIn)
		wg.Done()
	}()

	_, errStderr = copyAndCapture(os.Stderr, stderrIn)
	//copyAndCapture(os.Stderr, stderrIn)
	wg.Wait()
	err = cmd.Wait()
	if err != nil {
		delErr := os.RemoveAll(baseDir)
		log.Debugf(">>>>>>DELETE RESULT :: [%s]", delErr)
		log.Fatalf("cmd.Run() failed with %s\n", err)
	}
	if errStdout != nil || errStderr != nil {
		delErr := os.RemoveAll(baseDir)
		log.Debugf(">>>>>>DELETE RESULT :: [%s]", delErr)
		log.Fatal("failed to capture stdout or stderr\n")
	}
	//outStr, errStr := string(stdout), string(stderr)
	//fmt.Printf("\nout:\n%s\nerr:\n%s\n", outStr, errStr)

	//Result 파일 생성....
	var fileCidMap map[string]string
	fileCidMap = make(map[string]string)


  var mergedResult string
	for i := 0; i < len(resultLine); i++ {
		log.Debugf("rawLine(%d)[%s]\n", i, resultLine[i])
		if(len(resultLine[i]) > 0) {
				mergedResult = mergedResult + resultLine[i]
		}
	}
	mergedResult = strings.Trim(mergedResult, " ")
	mergedResult = strings.Trim(mergedResult, "\n")
	mergedResult = strings.Trim(mergedResult, "\r")

	log.Debugf("mergedResult[%s]\n", mergedResult)
	resultLine = strings.Split(mergedResult, "\n");


	for i := 0; i < len(resultLine); i++ {
		resultLine[i] = strings.TrimSuffix(resultLine[i], "\n")
		log.Debugf("Line(%d)[%s]\n", i, resultLine[i])
		fields := strings.Fields(resultLine[i])
		fName := string(resultLine[i][53:])

		log.Debugf("		[%s][%s][%s]\n", fields[0], fields[1], fields[2])
		//fileCidMap[fields[2]] = fields[1]
		fileCidMap[fName] = fields[1]

		//fmt.Printf("\n[%s][%s]>>> ", resultLine[i], fName)
		//fmt.Printf("\n[%s] fileCidMap [%s][%s]\n", resultLine[i], fields[1], fName)
	}

	ccStartIdx := 0
	for i := 0; i < len(resultFileArr); i++ {
		filePath := baseDir + "/" + resultFileArr[i].Path

		ccStartIdx = strings.Index(filePath, ".ipfs_cc_")
		filePath = filePath[ccStartIdx:]

		//fmt.Printf("Index [%s][%d]\n", filePath, sIdx)

		fileCid := fileCidMap[filePath]
		//fmt.Printf("Find [%s][%s]\n", filePath, fileCid)
		resultFileArr[i].Cid = fileCid
	}

	ccRootDir := baseDir[ccStartIdx:]
	ccid, _ := req.Options[ccidOptionName].(string)
	//var ccVersion = fileCidMap[baseDir]
	var ccVersion = fileCidMap[ccRootDir]

	var resultFile ResultFile

	var peerId = ""
	config, err := cmdenv.GetConfig(env)
	if err != nil {
		peerId = ""
	} else {
		peerId = config.Identity.PeerID
	}

	if strings.TrimSpace(ccid) == "" {
		//CCID = Base58(0x1220 + Sha256(rootHash + ipfs id + timestamp))

		timestamp := strconv.FormatInt(time.Now().Unix()*1000, 10)
		baseCCid := peerId + ccVersion + timestamp

		h, _ := mh.Sum([]byte(baseCCid), mh.SHA2_256, -1)
		c1 := cid.NewCidV0(h)
		ccid = c1.String()
	}

	resultFile.Files = resultFileArr
	var resultForAddCc ResultForAddCC
	resultForAddCc.Result = "0"
	resultForAddCc.ResultMessage = "OK"
	resultForAddCc.Ccid = ccid
	resultForAddCc.Version = ccVersion
	resultForAddCc.ChunkSize = 262144
	resultFile.Result = resultForAddCc

	//CCIS 연동 인터페이스
	usingCCIS, _ := req.Options[usingCCISOptionName].(string)
	prvKeyPath, _ := req.Options[prvKeyPathCIDOptionName].(string)
	prvPW, _ := req.Options[prvPWCIDOptionName].(string)
	ccisUrl, _ := req.Options[ccisUrlOptionName].(string)
	//log.Debugf("<<<< USING_CCID[%s], KEY_PATH[%s], KEYPW[%s]", usingCCIS, prvKeyPath, prvPW)

	if strings.TrimSpace(usingCCIS) == "yes" {
		var uploadInfo UploadCCInfo
		uploadInfo.Apiv = "1.0"
		uploadInfo.Ccid = ccid
		uploadInfo.Version = ccVersion
		uploadInfo.GasLevel = "average"
		uploadInfo.NodeId = peerId
		uploadInfo.Target = "eth"
		uploadInfo.PrvPath = prvKeyPath
		uploadInfo.Password = prvPW

		if ccisUrl == "" {
			ccisUrl = "http://localhost:8080/ccis/v1/interface.do"
		}

		txId, err := registerCCIS(ccisUrl, uploadInfo)

		var txResult TxResult
		if err == nil {
			txResult.TxHash = txId
		} else {
			txResult.Error = err.Error()
		}
		resultFile.TxResult = txResult
	}

	//descriptorPath := req.Arguments[0]
	//log.Debugf("---------result path[%d]\n", len(req.Arguments))

	jsonBytes, _ := json.MarshalIndent(resultFile, "", "    ")
	if len(req.Arguments) == 2 {
		ioutil.WriteFile(req.Arguments[1], jsonBytes, os.ModePerm)
	} else {
		fmt.Printf("\n%s\n", jsonBytes)
	}
}

func registerCCIS(ccisUrl string, uploadInfo UploadCCInfo) (string, error) {
	var jsonRpcReq JsonRpcReqUploadCC
	jsonRpcReq.Jsonrpc = "2.0"
	jsonRpcReq.Method = "upload_content"
	jsonRpcReq.Params = uploadInfo
	jsonRpcReq.Id = 1
	jsonBytes, _ := json.MarshalIndent(jsonRpcReq, "", "    ")

	resp, err := http.Post(
		ccisUrl,
		"application/json",
		bytes.NewBuffer(jsonBytes))

	if err != nil {
		return "", err
	}
	defer resp.Body.Close()
	if resp.StatusCode != 200 {
		return "", errors.New("http status is not 200 [" + strconv.Itoa(resp.StatusCode) + "]")
	}

	data, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		return "", err
	}

	var jsonRpcRes JsonRpcRes

	//fmt.Printf("data [%s]\n", data)

	err = json.Unmarshal(data, &jsonRpcRes)
	if err != nil {
		return "", err
	}

	//fmt.Printf("???? res[%s][%s]\n", jsonRpcRes.Jsonrpc, jsonRpcRes.Result)

	return jsonRpcRes.Result, nil
}

type JsonRpcReqDeleteCC struct {
	Jsonrpc string       `json:"jsonrpc"`
	Method  string       `json:"method"`
	Id      int          `json:"id"`
	Params  DeleteCCInfo `json:"params"`
}

type JsonRpcReqVersionCC struct {
	Jsonrpc string            `json:"jsonrpc"`
	Method  string            `json:"method"`
	Id      int               `json:"id"`
	Params  ViewVersionCCInfo `json:"params"`
}

type JsonRpcReqUploadCC struct {
	Jsonrpc string       `json:"jsonrpc"`
	Method  string       `json:"method"`
	Id      int          `json:"id"`
	Params  UploadCCInfo `json:"params"`
}

/////
type JsonRpcRes struct {
	Jsonrpc string `json:"jsonrpc"`
	Result  string `json:"result"`
	Id      int    `json:"id"`
}

type UploadCCInfo struct {
	Apiv     string `json:"api_v"`
	Ccid     string `json:"ccid"`
	Version  string `json:"version"`
	GasLevel string `json:"gas_level"`
	NodeId   string `json:"node_id"`
	Target   string `json:"target"`
	PrvPath  string `json:"prv_path"`
	Password string `json:"password"`
}

type DeleteCCInfo struct {
	Apiv     string `json:"api_v"`
	Ccid     string `json:"ccid"`
	Version  string `json:"version"`
	GasLevel string `json:"pub_key"`
	NodeId   string `json:"content_path"`
	Target   string `json:"target"`
	PrvPath  string `json:"prv_path"`
	Password string `json:"password"`
}

type ViewVersionCCInfo struct {
	Apiv    string `json:"api_v"`
	PubKey  string `json:"pub_key"`
	Ccid    string `json:"ccid"`
	Version string `json:"version"`
	Target  string `json:"target"`
}

////////
type Descriptor struct {
	Contents  []string    `json:"contents"`
	BasicMeta []BasicMeta `json:"basicMeta"`
}

type BasicMeta struct {
	Target      []string `json:"target"`
	ContentType string   `json:"content-type"`
	MetaType    string   `json:"meta-type"`
	Metadata    Metadata `json:"metadata"`
}

type Metadata struct {
	VenderId              string       `json:"vender_id,omitempty"`
	Country               string       `json:"country"`
	OriginalSpokenLocale  string       `json:"original_spoken_locale"`
	Title                 string       `json:"title"`
	Synopsis              string       `json:"synopsis"`
	ProductionCompany     string       `json:"production_company"`
	CopyrightCline        string       `json:"copyright_cline"`
	TheatricalReleaseDate string       `json:"theatrical_release_date"`
	Genre                 []string     `json:"genre"`
	Rating                string       `json:"rating"`
	Cast                  []ArtistInfo `json:"cast"`
	Crew                  []ArtistInfo `json:"crew"`
	Artwork               []Artwork    `json:"artwork"`
	//	[191111-jsy] 피플앤스토리 항목 추가 요청
	ContentsInfo string `json:"contents_info"`
}

type ArtistInfo struct {
	Name     string `json:"name"`
	ArtistId string `json:"artist_id"`
	CastName string `json:"cast_name"`
	Role     string `json:"role"`
}

type Artwork struct {
	Title    string `json:"title"`
	FileName string `json:"file_name"`
	FileSize int64  `json:"file_size"`
	Rep      string `json:"rep"`
	Heigh    int64  `json:"height"`
	Width    int64  `json:"width"`
	Format   string `json:"format"`
}

type Manifest struct {
	BasicMeta       []FileInfo      `json:"basic-meta,omitempty"`
	ExtendedMeta    []FileInfo      `json:"extended-meta,omitempty"`
	Contents        []FileInfo      `json:"contents,omitempty"`
	DerivedContents []FileInfo      `json:"derivedContents,omitempty"`
	Control         ManifestControl `json:"control,omitempty"`
}

type FileInfo struct {
	Type     string `json:"type,omitempty"`
	Path     string `json:"path,omitempty"`
	Size     int64  `json:"size,omitempty"`
	FileSize int64  `json:"file_size,omitempty"`
	Cid      string `json:"cid,omitempty"`
}

type ManifestControl struct {
	Owner      string `json:"owner,omitempty"`
	Lastmodify string `json:"lastmodify,omitempty"`
}

type ResultFile struct {
	Result   ResultForAddCC `json:"result"`
	Files    []FileInfo     `json:"files"`
	TxResult TxResult       `json:"tx_result,omitempty"`
}

type ResultForAddCC struct {
	Result        string `json:"result"`
	ResultMessage string `json:"result_message"`
	Ccid          string `json:"ccid"`
	Version       string `json:"version"`
	ChunkSize     int    `json:"chunk_size"`
}

type TxResult struct {
	TxHash string `json:"tx_hash,omitempty"`
	Error  string `json:"error,omitempty"`
}

/*
func copyAndCapture(w io.Writer, r io.Reader) ([]byte, error) {
	var out []byte
	buf := make([]byte, 4096, 4096)
	for {
		n, err := r.Read(buf[:])
		if n > 0 {
			d := buf[:n]
			out = append(out, d...)
			_, err := w.Write(d)
			if err != nil {
				return out, err
			}
		}
		if err != nil {
			// Read returns io.EOF at the end of file, which is not an error for us
			if err == io.EOF {
				err = nil
			}
			return out, err
		}
	}
}
*/

func copyAndCapture(w io.Writer, r io.Reader) ([]string, error) {
	//var out []byte
	var resultLine []string
	buf := make([]byte, 8192, 8192)
	for {
		readNum, err := r.Read(buf[:])
		if readNum > 0 {
			readBuf := buf[:readNum]

			readStr := string(readBuf)
			//fmt.Println("READ [",n,"]OUT PUT :: [", string(d), "]")

			// added
			if strings.HasPrefix(readStr, " added") || strings.HasPrefix(readStr, "added") {
				//Pass
				//fmt.Println("PASS READ [",n,"]OUT PUT :: [", string(d), "]")
				//우왕 된다.. -_-;;;

				resultLine = append(resultLine, readStr)
			} else {
				//out = append(out, readBuf...)
				_, err := w.Write(readBuf)
				if err != nil {
					return resultLine, err
				}
			}
		}
		if err != nil {
			// Read returns io.EOF at the end of file, which is not an error for us
			if err == io.EOF {
				err = nil
			}
			return resultLine, err
		}
	}
}

func predictExt(fileName string) string {
	fileExt := "binary"

	extMap := map[string]string{
		".MP4": "video",
		".TS":  "video",
		".AVI": "video",
		".MKV": "vVideo",
		".MOV": "video",

		".MP3": "audio",
		".OGG": "audio",
		".M4A": "audio",
		".WAV": "audio",

		".PNG":  "image",
		".JPG":  "image",
		".JPEG": "image",
		".GIF":  "image",

		".JSON": "json",
	}

	if strings.HasPrefix(fileName, "basicMeta") || strings.HasPrefix(fileName, "extMeta") {
		fileExt = "manifest"
	} else {
		fExt := strings.ToUpper(filepath.Ext(fileName))
		val, exists := extMap[fExt]
		if exists == false {
			//PASS
		} else {
			fileExt = val
		}
	}

	return fileExt
}
