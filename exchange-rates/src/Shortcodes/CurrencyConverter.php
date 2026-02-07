<?php

namespace Dejurin\ExchangeRates\Shortcodes;

use Dejurin\ExchangeRates\Plugin;
use Dejurin\ExchangeRates\Service\CurrencyConverter as Service_CurrencyConverter;
use Dejurin\ExchangeRates\Service\Tools;

class CurrencyConverter
{
    public $parameters;
    public $settings;
    public $default_attr = [];

    public const BADGE_SLUG = 'shortcode-'.Plugin::PLUGIN_SLUG.'-currency-converter';

    public function __construct()
    {
        add_shortcode(Plugin::PLUGIN_SLUG.'_currency-converter', [$this, 'shortcode']);

        // Defaults (static, predictable)
        $this->default_attr = [
            'title'  => 'title',
            'amount' => 1,
            'from'   => 'USD',
            'to'     => 'EUR',
            'code'   => false,
            'border' => false,
            'after'  => false,
            'symbol' => false,
            'id'     => '', // better: let shortcode generate stable id
        ];
    }

    /**
     * Make attributes safe and typed.
     *
     * @param array $atts
     * @return array
     */
    private function sanitize_atts(array $atts): array
    {
        // 1) Merge with defaults so keys always exist
        //    (WP core helper keeps only known keys for this shortcode tag)
        $merged = shortcode_atts(
            $this->default_attr,
            $atts,
            Plugin::PLUGIN_SLUG.'_currency-converter'
        );

        // 2) Sanitize + types
        $title  = sanitize_text_field((string)$merged['title']);
        $amount = is_numeric($merged['amount']) ? (float)$merged['amount'] : (float)$this->default_attr['amount'];

        // Currency codes: keep [A-Z]{3}, uppercase
        $from = strtoupper(sanitize_key((string)$merged['from']));
        $to   = strtoupper(sanitize_key((string)$merged['to']));
        if (!preg_match('/^[A-Z]{3}$/', $from)) { $from = $this->default_attr['from']; }
        if (!preg_match('/^[A-Z]{3}$/', $to))   { $to   = $this->default_attr['to']; }

        // Booleans: interpret "true/false/1/0/yes/no/on/off" correctly
        $bool = static function ($v): bool {
            // FILTER_VALIDATE_BOOLEAN handles strings safely
            return (bool) filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        };

        $code   = $bool($merged['code']);
        $border = $bool($merged['border']);
        $after  = $bool($merged['after']);
        $symbol = $bool($merged['symbol']);

        // ID: generate stable id if empty; sanitize provided
        $id = $merged['id'];
        if ($id === '' || $id === null) {
            // Stable but unique-ish per config (no time() to avoid cache-busting)
            $id = substr('w-'.md5($from.'|'.$to.'|'.$amount.'|'.(int)$code.(int)$border.(int)$after.(int)$symbol), 0, 12);
        } else {
            // allows [A-Za-z0-9_-] and keeps it CSS-safe
            $id = sanitize_html_class((string)$id);
        }

        return [
            'title'  => $title,
            'amount' => $amount,
            'from'   => $from,
            'to'     => $to,
            'code'   => $code,
            'border' => $border,
            'after'  => $after,
            'symbol' => $symbol,
            'id'     => $id,
        ];
    }

    public function shortcode($attr = [])
    {
        // Keep only allowed keys (defense-in-depth)
        if (is_array($attr)) {
            $attr = Tools::filter_keys_allowed_list(
                $attr,
                ['title','amount','from','to','code','border','after','symbol','id']
            );
        } else {
            $attr = [];
        }

        // Merge + sanitize
        $safe = $this->sanitize_atts($attr);

        // --- Ensure unique DOM id while keeping cache-friendly base id ---
        // comment: userProvidedId — true if user set `id` explicitly
        $userProvidedId = isset($attr['id']) && $attr['id'] !== '' && $attr['id'] !== null;

        // comment: baseId is stable (hash of params) or sanitized user id
        $baseId = $safe['id'];
        $domId  = $baseId;

        // comment: only auto-generated ids may be suffixed; user ids are respected as-is
        static $used = []; // request-scoped counter
        if (!$userProvidedId) {
            if (!isset($used[$baseId])) {
                $used[$baseId] = 1;               // first occurrence: keep base id
            } else {
                $used[$baseId]++;                  // next ones: -2, -3, ...
                $domId = $baseId . '-' . $used[$baseId];
            }
        }

        // Prepare service
        $obj = new Service_CurrencyConverter();
        $obj->parameters = array_merge($this->default_attr, $safe, ['id' => $domId]);

        // Render
        return '<div class="cr-'.esc_attr(Plugin::PLUGIN_SLUG).'">'.
            $obj->get_html_widget($domId).
        '</div>';
    }

}
