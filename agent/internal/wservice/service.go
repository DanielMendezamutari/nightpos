package wservice

import (
	"context"
	"fmt"
	"os"
	"path/filepath"

	"github.com/kardianos/service"
	"github.com/nightpos/print-agent/internal/agent"
	"github.com/nightpos/print-agent/internal/config"
	"github.com/nightpos/print-agent/internal/logger"
	"github.com/nightpos/print-agent/internal/paths"
)

type program struct {
	runtime *agent.Runtime
	cancel  context.CancelFunc
}

func (p *program) Start(s service.Service) error {
	cfg, err := config.Load()
	if err != nil {
		return err
	}
	if err := config.Validate(cfg); err != nil {
		return err
	}
	if err := logger.InitWithLevel(cfg.LogLevel); err != nil {
		return err
	}
	logger.Banner(agent.Version)
	logger.Info("Backend: %s", cfg.BackendURL)
	logger.Info("Impresora: %s", cfg.PrinterName)
	logger.Info("dry_run=%v poll_interval_ms=%d", cfg.DryRun, cfg.PollIntervalMS)

	ctx, cancel := context.WithCancel(context.Background())
	p.cancel = cancel
	p.runtime = agent.NewRuntime(cfg)
	p.runtime.Start(ctx)
	logger.Info("Service worker started")
	return nil
}

func (p *program) Stop(s service.Service) error {
	logger.Info("Service stop requested")
	if p.cancel != nil {
		p.cancel()
	}
	if p.runtime != nil {
		p.runtime.Stop()
	}
	logger.Close()
	return nil
}

func ConfigFor(exePath string) (service.Config, error) {
	if exePath == "" {
		var err error
		exePath, err = os.Executable()
		if err != nil {
			return service.Config{}, err
		}
	}
	exePath, err := filepath.Abs(exePath)
	if err != nil {
		return service.Config{}, err
	}

	return service.Config{
		Name:        paths.ServiceName,
		DisplayName: paths.DisplayName,
		Description: "Agente de impresión local NightPOS (comandas térmicas USB).",
		Executable:  exePath,
	}, nil
}

func NewAt(exePath string) (service.Service, error) {
	cfg, err := ConfigFor(exePath)
	if err != nil {
		return nil, err
	}
	return service.New(&program{}, &cfg)
}

func New() (service.Service, error) {
	return NewAt("")
}

func RunService() error {
	s, err := New()
	if err != nil {
		return err
	}
	return s.Run()
}

func Control(action string) error {
	return ControlAt("", action)
}

func ControlAt(exePath, action string) error {
	s, err := NewAt(exePath)
	if err != nil {
		return fmt.Errorf("service init: %w", err)
	}
	if err := service.Control(s, action); err != nil {
		return err
	}
	switch action {
	case "install":
		fmt.Println("Servicio instalado:", paths.ServiceName)
	case "uninstall":
		fmt.Println("Servicio desinstalado:", paths.ServiceName)
	case "start":
		fmt.Println("Servicio iniciado")
	case "stop":
		fmt.Println("Servicio detenido")
	case "restart":
		fmt.Println("Servicio reiniciado")
	}
	return nil
}

func StatusText() (string, error) {
	s, err := New()
	if err != nil {
		return "", err
	}
	st, err := s.Status()
	if err != nil {
		return "", err
	}
	switch st {
	case service.StatusRunning:
		return "RUNNING", nil
	case service.StatusStopped:
		return "STOPPED", nil
	case service.StatusUnknown:
		return "UNKNOWN", nil
	default:
		return fmt.Sprintf("%v", st), nil
	}
}
