<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use Response;
use Illuminate\Http\Request;
use DB;
use App\Library\Mail;


class MailController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return Response
     */
    public function send(Request $request)
    {
        // perhaps use the League/oauth stuff instead, but it has too many moving parts

        $client = DB::table('oauth_clients')
            ->where('id', $request->input('client_id'))
            ->where('secret', $request->input('client_secret'))
            ->first();

        if(!$client) {
            return view('auth.denied');
        }
        Mail::sendStyledMail($request->input('template'),  json_decode($request->input('data', '{}'), true), $request->input('email', ''), $request->input('name', ''), $request->input('subject', ''));

        return Response::json(['message' => 'mail sent']);
    }

    public function view(Request $request)
    {
        $email = Mail::render($request->input('template'),  json_decode($request->input('data', '{}'), true), $request->input('email', ''), $request->input('name', ''), $request->input('subject', ''));
        return $email;
    }

}