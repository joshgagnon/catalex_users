<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    @include('emails.layouts.partials.head')
    
    <body>
        <table class="body">
            <tr>
                <td class="center" align="center" valign="top">
                    <center>
                        @include('emails.layouts.partials.header')

                        @yield('content')

                        @include('emails.layouts.partials.member-footer')
                    </center>
                </td>
            </tr>
        </table>
    </body>
</html>
