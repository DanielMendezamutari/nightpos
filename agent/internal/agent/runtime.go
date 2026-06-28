package agent

import (
	"context"
	"sync"
	"time"

	"github.com/nightpos/print-agent/internal/api"
	"github.com/nightpos/print-agent/internal/config"
	"github.com/nightpos/print-agent/internal/logger"
	"github.com/nightpos/print-agent/internal/printer"
	"github.com/nightpos/print-agent/internal/status"
)

const Version = "2.0.0"

var networkBackoffSteps = []time.Duration{
	30 * time.Second,
	60 * time.Second,
	120 * time.Second,
	300 * time.Second,
}

type Runtime struct {
	cfg    config.Config
	client *api.Client
	cancel context.CancelFunc
	wg     sync.WaitGroup

	backoffMu     sync.Mutex
	netFailStreak int
}

func NewRuntime(cfg config.Config) *Runtime {
	return &Runtime{
		cfg:    cfg,
		client: api.New(cfg.BackendURL, cfg.DeviceKey),
	}
}

func (r *Runtime) Start(ctx context.Context) {
	ctx, r.cancel = context.WithCancel(ctx)
	r.wg.Add(1)
	go r.loop(ctx)
}

func (r *Runtime) Stop() {
	if r.cancel != nil {
		r.cancel()
	}
	r.wg.Wait()
}

func (r *Runtime) loop(ctx context.Context) {
	defer r.wg.Done()

	status.Update(func(s *status.Snapshot) {
		s.State = status.StateStarting
		s.Message = "Iniciando agente..."
		s.PrinterName = r.cfg.PrinterName
		s.BackendURL = r.cfg.BackendURL
		s.ServiceRunning = true
	})

	if !r.cfg.DryRun {
		if err := printer.Verify(r.cfg.PrinterName); err != nil {
			logger.Error("Printer verify failed: %v", err)
			status.Update(func(s *status.Snapshot) {
				s.State = status.StatePrinterError
				s.Message = err.Error()
				s.LastError = err.Error()
			})
		}
	}

	netErr := r.tick(ctx)
	wait := r.nextWait(netErr)

	for {
		select {
		case <-ctx.Done():
			status.Update(func(s *status.Snapshot) {
				s.ServiceRunning = false
				s.Message = "Detenido"
			})
			return
		case <-time.After(wait):
			netErr = r.tick(ctx)
			wait = r.nextWait(netErr)
		}
	}
}

func (r *Runtime) nextWait(networkErr bool) time.Duration {
	r.backoffMu.Lock()
	defer r.backoffMu.Unlock()

	base := time.Duration(r.cfg.PollIntervalMS) * time.Millisecond

	if !networkErr {
		r.netFailStreak = 0
		return base
	}

	if r.netFailStreak < len(networkBackoffSteps) {
		r.netFailStreak++
	}
	idx := r.netFailStreak - 1
	if idx >= len(networkBackoffSteps) {
		idx = len(networkBackoffSteps) - 1
	}
	wait := networkBackoffSteps[idx]
	logger.Info("Backoff hosting: próximo intento en %s (fallo de red #%d)", wait, r.netFailStreak)
	return wait
}

func (r *Runtime) tick(ctx context.Context) (networkErr bool) {
	select {
	case <-ctx.Done():
		return false
	default:
	}

	if err := r.client.Heartbeat(r.cfg.PrinterName, Version, ""); err != nil {
		logger.Warn("Error conexión backend: %v", err)
		if api.IsNetworkError(err) {
			status.Update(func(s *status.Snapshot) {
				s.State = status.StateNoInternet
				s.Message = "Sin conexión al backend"
				s.LastError = err.Error()
			})
			return true
		}
		status.Update(func(s *status.Snapshot) {
			s.State = status.StateConfigError
			s.Message = err.Error()
			s.LastError = err.Error()
		})
		return false
	}

	status.Update(func(s *status.Snapshot) {
		s.LastSeenAt = time.Now()
		if s.State != status.StatePrinterError {
			s.State = status.StateConnected
			s.Message = "Conectado"
			s.LastError = ""
		}
	})
	logger.Debug("Device online — backend OK")

	jobs, err := r.client.Pending(5)
	if err != nil {
		logger.Warn("Pending jobs failed: %v", err)
		if api.IsNetworkError(err) {
			status.Update(func(s *status.Snapshot) {
				s.State = status.StateNoInternet
				s.Message = "Sin conexión al backend"
				s.LastError = err.Error()
			})
			return true
		}
		return false
	}

	for _, job := range jobs {
		select {
		case <-ctx.Done():
			return false
		default:
			r.processJob(job)
		}
	}

	return false
}

func (r *Runtime) processJob(job api.PrintJob) {
	logger.Info("Job #%d %s recibido", job.ID, job.Type)

	if err := r.client.Claim(job.ID); err != nil {
		logger.Error("Job #%d claim failed: %v", job.ID, err)
		return
	}
	logger.Info("Job #%d CLAIMED", job.ID)

	printErr := r.print(job)
	if printErr != nil {
		logger.Error("Job #%d FAILED — impresora: %v", job.ID, printErr)
		_ = r.client.Failed(job.ID, printErr.Error())
		status.Update(func(s *status.Snapshot) {
			s.State = status.StatePrinterError
			s.Message = printErr.Error()
			s.LastError = printErr.Error()
			s.LastJobID = job.ID
			s.LastJobStatus = "FAILED"
		})
		return
	}

	if err := r.client.Printed(job.ID); err != nil {
		logger.Error("Mark printed #%d failed: %v", job.ID, err)
		return
	}

	logger.Info("Job #%d PRINTED", job.ID)
	status.Update(func(s *status.Snapshot) {
		s.LastJobID = job.ID
		s.LastJobStatus = "PRINTED"
		if s.State == status.StatePrinterError {
			s.State = status.StateConnected
			s.Message = "Conectado"
		}
	})
}

func (r *Runtime) print(job api.PrintJob) error {
	content := job.ContentText
	if content == "" {
		return errEmptyContent
	}

	if r.cfg.DryRun {
		txt, bin, n, err := printer.WriteDryRun(r.cfg.DryRunDir, job.ID, content)
		if err != nil {
			return err
		}
		logger.Info("Dry-run job #%d -> %s (%d bytes RAW in %s)", job.ID, txt, n, bin)
		return nil
	}

	logger.Info("Imprimiendo job #%d en %s", job.ID, r.cfg.PrinterName)
	n, err := printer.PrintRawEscPos(r.cfg.PrinterName, content)
	if err != nil {
		return err
	}
	logger.Info("Job #%d PRINTED (%d bytes)", job.ID, n)
	return nil
}

var errEmptyContent = &printError{"content_text vacío"}

type printError struct{ msg string }

func (e *printError) Error() string { return e.msg }
