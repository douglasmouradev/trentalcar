<?php

declare(strict_types=1);

/** Cliente HTTP mínimo para smoke/integration tests (cookies + redirects). */
final class HttpTestClient
{
    private readonly string $cookieJar;

    public function __construct(private readonly string $baseUrl)
    {
        $jar = tempnam(sys_get_temp_dir(), 'trc-http-');
        if ($jar === false) {
            throw new RuntimeException('Failed to create cookie jar');
        }
        $this->cookieJar = $jar;
    }

    public function __destruct()
    {
        if (is_file($this->cookieJar)) {
            unlink($this->cookieJar);
        }
    }

    public static function fromEnv(): ?self
    {
        if (!extension_loaded('curl')) {
            return null;
        }
        $base = getenv('SMOKE_BASE_URL');
        if ($base === false || trim($base) === '') {
            return null;
        }

        return new self(rtrim(trim($base), '/'));
    }

    /** @return array{code: int, body: string, location: ?string} */
    public function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    /** @param array<string, bool|float|int|string|null> $fields */
    public function post(string $path, array $fields): array
    {
        return $this->request('POST', $path, $fields);
    }

    public function extractCsrf(string $html): ?string
    {
        if (preg_match('/name="_csrf"\s+value="([^"]+)"/', $html, $m)) {
            return $m[1];
        }

        return null;
    }

    /** @return array{code: int, body: string, location: ?string} */
    public function loginAs(string $email, string $password = 'password123'): array
    {
        $page = $this->get('/login');
        $csrf = $this->extractCsrf($page['body']);
        if ($csrf === null) {
            throw new RuntimeException('CSRF not found on login page');
        }

        return $this->post('/login', [
            '_csrf' => $csrf,
            'email' => $email,
            'password' => $password,
            'privacy_accept' => '1',
        ]);
    }

    /** @return array{code: int, body: string, location: ?string} */
    public function loginAsOwner(string $email = 'owner@titaniumrental.com', string $password = 'password123'): array
    {
        return $this->loginAs($email, $password);
    }

    /** @param array<string, bool|float|int|string|null>|null $fields */
    private function request(string $method, string $path, ?array $fields = null): array
    {
        $url = $this->baseUrl . ($path === '' ? '/' : $path);
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('curl_init failed');
        }

        $headers = [];
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_COOKIEJAR => $this->cookieJar,
            CURLOPT_COOKIEFILE => $this->cookieJar,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $headerLine) use (&$headers): int {
                unset($curl);
                $trim = trim($headerLine);
                if ($trim !== '') {
                    $headers[] = $trim;
                }

                return strlen($headerLine);
            },
        ];

        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = http_build_query($fields ?? []);
        }

        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('HTTP request failed: ' . $err);
        }

        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $body = substr($raw, $headerSize);
        $location = null;
        foreach ($headers as $header) {
            if (stripos($header, 'Location:') === 0) {
                $location = trim(substr($header, 9));
            }
        }

        return [
            'code' => $code,
            'body' => is_string($body) ? $body : '',
            'location' => $location,
        ];
    }
}
