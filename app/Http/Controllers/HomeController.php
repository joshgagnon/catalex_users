<?php namespace App\Http\Controllers;

use Auth;
use Config;
use DB;
use Illuminate\Http\Request;
use LucaDegasperi\OAuth2Server\Facades\Authorizer;

class HomeController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Home Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders your application's "dashboard" for users that
    | are authenticated. Of course, you are free to change or remove the
    | controller as you wish. It is just here to get your app started!
    |
    */

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $subscriptionUpToDate = $user->subscriptionUpToDate();
        $userHasPendingInvite = $user->organisationInvites()->count() > 0;
        $emailNeedsVerified = !$user->email_verified;
        $emailVerificationSent = $user->emailVerificationToken()->exists();

        return view('user.home')->with([
            'subscriptionUpToDate'  => $subscriptionUpToDate,
            'userHasPendingInvite'  => $userHasPendingInvite,
            'emailNeedsVerified'    => $emailNeedsVerified,
            'emailVerificationSent' => $emailVerificationSent,
            'requires2fa' =>  $request->user()->organisation->require_2fa && !$request->user()->google2fa_secret
        ]);
    }

    public function getBrowserLogin()
    {
        $params = Authorizer::getAuthCodeRequestParams();
        $client = DB::table('oauth_clients')->where('name', 'Law Browser')->first();
        if (!$client) {
            return view('auth.denied');
        }
        $params['client_id'] = $client->id;
        $params['redirect_uri'] = env('BROWSER_LOGIN_URL', 'http://localhost:3000/login');
        $params['response_type'] = 'code';
        $redirect = '/login/law-browser?' . http_build_query($params);
        return redirect($redirect);
    }

    public function getSignLogin(Request $request)
    {
        $params = Authorizer::getAuthCodeRequestParams();
        $client = DB::table('oauth_clients')->where('name','Sign')->first();

        if (!$client) {
            return view('auth.denied');
        }

        if ($request->next) {
            $params['next'] = $request->next;
        }

        $params['client_id'] = $client->id;
        $params['redirect_uri'] = env('SIGN_LOGIN_URL', 'http://localhost:3000/login');
        $params['response_type'] = 'code';

        $redirect = '/login/sign?' . http_build_query($params);

        return redirect($redirect);
    }

    public function getCCLogin(Request $request)
    {
        $params = Authorizer::getAuthCodeRequestParams();
        $client = DB::table('oauth_clients')->where('name', 'Court Costs')->first();

        if (!$client) {
            return view('auth.denied');
        }

        if ($request->next) {
            $params['next'] = $request->next;
        }

        $params['client_id'] = $client->id;
        $params['redirect_uri'] = env('CC_LOGIN_URL', 'http://localhost:5651/login');
        $params['response_type'] = 'code';

        $redirect = '/login/cc?' . http_build_query($params);
        return redirect($redirect);
    }

    public function getGoodCompaniesLogin(Request $request)
    {
        $user = Auth::user();

        if (!$user->subscriptionUpToDate()) {
            return redirect()->route('index');
        }

        $params = Authorizer::getAuthCodeRequestParams();
        $client = DB::table('oauth_clients')->where('name', 'Good Companies')->first();

        if (!$client) {
            return view('auth.denied');
        }

        if ($request->next) {
            $params['next'] = $request->next;
        }

        $params['client_id'] = $client->id;
        $params['redirect_uri'] = env('GOOD_COMPANIES_LOGIN_URL', 'http://localhost:5667/auth/catalex/login');
        $params['response_type'] = 'code';
        $redirect = '/login/good-companies?' . http_build_query($params);
        return redirect($redirect);
    }


    public function setup2FA(Request $request)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            "ELF",
            $request->user()->email,
            $secret
        );
        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(400),
                new ImagickImageBackEnd()
            )
        );
        $qrcode_image = base64_encode($writer->writeString($qrCodeUrl));
        return view('auth.setup2FA')->with([
            'secret' => $secret,
            'qrcode_image' => $qrcode_image,
            'name' => $request->user()->organisation->name
        ]);
    }

    public function save2FA(Request $request)
    {
        $user = $request->user();
        $secret = $request->input('secret');
        $window = 8; // 8 keys (respectively 4 minutes) past and future
        $key = $request->input('totp');
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($secret, $key, $window);
        if(!$valid) {
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                "ELF",
                $user->email,
                $secret
            );
            $writer = new Writer(
                new ImageRenderer(
                    new RendererStyle(400),
                    new ImagickImageBackEnd()
                )
            );
            $qrcode_image = base64_encode($writer->writeString($qrCodeUrl));
            return view('auth.setup2FA')->with([
                'secret' => $secret,
                'qrcode_image' => $qrcode_image
            ])->withErrors(["totp"=>"Code didn't match, please try again"]);
        }
        else{
            $user->google2fa_secret = $secret;
            $user->save();
            G2FA::login();
            return redirect('/');
        }
    }

    public function otp(Request $request)
    {
        return redirect('/');
    }

}

