package status

import (
	"encoding/json"
	"os"
	"sync"
	"time"

	"github.com/nightpos/print-agent/internal/paths"
)

type State string

const (
	StateConnected      State = "connected"
	StateNoInternet     State = "no_internet"
	StatePrinterError   State = "printer_error"
	StateConfigError    State = "config_error"
	StateStarting       State = "starting"
)

type Snapshot struct {
	State          State     `json:"state"`
	Message        string    `json:"message"`
	LastSeenAt     time.Time `json:"last_seen_at,omitempty"`
	LastJobID      int64     `json:"last_job_id,omitempty"`
	LastJobStatus  string    `json:"last_job_status,omitempty"`
	LastError      string    `json:"last_error,omitempty"`
	PrinterName    string    `json:"printer_name,omitempty"`
	BackendURL     string    `json:"backend_url,omitempty"`
	UpdatedAt      time.Time `json:"updated_at"`
	ServiceRunning bool      `json:"service_running"`
}

var (
	mu       sync.RWMutex
	current  Snapshot
)

func init() {
	current = Snapshot{State: StateStarting, Message: "Iniciando...", UpdatedAt: time.Now()}
}

func Get() Snapshot {
	mu.RLock()
	defer mu.RUnlock()
	return current
}

func Update(fn func(*Snapshot)) {
	mu.Lock()
	defer mu.Unlock()
	fn(&current)
	current.UpdatedAt = time.Now()
	writeFile(current)
}

func writeFile(s Snapshot) {
	if err := paths.EnsureDataDirs(); err != nil {
		return
	}
	data, err := json.MarshalIndent(s, "", "  ")
	if err != nil {
		return
	}
	_ = os.WriteFile(paths.StatusFilePath(), data, 0o644)
}

func LoadFromFile() Snapshot {
	data, err := os.ReadFile(paths.StatusFilePath())
	if err != nil {
		return Get()
	}
	var s Snapshot
	if err := json.Unmarshal(data, &s); err != nil {
		return Get()
	}
	mu.Lock()
	current = s
	mu.Unlock()
	return s
}

func Label(s Snapshot) string {
	switch s.State {
	case StateConnected:
		return "Conectado"
	case StateNoInternet:
		return "Sin internet"
	case StatePrinterError:
		return "Error impresora"
	case StateConfigError:
		return "Error configuración"
	default:
		return s.Message
	}
}

func Emoji(s Snapshot) string {
	switch s.State {
	case StateConnected:
		return "🟢"
	case StateNoInternet:
		return "🟡"
	case StatePrinterError, StateConfigError:
		return "🔴"
	default:
		return "⚪"
	}
}
