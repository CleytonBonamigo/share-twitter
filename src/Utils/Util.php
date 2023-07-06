<?php

namespace CleytonBonamigo\ShareTwitter\Utils;

class Util
{
    /**
     * @param mixed $input
     *
     * @return mixed
     */
    public static function urlencodeRfc3986($input)
    {
        $output = '';
        if (is_array($input)) {
            $output = array_map(
                [__NAMESPACE__ . '\Util', 'urlencodeRfc3986'],
                $input,
            );
        } elseif (is_scalar($input)) {
            $output = rawurlencode((string) $input);
        }
        return $output;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public static function buildHttpQuery(array $params): string
    {
        if (empty($params)) {
            return '';
        }

        // Urlencode both keys and values
        $keys = Util::urlencodeRfc3986(array_keys($params));
        $values = Util::urlencodeRfc3986(array_values($params));
        $params = array_combine($keys, $values);

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');

        $pairs = [];
        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                // June 12th, 2010 - changed to sort because of issue 164 by hidetaka
                sort($value, SORT_STRING);
                foreach ($value as $duplicateValue) {
                    $pairs[] = $parameter . '=' . $duplicateValue;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        return implode('&', $pairs);
    }

    /**
     * Return the default CURL Options.
     * @return array
     */
    public static function curlOptions(): array
    {
        return [
            // CURLOPT_VERBOSE => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
            CURLOPT_ENCODING => 'gzip'
        ];
    }

    /**
     * Parse headers from CURL Request.
     * @param string $header
     * @return array
     */
    public static function parseHeaders(string $header): array
    {
        $headers = [];
        foreach (explode("\r\n", $header) as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(': ', $line);
                $key = str_replace('-', '_', strtolower($key));
                $headers[$key] = trim($value);
            }
        }
        return $headers;
    }
}
