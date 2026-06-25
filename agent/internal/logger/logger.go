package logger

import (
	"fmt"
	"io"
	"log"
	"os"
	"os/exec"
	"strings"
	"sync"

	"github.com/nightpos/print-agent/internal/paths"
)

var (
	mu          sync.Mutex
	file        *os.File
	std         *log.Logger
	initialized bool
	level       = "info"
)

var levelRank = map[string]int{
	"debug": 10,
	"info":  20,
	"warn":  30,
	"error": 40,
}

func Init() error {
	return InitWithLevel("info")
}

func InitWithLevel(logLevel string) error {
	mu.Lock()
	defer mu.Unlock()
	if initialized {
		return nil
	}
	if err := paths.EnsureDataDirs(); err != nil {
		return err
	}
	f, err := os.OpenFile(paths.LogFilePath(), os.O_CREATE|os.O_WRONLY|os.O_APPEND, 0o644)
	if err != nil {
		return err
	}
	file = f
	mw := io.MultiWriter(f)
	std = log.New(mw, "", log.LstdFlags|log.Lmicroseconds)
	level = normalizeLevel(logLevel)
	initialized = true
	return nil
}

func InitFileOnly() error {
	return Init()
}

func Close() {
	mu.Lock()
	defer mu.Unlock()
	if file != nil {
		_ = file.Close()
		file = nil
	}
	initialized = false
}

func Info(format string, args ...any)  { write("info", format, args...) }
func Warn(format string, args ...any)  { write("warn", format, args...) }
func Error(format string, args ...any) { write("error", format, args...) }
func Debug(format string, args ...any) { write("debug", format, args...) }

func write(lvl, format string, args ...any) {
	mu.Lock()
	defer mu.Unlock()
	if std == nil || !shouldLog(lvl) {
		return
	}
	std.Printf("[%s] "+format, append([]any{strings.ToUpper(lvl)}, args...)...)
}

func shouldLog(lvl string) bool {
	current, ok := levelRank[normalizeLevel(level)]
	if !ok {
		current = levelRank["info"]
	}
	incoming, ok := levelRank[normalizeLevel(lvl)]
	if !ok {
		incoming = levelRank["info"]
	}
	return incoming >= current
}

func normalizeLevel(value string) string {
	return strings.ToLower(strings.TrimSpace(value))
}

func Path() string {
	return paths.LogFilePath()
}

func OpenLogFolder() error {
	return exec.Command("explorer.exe", paths.LogDir()).Start()
}

func OpenLogFile() error {
	return exec.Command("notepad.exe", paths.LogFilePath()).Start()
}

func Banner(version string) {
	Info("NightPOS Agent iniciado v%s", version)
	Info("Config: %s", paths.ConfigPath())
	Info("Logs: %s", paths.LogFilePath())
	fmt.Println()
}
