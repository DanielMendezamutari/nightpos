package api

import (
	"bytes"
	"crypto/tls"
	"encoding/json"
	"fmt"
	"io"
	"net"
	"net/http"
	"strings"
	"time"
)

const UserAgent = "NightPOSPrintAgent/2.0"

const requestTimeout = 30 * time.Second

type Client struct {
	baseURL    string
	deviceKey  string
	httpClient *http.Client
}

type PrintJob struct {
	ID          int64  `json:"id"`
	Type        string `json:"type"`
	SourceType  string `json:"source_type"`
	SourceID    int64  `json:"source_id"`
	ContentText string `json:"content_text"`
	Attempts    int    `json:"attempts"`
	CreatedAt   string `json:"created_at"`
}

func NewHTTPClient() *http.Client {
	transport := &http.Transport{
		Proxy: http.ProxyFromEnvironment,
		DialContext: (&net.Dialer{
			Timeout:   10 * time.Second,
			KeepAlive: 30 * time.Second,
		}).DialContext,
		ForceAttemptHTTP2:     false,
		TLSNextProto:          make(map[string]func(authority string, c *tls.Conn) http.RoundTripper),
		MaxIdleConns:          4,
		MaxIdleConnsPerHost:   2,
		IdleConnTimeout:       90 * time.Second,
		TLSHandshakeTimeout:   10 * time.Second,
		ExpectContinueTimeout: 1 * time.Second,
		ResponseHeaderTimeout: 20 * time.Second,
	}

	return &http.Client{
		Transport: transport,
		Timeout:   requestTimeout,
	}
}

func New(baseURL, deviceKey string) *Client {
	return &Client{
		baseURL:    strings.TrimRight(baseURL, "/"),
		deviceKey:  deviceKey,
		httpClient: NewHTTPClient(),
	}
}

type envelope struct {
	Success bool            `json:"success"`
	Message string          `json:"message"`
	Data    json.RawMessage `json:"data"`
}

func (c *Client) request(method, route string, body any) (json.RawMessage, error) {
	var reader io.Reader
	if body != nil {
		b, err := json.Marshal(body)
		if err != nil {
			return nil, err
		}
		reader = bytes.NewReader(b)
	}

	req, err := http.NewRequest(method, c.baseURL+route, reader)
	if err != nil {
		return nil, err
	}
	req.Header.Set("Authorization", "Bearer "+c.deviceKey)
	req.Header.Set("Accept", "application/json")
	req.Header.Set("User-Agent", UserAgent)
	if body != nil {
		req.Header.Set("Content-Type", "application/json")
	}

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	raw, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, err
	}

	var env envelope
	if err := json.Unmarshal(raw, &env); err != nil {
		return nil, fmt.Errorf("invalid API response: %w", err)
	}
	if resp.StatusCode >= 400 {
		msg := env.Message
		if msg == "" {
			msg = resp.Status
		}
		return nil, fmt.Errorf("%d %s", resp.StatusCode, msg)
	}
	if len(env.Data) == 0 {
		return json.RawMessage("{}"), nil
	}
	return env.Data, nil
}

func (c *Client) Heartbeat(printerName, agentVersion, lastError string) error {
	body := map[string]string{
		"printer_name":  printerName,
		"agent_version": agentVersion,
	}
	if lastError != "" {
		body["last_error"] = lastError
	}
	_, err := c.request(http.MethodPost, "/print-devices/heartbeat", body)
	return err
}

func (c *Client) Pending(limit int) ([]PrintJob, error) {
	data, err := c.request(http.MethodGet, fmt.Sprintf("/print-jobs/pending?limit=%d", limit), nil)
	if err != nil {
		return nil, err
	}
	var parsed struct {
		Jobs []PrintJob `json:"jobs"`
	}
	if err := json.Unmarshal(data, &parsed); err != nil {
		return nil, err
	}
	return parsed.Jobs, nil
}

func (c *Client) Claim(jobID int64) error {
	_, err := c.request(http.MethodPost, fmt.Sprintf("/print-jobs/%d/claim", jobID), nil)
	return err
}

func (c *Client) Printed(jobID int64) error {
	_, err := c.request(http.MethodPost, fmt.Sprintf("/print-jobs/%d/printed", jobID), nil)
	return err
}

func (c *Client) Failed(jobID int64, errMsg string) error {
	_, err := c.request(http.MethodPost, fmt.Sprintf("/print-jobs/%d/failed", jobID), map[string]string{
		"error": errMsg,
	})
	return err
}

func IsNetworkError(err error) bool {
	if err == nil {
		return false
	}
	s := strings.ToLower(err.Error())
	needles := []string{
		"timeout",
		"connection refused",
		"connection reset",
		"no such host",
		"network is unreachable",
		"i/o timeout",
		"stream error",
		"cancel",
		"forcibly closed",
		"wsarecv",
		"empty reply",
		"eof",
		"unexpected eof",
		"broken pipe",
		"tls:",
	}
	for _, n := range needles {
		if strings.Contains(s, n) {
			return true
		}
	}
	return err == io.EOF || err == io.ErrUnexpectedEOF
}
