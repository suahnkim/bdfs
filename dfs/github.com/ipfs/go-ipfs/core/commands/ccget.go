package commands

import (
	"fmt"
	cid "gx/ipfs/QmTbxNB1NwDesLmKTscr4udL2tVP7MaxvXnD1D9yX7g3PN/go-cid"
	cmdkit "gx/ipfs/Qmde5VP1qUkyQXKCfmEUA7bP64V2HAptbJ7phuPp7jXWwg/go-ipfs-cmdkit"
	coreiface "gx/ipfs/QmeWKXQfEqbtUDCiQBAHzSZDja9br5LdPgk8eHu86oJxgr/interface-go-ipfs-core"
	"io"
	"io/ioutil"
	"os"

	oldcmds "github.com/ipfs/go-ipfs/commands"
	"github.com/ipfs/go-ipfs/core/commands/cmdenv"
	"github.com/ipfs/go-ipfs/core/commands/e"
	"github.com/ipfs/go-ipfs/thirdparty/mb"

	cmds "gx/ipfs/QmQtQrtNioesAWtrx8csBvfY37gTe94d6wQ3VikZUjxD39/go-ipfs-cmds"
	path "gx/ipfs/QmR3bNAtBoTN6xZ2HQNqpRQARcDoazH9jU6zKUNjFyQKWS/go-path"
	ipld "gx/ipfs/QmZ6nzCLwGLVfRzYLpD7pW6UNuBDKEcA2imJtVpbEx2rxy/go-ipld-format"
)

const (
	pidOptionName = "pid"
)

var CCGetCmd = &cmds.Command{
	Helptext: cmdkit.HelpText{
		Tagline: "Download CCFS objects.",
		ShortDescription: `
Stores to disk the data contained an CCFS object(s) at the given path.

By default, the output will be stored at './<ccfs-path>', but an alternate
path can be specified with '--output=<path>' or '-o=<path>'.
`,
	},

	Arguments: []cmdkit.Argument{
		cmdkit.StringArg("ccfs-path", true, false, "The path to the CCFS object(s) to be outputted.").EnableStdin(),
	},
	Options: []cmdkit.Option{
		cmdkit.StringOption(pidOptionName, "p", "Distribution service product ID."),
		cmdkit.StringOption(outputOptionName, "o", "The path where the output should be stored."),
	},
	PreRun: func(req *cmds.Request, env cmds.Environment) error {
		// 오프라인 모드는 직접 오프체인 채널을 초기화한다.
		cctx := env.(*oldcmds.Context)
		mb.InitChannel(cctx.ConfigRoot)
		return nil
	},
	Run: func(req *cmds.Request, res cmds.ResponseEmitter, env cmds.Environment) error {
		//	명령이 동시 실행되지 않도록 잠근다.
		mb.Lock()

		defer func() {
			//	다음 작업을 위해 잠김을 해제한다.
			mb.Unlock()
		}()

		cmplvl, err := getCompressOptions(req)
		if err != nil {
			return err
		}

		api, err := cmdenv.GetApi(env, req)
		if nil != err {
			return err
		}

		//	인자 중 CCFS를 얻고 분석한다.
		ccfs, err := coreiface.ParsePath(req.Arguments[0])
		if nil != err {
			return err
		}

		//	CCFS 중 CCID, Version, 하위 경로를 얻는다.
		ccid, version, subPaths, err := path.SplitAbsPath(path.Path(ccfs.String()))
		if nil != err {
			return err
		}

		//	CCFS 내 모든 파일의 링크 정보를 조회한다.
		linkRoot, err := api.Object().LinksAll(req.Context, version)
		if nil != err {
			return err
		}

		//	하위 경로가 있으면 링크 정보에서 CCID를 찾는다.
		if 0 < len(subPaths) {
			if linkRoot, err = mb.FindLinkByPaths(linkRoot.Childs, linkRoot.Cid, subPaths); nil != err {
				return err
			}
		}

		//	모든 블록을 폴더 및 파일 단위로 정렬한다.
		mLinks := make(map[cid.Cid]*ipld.Link)
		mb.SortByDirAndFile(linkRoot, linkRoot.Childs, &mLinks)

		//	파일 별 다운로드 대상 청크 목록을 작성한다.
		if list := mb.MakeDownloadChunks(ccid, version, &mLinks); "" != list {
			//	다운로드 대상 목록을 파일에 기록한다.
			path := os.TempDir() + string(os.PathSeparator) + "FileChunks.dat"
			fmt.Println("\n● 청크 목록 파일:", path, "\n", list)
			if err = ioutil.WriteFile(path, []byte(list), os.ModePerm); nil != err {
				return fmt.Errorf("청크 목록 작성 실패: %s", err)
			}
			//	함수 종료 시 임시 파일을 삭제한다.
			defer os.Remove(path)

			//	온체인에 오프체인 채널을 개설한다.
			fmt.Println("● 오프체인 채널 개설 시작")
			pid, _ := req.Options[pidOptionName].(string)
			_, err := mb.OpenChannel(pid, linkRoot, mLinks, path)
			if nil != err {
				return fmt.Errorf("오프체인 채널 개설 실패: %s", err)
			}

			//	[200709-ToM] 최상위 블록에 채널ID를 설정한다.
			//linkRoot.Cid.SetChID(ch.ID)
		}

		file, err := api.Unixfs().GetT(req.Context, linkRoot.Cid)
		if nil != err {
			return err
		}

		size, err := file.Size()
		if err != nil {
			return err
		}

		res.SetLength(uint64(size))

		archive, _ := req.Options[archiveOptionName].(bool)
		reader, err := fileArchive(file, ccfs.String(), archive, cmplvl)
		if err != nil {
			return err
		}

		return res.Emit(reader)
	},
	PostRun: cmds.PostRunMap{
		cmds.CLI: func(res cmds.Response, re cmds.ResponseEmitter) error {
			req := res.Request()

			v, err := res.Next()
			if nil != err {
				return err
			}

			outReader, ok := v.(io.Reader)
			if !ok {
				return e.New(e.TypeErr(outReader, v))
			}

			outPath := getOutPath(req)

			cmplvl, err := getCompressOptions(req)
			if nil != err {
				return err
			}

			archive, _ := req.Options[archiveOptionName].(bool)

			gw := getWriter{
				Out:         os.Stdout,
				Err:         os.Stderr,
				Archive:     archive,
				Compression: cmplvl,
				Size:        int64(res.Length()),
			}

			return gw.Write(outReader, outPath)
		},
	},
}
