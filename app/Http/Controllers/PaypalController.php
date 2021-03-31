<?php

namespace App\Http\Controllers;

use App\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;
use App\User;

use Illuminate\Support\Facades\Auth;

require_once('utility/simple_html_dom.php');

class PaypalController extends BaseController
{

    //
    /**
     * Validate user's PayPal
     */
    public function validate_paypal(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "paypalme" => "required"
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->toArray(), 400);
        }

        $currentuser = Auth()->user();
        $currentuser->load('participant');
        $logged_in_email = $currentuser->email;

        $requestEmail = $request['email'];

        $requestEmailMatch = User::where("email", $requestEmail)->first();
        if ($requestEmailMatch && $requestEmailMatch->id !== $currentuser->id) {
            return $this->sendError('Email validation error - you are trying to claim a PayPal Email that is in use by another user as their primary login.');
        }

        $idExists = Participant::where("paypal_id", $request['email'])->first();
        $meExists = Participant::where("paypal_me", $request['paypalme'])->first();

        if ($idExists && $currentuser->participant->paypal_id !== $idExists->paypal_id) {
            return $this->sendError('That PayPal ID is in use');
        }

        if ($meExists && ($currentuser->id !== $meExists->user_id)) {
            return $this->sendError('That PayPal.Me is being used by another participant');
        }

        $checkPayPalMe = $this->checkPayPalMe($request['paypalme']);

        if ($checkPayPalMe == false) {
            return $this->sendError('That PayPal.Me wasn\'nt confirmed as correct');
        }

        $currentuser->load('participant');
        $currentuser->participant->paypal_id = $requestEmail;
        $currentuser->participant->paypal_me = $request['paypalme'];
        $currentuser->participant->paypal_id_status = 'Ok';

        $currentuser->participant->save();
        $this->logger('info', Auth::user()->email . ' updated PayPal ID ', [$request['paypalme']]);

        return $this->sendResponse($checkPayPalMe, 'Paypal info updated', 200);
    }

    private function get_data($url, $headers)
    {
        $ch = curl_init();
        $timeout = 10;
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, 'https://paypal.me/');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        // Blindly accept the certificate
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //        curl_setopt($ch,CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_ENCODING, true);
        $data = curl_exec($ch);
        if ($data === FALSE) {
            echo "cURL Error: " . curl_error($ch);
        }
        return $data;
    }

    private function checkPayPalMe($url)
    {

        //Curl paypal to see if the ME address exists or is 404
        $url = 'https://www.paypal.me/' . $url;
        $headers = array(
            'Content-Type:application/x-www-form-urlencoded',
            'Host: www.paypal.me',
            'Connection: keep-alive',
            'Cache-Control: max-age=0',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            //            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: en-US,en;q=0.8',
            'Cookie: AMCVS_00CC7FBA5881402E0A495ECF%40AdobeOrg=1; AMCV_00CC7FBA5881402E0A495ECF%40AdobeOrg=2096510701%7CMCIDTS%7C17392%7CMCMID%7C64199004705521285423041451780447508012%7CMCAAMLH-1503241441%7C9%7CMCAAMB-1503241441%7CNRX38WO0n5BH8Th-nqAG_A%7CMCOPTOUT-1502643841s%7CNONE%7CMCSYNCSOP%7C411-17399%7CMCAID%7CNONE%7CvVersion%7C2.0.0; JSESSIONID=EN3cH8nI5OtcYJ-CpEw_iibUHCxDjaWIdo0G2shUKOoZNZe-KHVk!-1602976279; InitialURL=us.spdrs.com; s_sq=%5B%5BB%5D%5D; geoloc=us:en; role=def; disclaimer=accepted; WT_FPC=id=61809310-4e2c-49c8-9d15-bc5b8f8cc542:lv=1502643509760:ss=1502640241586; s_cc=true; _mkto_trk=id:451-VAW-614&token:_mch-spdrs.com-1502636641656-38351; _bizo_bzid=a9dddfa7-ae51-4553-9a59-b6c89288e0f0; _bizo_cksm=6CE976185B916A09; visitor_id42582=578823939; visitor_id42582-hash=c501518c32b18f02cbeb2fb490d4f82f9747f4ab7ad73fc9993956cfe4b3876adb160b5badaafc008d442dd427ba4fbc3134aa04; _bizo_np_stats=155%3D455%2C'
        );

        $remotehtml = $this->get_data($url, $headers);
        //                echo $remotehtml;
        //                $remotehtml = 'dgt.html';
        $html = str_get_html($remotehtml, $use_include_path = true, $context = null, $offset = 0, $maxLen = -1, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT);
        //Get the fund name
        if ($html->find('meta[name=twitter:title]', 0) !== null) {
            //actual fund
            $twitterTitle = $html->find('meta[name=twitter:title]', 0);
            $value = $twitterTitle->content;
            if (!preg_match("/Get your very/", $value)) {
                $text = ["Pay", "Pal.Me", "using"];
                $trim = str_replace($text, '', $value);
                $trim = trim($trim);
                return $trim;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
