<?php

namespace App\Http\Controllers;

use App\Ad;

class VisitorCheckController extends Controller
{
    public function index()
    {
        $secretKey = get_option('recaptcha_v3_secret_key');

        if ($secretKey) {
            $recaptchaV3 = $this->recaptchaV3Check(request()->post());

            if (!$recaptchaV3['status']) {
                request()->session()->put('VisitorStatus', 0);
                return [
                    'status' => true,
                    'ads' => false,
                ];
            }

            $recaptcha_v3_score = (float)get_option('recaptcha_v3_score', 0.5);

            if ($recaptchaV3['score'] < $recaptcha_v3_score) {
                request()->session()->put('VisitorStatus', 0);
                return [
                    'status' => true,
                    'ads' => false,
                ];
            }
        }

        if (isProxyIP()) {
            request()->session()->put('VisitorStatus', 0);
            return [
                'status' => true,
                'ads' => false,
            ];
        }

        request()->session()->put('VisitorStatus', 1);
        return [
            'status' => true,
            'ads' => true,
        ];
    }

    protected function recaptchaV3Check($post_data = [])
    {
        $secretKey = get_option('recaptcha_v3_secret_key');

        if (empty($post_data['g-recaptcha-response'])) {
            return [
                'status' => false,
                'message' => 'Missing g-recaptcha-response',
            ];
        }

        $data = array(
            'secret' => $secretKey,
            'response' => $post_data['g-recaptcha-response'],
        );

        $result = curlRequest('https://www.recaptcha.net/recaptcha/api/siteverify', 'POST', $data);

        $responseData = json_decode($result->body, true);
        //debug($responseData);

        if ($responseData['success'] === false) {
            return [
                'status' => false,
                'message' => $responseData['error-codes'],
            ];
        }

        if (!(isset($responseData['action']) && $responseData['action'] === 'visitorCheck')) {
            return [
                'status' => false,
                'message' => 'Invalid reCaptchaV3 action',
            ];
        }

        if (isset($responseData['score'])) {
            return [
                'status' => true,
                'score' => $responseData['score'],
            ];
        }

        return [
            'status' => false,
            'message' => '',
        ];
    }
}
