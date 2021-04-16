// `Tracey` is a simple library which allows for much easier function enter / exit logging
package tracey

import (
	"fmt"
	"io/ioutil"
	"regexp"
	"strconv"

	logging "gx/ipfs/QmbkT7eMTyXfpeyB3ZMxxcxg7XH8t6uXp49jqzz4HB7BGF/go-log"
	"reflect"
	"runtime"
)

// Define a global regex for extracting function names
var RE_stripFnPreamble = regexp.MustCompile(`^.*\.(.*)$`)
var RE_detectFN = regexp.MustCompile(`\$FN`)

// These options represent the various settings which tracey exposes.
// A pointer to this structure is expected to be passed into the
// `tracey.New(...)` function below.
type Options struct {

	// Setting "DisableNesting" to "true" will cause tracey to not indent
	// any messages from nested functions. The default value is "false"
	// which enables nesting by prepending "SpacesPerIndent" number of
	// spaces per level nested.
	DisableNesting  bool
	SpacesPerIndent int `default:"2"`

	// Setting "EnterMessage" or "ExitMessage" will override the default
	// value of "Enter: " and "EXIT:  " respectively.
	EnterMessage string `default:"▶ "`
	ExitMessage  string `default:"◀ "`
}

// Main entry-point for the tracey lib. Calling New with nil will
// result in the default options being used.
func New(opts *Options) (func(logging.EventLogger, [5]uintptr, string), func(logging.EventLogger, ...interface{}) (logging.EventLogger, [5]uintptr, string), func([5]uintptr, logging.EventLogger, ...interface{}) (logging.EventLogger, [5]uintptr, string)) {
	var options Options
	if opts != nil {
		options = *opts
	}

	// Use reflect to deduce "default" values for the
	// Enter and Exit messages (if they are not set)
	reflectedType := reflect.TypeOf(options)
	if options.EnterMessage == "" {
		field, _ := reflectedType.FieldByName("EnterMessage")
		options.EnterMessage = field.Tag.Get("default")
	}
	if options.ExitMessage == "" {
		field, _ := reflectedType.FieldByName("ExitMessage")
		options.ExitMessage = field.Tag.Get("default")
	}

	// If nesting is enabled, and the spaces are not specified,
	// use the "default" value
	if options.DisableNesting {
		options.SpacesPerIndent = 0
	} else if options.SpacesPerIndent == 0 {
		field, _ := reflectedType.FieldByName("SpacesPerIndent")
		options.SpacesPerIndent, _ = strconv.Atoi(field.Tag.Get("default"))
	}

	//
	// Define functions we will use and return to the caller
	//
	_spacify := func(pc [5]uintptr) string {
		return fmt.Sprintf("[0x%x->0x%x->0x%x] ", pc[3], pc[2], pc[1])
	}

	// Enter function, invoked on function entry
	_enter := func(log logging.EventLogger, args ...interface{}) (logging.EventLogger, [5]uintptr, string) {
		var pc [5]uintptr
		var f [5]string
		var l [5]int
		var ok [5]bool

		for iIndex := 0; 5 > iIndex; iIndex++ {
			pc[iIndex], f[iIndex], l[iIndex], ok[iIndex] = runtime.Caller(1 + iIndex)
		}

		// Figure out the name of the caller and use that
		fnName := "<unknown>"
		if ok[0] {
			fnName = RE_stripFnPreamble.ReplaceAllString(runtime.FuncForPC(pc[0]).Name(), "$1") + "()"
		}

		traceMessage := fnName
		if len(args) > 0 {
			if fmtStr, ok := args[0].(string); ok {
				// We have a string leading args, assume its to be formatted
				traceMessage += " " + fmt.Sprintf(fmtStr, args[1:]...)
			}
		}

		// "$FN" will be replaced by the name of the function (if present)
		traceMessage = RE_detectFN.ReplaceAllString(traceMessage, fnName)

		log.Debugf("[ToM]%s%s%s [%s:%d]\r\n=>%s:%d", _spacify(pc), options.EnterMessage, traceMessage, f[0], l[0], f[1], l[1])
		return log, pc, traceMessage
	}

	_enter2 := func(parent [5]uintptr, log logging.EventLogger, args ...interface{}) (logging.EventLogger, [5]uintptr, string) {
		var pc [5]uintptr
		var f [5]string
		var l [5]int
		var ok [5]bool

		for iIndex := 0; 5 > iIndex; iIndex++ {
			pc[iIndex], f[iIndex], l[iIndex], ok[iIndex] = runtime.Caller(1 + iIndex)
		}

		// Figure out the name of the caller and use that
		fnName := "<unknown>"
		if ok[0] {
			fnName = RE_stripFnPreamble.ReplaceAllString(runtime.FuncForPC(pc[0]).Name(), "$1") + "()"
		}

		traceMessage := fnName
		if len(args) > 0 {
			if fmtStr, ok := args[0].(string); ok {
				// We have a string leading args, assume its to be formatted
				traceMessage += " " + fmt.Sprintf(fmtStr, args[1:]...)
			}
		}

		// "$FN" will be replaced by the name of the function (if present)
		traceMessage = RE_detectFN.ReplaceAllString(traceMessage, fnName)

		pc[2] = parent[1]
		pc[3] = parent[2]
		pc[4] = parent[3]

		log.Debugf("[ToM]%s%s%s [%s:%d]", _spacify(pc), options.EnterMessage, traceMessage, f[0], l[0])
		return log, pc, traceMessage
	}

	// Exit function, invoked on function exit (usually deferred)
	_exit := func(log logging.EventLogger, parent [5]uintptr, s string) {
	}

	return _exit, _enter, _enter2
}

var Exit, Enter, Enter2 = New(nil)

// [ToM-190605] 디버깅을 위해 ioutil.ReadFile() 함수를 Wrapping
func ReadFile(log logging.EventLogger, filename string) ([]byte, error) {
	b, err := ioutil.ReadFile(filename)
	if nil != err {
		defer Exit(Enter(log, "path:'%s' => '%v'", filename, err))
		return nil, err
	}
	defer Exit(Enter(log, "path:'%s' => %dbytes", filename, len(b)))
	return b, err
}
