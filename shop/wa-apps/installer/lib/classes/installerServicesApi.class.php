<?php

class installerServicesApi extends waWebasystIDApi
{
    const WS_CONNECT_SERVICE = 'WS_TOKEN';
    const WS_MESSAGE_SERVICE = 'WS_MESSAGE';
    const EMAIL_MESSAGE_SERVICE = 'EMAIL';
    const SMS_SERVICE = 'SMS';

    public function __construct(array $options = [])
    {
        $config = new waServicesApiUrlConfig();
        $this->provider = new waServicesUrlsProvider([
            'config' => $config
        ]);
    }

    public function isConnected()
    {
        static $result = null;
        if ($result === null) {
            $cm = new waWebasystIDClientManager();
            $result = !!$cm->isConnected();
        }
        return $result;
    }

    public function billingCall($api_method, array $params = [], $http_method = waNet::METHOD_GET, array $net_options = [])
    {
        if (!$this->isConnected()) {
            throw new waException(_w('Not connected to Webasyst ID.'));
        }
        $token = $this->getSystemToken();
        $resp = $this->requestApiMethod($api_method, $token, $params, $http_method, $net_options);
        if ($resp['status'] == 401) {
            $token = $this->getSystemToken(true);
            $resp = $this->requestApiMethod($api_method, $token, $params, $http_method, $net_options);
        } else if ($resp['status'] == 404) {
            // If endpoint URL is invalid, try to refresh endpoint config and repeate call
            $this->refreshApiUrlConfig();
            $resp = $this->requestApiMethod($api_method, $token, $params, $http_method, $net_options);
        } else if (in_array($resp['status'], [301, 302]) && !empty(ifset($resp, 'headers', 'Location', null))) {
            // If endpoint URL is moved, try to repeate call to new url
            $url = $resp['headers']['Location'];
            $resp = $this->requestApiUrl($url, $token, $params, $http_method, $net_options);
        }
        return $resp;
    }

    public function serviceCall(
        $service,
        array $params = [],
        $http_method = waNet::METHOD_GET,
        array $net_options = [],
        $service_sub_path = '',
        $use_system_token = true
    ) {
        if (!$this->isConnected()) {
            throw new waException(_w('Not connected to Webasyst ID.'));
        }
        $url = $this->provider->getServiceUrl($service);
        if (!$url) {
            throw new waException('Unable to get URL for service '.htmlspecialchars($service));
        }
        $url .= $service_sub_path;
        $token = $this->getToken($use_system_token);
        if (empty($token)) {
            throw new waException('Unable to get access token for Webasyst service API');
        }
        $resp = $this->requestApiUrl($url, $token, $params, $http_method, $net_options);
        if ($resp['status'] == 401) {
            $token = $this->getToken($use_system_token, true);
            if (empty($token)) {
                throw new waException('Unable to get access token for Webasyst service API');
            }
            $resp = $this->requestApiUrl($url, $token, $params, $http_method, $net_options);
        } else if ($resp['status'] == 404) {
            // If endpoint URL is invalid, try to refresh endpoint config and repeate call
            $this->refreshApiUrlConfig();
            $url = $this->provider->getServiceUrl($service);
            if (!empty($url)) {
                $url .= $service_sub_path;
                $resp = $this->requestApiUrl($url, $token, $params, $http_method, $net_options);
            }
        } else if (in_array($resp['status'], [301, 302]) && !empty(ifset($resp, 'headers', 'Location', null))) {
            // If endpoint URL is moved, try to repeate call to new url
            $url = $resp['headers']['Location'];
            $resp = $this->requestApiUrl($url, $token, $params, $http_method, $net_options);
        }
        return $resp;
    }

    public function getBalance($service = null, $locale = null)
    {
        if (empty($locale)) {
            $locale = wa()->getLocale();
        }
        $params = ['locale' => $locale];
        if (!empty($service)) {
            if (is_array($service)) {
                $params['services'] = $service;
            } else {
                $params['service'] = $service;
            }
        }
        return $this->billingCall('balance', $params);
    }

    public function isBalanceEnough($service, $count, $locale = null)
    {
        if (empty($locale)) {
            $locale = wa()->getLocale();
        }
        return $this->billingCall(
            'balance/check-service',
            [
                'service' => $service,
                'count' => $count,
                'locale' => $locale
            ],
            waNet::METHOD_POST
        );
    }

    public function getBalanceCreditUrl($service = null)
    {
        $params = empty($service) ? [] : [ 'service' => $service ];
        return $this->billingCall('balance/credit-url', $params);
    }

    public function getIpWhiteList()
    {
        return $this->billingCall('ip-white-list');
    }

    public function addIpToWhiteList($ip)
    {
        return $this->billingCall(
            'ip-white-list/add',
            ['ip' => $ip],
            waNet::METHOD_POST,
            [
                'format' => waNet::FORMAT_JSON,
                'request_format' => waNet::FORMAT_RAW,
            ]
        );
    }

    public function deleteIpFromWhiteList($ip)
    {
        return $this->billingCall(
            'ip-white-list/delete',
            ['ip' => $ip],
            waNet::METHOD_POST,
            [
                'format' => waNet::FORMAT_JSON,
                'request_format' => waNet::FORMAT_RAW,
            ]
        );
    }

    public function confirmIpWhiteListChange($code)
    {
        return $this->billingCall(
            'ip-white-list/confirm-operation',
            ['code' => $code],
            waNet::METHOD_POST,
            [
                'format' => waNet::FORMAT_JSON,
                'request_format' => waNet::FORMAT_RAW,
            ]
        );
    }

    public function sendEmail(array $message_data)
    {
        return $this->serviceCall(self::EMAIL_MESSAGE_SERVICE, $message_data, waNet::METHOD_POST, ['request_format' => waNet::FORMAT_JSON]);
    }

    public function sendSms($to, $text, $from = null, $is_repeated = false, $app_id = null, $context = null)
    {
        $app_id = ifempty($app_id, wa()->getApp());
        return $this->serviceCall(self::SMS_SERVICE, [
            'to' => $to,
            'text' => $text,
            'from' => $from,
            'is_repeated' => $is_repeated,
            'app_id' => $app_id,
            'context' => $context,
        ], waNet::METHOD_POST, ['request_format' => waNet::FORMAT_JSON]);
    }

    public function getWebsocketUrl($channel_id, $app_id = null)
    {
        $app_id = ifempty($app_id, wa()->getApp());
        $api_result = $this->serviceCall(
            self::WS_CONNECT_SERVICE,
            ['channel' => $app_id.'_'.$channel_id],
            waNet::METHOD_POST,
            ['request_format' => waNet::FORMAT_JSON],
            '', false
        );
        if ($api_result['status'] >= 300) {
            $this->logError($api_result);
            throw new waException(_w('Webasyst API access error.'));
        } else {
            return ifset($api_result, 'response', 'ws_url', null);
        }
    }

    public function sendWebsocketMessage(array $message, $channel_id, $app_id = null)
    {
        $app_id = ifempty($app_id, wa()->getApp());
        $api_result = $this->serviceCall(
            self::WS_MESSAGE_SERVICE,
            $message,
            waNet::METHOD_POST,
            ['request_format' => waNet::FORMAT_JSON],
            '/'.$app_id.'_'.$channel_id.'/'
        );
        if ($api_result['status'] >= 300) {
            $this->logError($api_result);
            throw new waException(_w('Webasyst API access error.'));
        }
    }

    public function getSystemToken($force_refresh = false)
    {
        static $token = null;
        if ($token === null || $force_refresh) {
            $token = (new waWebasystIDClientManager)->getSystemAccessToken($force_refresh);
        }
        return $token;
    }

    public function getUserToken($force_refresh = false)
    {
        $token_params = wa()->getUser()->getWebasystTokenParams();
        if (empty($token_params)) {
            return null;
        }
        if ($force_refresh) {
            $ok = $this->refreshedTokenParams($token_params, wa()->getUser()->getId());
        } else {
            $ok = $this->refreshTokenWhenExpired($token_params, wa()->getUser()->getId());
        }
        if (!$ok) {
            return null;
        }
        return $token_params['access_token'];
    }

    private function getToken($use_system_token, $force_refresh = false)
    {
        return $use_system_token ?
            $this->getSystemToken($force_refresh) :
            ($this->getUserToken($force_refresh) ?: $this->getSystemToken($force_refresh));
    }

    protected function refreshApiUrlConfig()
    {
        $config = new waServicesApiUrlConfig();
        $config->keepEndpointsSynchronized(true);
        $this->provider = new waServicesUrlsProvider([
            'config' => $config
        ]);
    }
}
