<?php

namespace Dejurin\ExchangeRates\Models;

// use Dejurin\ExchangeRates\Models\Currencies;
use Dejurin\ExchangeRates\Plugin;

class Dev
{
    public static function caption($parameters, $time, $widget_nubmer, $widget_slug, $source_id, $dev = 1)
    {
        // $get_currencies = Currencies::get_currencies();
        try {
            $put_time = new \DateTime($time['put_time']);
            $local_time = new \DateTime($time['local_time']);
        } catch (Exception $e) {
            $put_time = new \DateTime(gmdate("r", time()));
            $local_time = new \DateTime(gmdate("r", time()));
        }

        // $tz_name = wp_timezone_string();
        $put_time_timestamp = strtotime($time['put_time']);
        if ($put_time_timestamp === false) {
            $wp_i18n_put_time_d_M = wp_date('d M', time());
        } else {
            $wp_i18n_put_time_d_M = wp_date('d M', $put_time_timestamp);
        }

        $get_sources = Sources::get_list();
        $source = $get_sources[$source_id];

        $base_currency = isset($parameters['base_currency']) && !empty($parameters['base_currency']) ? strtoupper($parameters['base_currency']) : 'USD';
        $quote_currency = isset($parameters['quote_currency']) && !empty($parameters['quote_currency']) ? strtoupper($parameters['quote_currency']) : null;

        $_base_currency = 'USD' === $base_currency && is_null($quote_currency) ? 'usd' : strtolower($base_currency);
        $_quote_currency = !is_null($quote_currency) && $base_currency !== $quote_currency ? strtolower($quote_currency) : '';

        $html = '<div class="cr-'.Plugin::PLUGIN_SLUG.'"><div class="exchange-rates d-flex exchange-rates-caption">';

        if (1 === $dev) {
            $template = '%3$s&nbsp;&middot;&nbsp;<a href="'
                        .((!is_null($quote_currency)) ? 'https://'.esc_attr($_base_currency).'.'.\Dejurin\ExchangeRates\Models\Sources::$_new1.'/'.esc_attr($_quote_currency) : 'https://'.esc_attr($_base_currency).'.'.\Dejurin\ExchangeRates\Admin\Admin::$_new1).'"'
                        .' target="_blank" rel="noreferrer noopener">'
                        .'%1$s</a>&nbsp;&middot;&nbsp;%2$s';
        }
        else if (2 === $dev) {
            $_add = \Dejurin\ExchangeRates\Models\Sources::$_new2;
            if (0 === $dev) {
                $_add = \Dejurin\ExchangeRates\Models\Sources::$_new0;
            }
            $template = '%3$s&nbsp;&middot;&nbsp;%1$s&nbsp;&middot;&nbsp;<a href="'
                    .'https://'.esc_attr($_add).'/'.esc_attr($_base_currency).'"'
                    .' target="_blank" rel="noreferrer noopener">'
                    .'%2$s</a>';
        }
        else {
            $_add = \Dejurin\ExchangeRates\Models\Sources::$_new2;
            if (0 === $dev) {
                $_add = \Dejurin\ExchangeRates\Models\Sources::$_new0;
            }
            $template = '%3$s&nbsp;&middot;&nbsp;<a href="'
                    .'https://'.esc_attr($_add).'/'.esc_attr($_base_currency).'"'
                    .' target="_blank" rel="noreferrer noopener">'
                    .'FX Source</a>:&nbsp;%1$s&nbsp;';
        }

        if (2 === $dev) {
            $html .= sprintf(
                $template,
                '<a href="https://currencyrate.today/" target="_blank" rel="nofollow noreferrer noopener">'.$source['short'].'</a>',
                (!is_null($quote_currency) ? $parameters['base_currency'].'/'.$quote_currency : $parameters['base_currency']),
                $wp_i18n_put_time_d_M
            );
        } else if (1 === $dev) {
            $html .= sprintf(
                $template,
                $source['short'],
                (!is_null($quote_currency) ? $parameters['base_currency'].'/'.$quote_currency : $parameters['base_currency']),
                $wp_i18n_put_time_d_M
            );
        }
        else {
            $html .= sprintf(
                $template,
                '<strong role="button" title="'.esc_attr__('Info', 'exchange-rates').'" data-caption-id="'.esc_attr($widget_slug).'-info-caption'.esc_attr($widget_nubmer).'" class="d-flex info '.esc_attr(Plugin::PLUGIN_SLUG).'-caption-button">'.$source['short'].'</strong>',
                (!is_null($quote_currency) ? $parameters['base_currency'].'/'.$quote_currency : $parameters['base_currency']),
                $wp_i18n_put_time_d_M
            );
        }
        $html .= '</div>';

        $html .= '<div id="'.esc_attr($widget_slug).'-info-caption'.esc_attr($widget_nubmer).'" class="'.esc_attr(Plugin::PLUGIN_SLUG).'-info-caption">'
                .'<div>'
                .'<b><a href="'.esc_url($source['source_url']).'" target="_blank" rel="noreferrer noopener nofollow">'.esc_html($source['name']).'</a></b><br>'
                .'<b>'.esc_html__('Check:', 'exchange-rates').'</b> '.esc_html($put_time->format('d M Y H:i')).' UTC<br>'
                .'<b>'.esc_html__('Latest change:', 'exchange-rates').'</b> '.esc_html($local_time->format('d M Y H:i')).' UTC<br>'
                .'<b>API</b>: CurrencyRate'
                .'</div>'
                .'<div><small><b>'.esc_html__('Disclaimers.', 'exchange-rates').'</b> '.esc_html__('This plugin or website cannot guarantee the accuracy of the exchange rates displayed. You should confirm current rates before making any transactions that could be affected by changes in the exchange rates.', 'exchange-rates').'</small></div>'
                .'<div>⚡<small>'.esc_html__('You can install this WP plugin on your website from the WordPress official website:', 'exchange-rates').' <a href="https://wordpress.org/plugins/exchange-rates/" target="_blank" rel="noreferrer noopener nofollow"><b>Exchange Rates</b></a></small>🚀</div>'
                .'</div></div>';

        return $html;
    }
}
