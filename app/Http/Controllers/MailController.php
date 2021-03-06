<?php

namespace App\Http\Controllers;

use Response;
use Illuminate\Http\Request;
use DB;
use App\Library\Mail;


class MailController extends Controller
{
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
        Mail::queueStyledMail($request->input('template'), json_decode($request->input('data', '{}'), true), $request->input('email', ''), $request->input('name', ''), $request->input('subject', ''));

        return Response::json(['message' => 'mail sent']);
    }

    public function view(Request $request)
    {
        $email = Mail::render($request->input('template'),  json_decode($request->input('data', '{}'), true), $request->input('email', ''), $request->input('name', ''), $request->input('subject', ''));
        return $email;
    }

    public function sendDocuments(Request $request)
    {
        $client = DB::table('oauth_clients')
            ->where('id', $request->input('client_id'))
            ->where('secret', $request->input('client_secret'))
            ->first();

        if (!$client) {
            return view('auth.denied');
        }

        $template = $request->input('template');
        $recipients = json_decode($request->input('recipients'));
        $subject = $request->input('subject');
        $files = $request->files->all();
        $senderName = $request->input('sender_name');
        $senderEmail = $request->input('sender_email');
        $attachments = [];

        foreach ($files as $file) {
            $newFile = $file->move(base_path('storage/tmp'));

            $attachments[] = [
                'path' => $newFile->getPathname(),
                'name' => $file->getClientOriginalName()
            ];
        }

        foreach ($recipients as $recipient) {
            $data = ['recipientName' => $recipient->name, 'senderName' => $senderName];
            Mail::queueStyledMail($template, $data, $recipient->email, $recipient->name, $subject, $attachments, $senderName, $senderEmail);
        }

        return Response::json(['message' => 'mail queued']);
    }

}
