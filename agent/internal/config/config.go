package config

import (
	"encoding/json"
	"errors"
	"os"

	"github.com/nightpos/print-agent/internal/paths"
)

type Config struct {
	BackendURL     string `json:"backend_url"`
	DeviceKey      string `json:"device_key"`
	PrinterName    string `json:"printer_name"`
	PollIntervalMS int    `json:"poll_interval_ms"`
	DryRun         bool   `json:"dry_run"`
	DryRunDir      string `json:"dry_run_dir"`
	LogLevel       string `json:"log_level"`
}

func Default() Config {
	return Config{
		PollIntervalMS: 1500,
		DryRun:         false,
		DryRunDir:      paths.ProgramDataRoot() + `\dry-run-output`,
		LogLevel:       "info",
	}
}

func Load() (Config, error) {
	cfg := Default()
	data, err := os.ReadFile(paths.ConfigPath())
	if err != nil {
		return cfg, err
	}
	if err := json.Unmarshal(data, &cfg); err != nil {
		return cfg, err
	}
	if cfg.PollIntervalMS <= 0 {
		cfg.PollIntervalMS = 1500
	}
	if cfg.LogLevel == "" {
		cfg.LogLevel = "info"
	}
	return cfg, nil
}

func Save(cfg Config) error {
	if err := paths.EnsureDataDirs(); err != nil {
		return err
	}
	data, err := json.MarshalIndent(cfg, "", "  ")
	if err != nil {
		return err
	}
	return os.WriteFile(paths.ConfigPath(), data, 0o644)
}

func Validate(cfg Config) error {
	if cfg.BackendURL == "" {
		return errors.New("backend_url is required")
	}
	if cfg.DeviceKey == "" {
		return errors.New("device_key is required")
	}
	if !cfg.DryRun && cfg.PrinterName == "" {
		return errors.New("printer_name is required")
	}
	return nil
}

func WriteExampleIfMissing() error {
	if err := paths.EnsureDataDirs(); err != nil {
		return err
	}
	if _, err := os.Stat(paths.ConfigPath()); err == nil {
		return nil
	}
	example := Default()
	example.BackendURL = "https://tu-dominio-nightpos.com/api/v1"
	example.DeviceKey = "npd_live_REEMPLAZAR"
	example.PrinterName = "CAJA"
	return Save(example)
}
