package logger

import (
	"fmt"
	"io"
	"log"
	"os"
	"os/exec"
	"sync"

	"github.com/nightpos/print-agent/internal/paths"
)

var (
	mu          sync.Mutex
	file        *os.File
	std         *log.Logger
	initialized bool
)

func Init() error {
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

func Info(format string, args ...any)  { write("INFO", format, args...) }
func Warn(format string, args ...any)  { write("WARN", format, args...) }
func Error(format string, args ...any) { write("ERROR", format, args...) }
func Debug(format string, args ...any) { write("DEBUG", format, args...) }

func write(level, format string, args ...any) {
	mu.Lock()
	defer mu.Unlock()
	if std == nil {
		return
	}
	std.Printf("[%s] "+format, append([]any{level}, args...)...)
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
	Info("NightPOS Print Agent %s starting", version)
	Info("Config: %s", paths.ConfigPath())
	Info("Logs: %s", paths.LogFilePath())
	fmt.Println()
}
